<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    private function statusOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::application_status_enum)) AS status"))->pluck('status')->all();
        }
        return ['draft','submitted','under_review','accepted','rejected','cancelled'];
    }

    public function index(Request $request)
    {
        $query = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name', 'period_year', 'period_term', 'status', 'submitted_at', 'created_at', 'updated_at');
        if (session('role') === 'student') {
            $query->where('student_id', $this->currentStudentId());
        }

        $filters = [];

        if ($statusParam = $request->query('status')) {
            if (Str::startsWith($statusParam, 'in:')) {
                $statuses = array_filter(explode(',', Str::after($statusParam, 'in:')));
                if ($statuses) {
                    $query->whereIn('status', $statuses);
                    $filters['status'] = 'Status: ' . implode(', ', $statuses);
                }
            }
        }

        foreach (['submitted_at' => 'Submitted', 'created_at' => 'Created', 'updated_at' => 'Updated'] as $param => $label) {
            if ($range = $request->query($param)) {
                if (Str::startsWith($range, 'range:')) {
                    [$start, $end] = array_pad(explode(',', Str::after($range, 'range:')), 2, null);
                    if ($start) {
                        $query->whereDate($param, '>=', $start);
                    }
                    if ($end) {
                        $query->whereDate($param, '<=', $end);
                    }
                    $filters[$param] = $label . ': ' . $start . ' - ' . $end;
                }
            }
        }

        if ($search = $request->query('q')) {
            $term = strtolower($search);
            $driver = DB::getDriverName();
            $yearCast = $driver === 'pgsql' ? 'period_year::text' : 'CAST(period_year AS CHAR)';
            $termCast = $driver === 'pgsql' ? 'period_term::text' : 'CAST(period_term AS CHAR)';
            $query->where(function ($q) use ($term, $yearCast, $termCast) {
                $q->whereRaw('LOWER(student_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(institution_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER($yearCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($termCast) LIKE ?", ["%{$term}%"]);
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = ['submitted_at', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir)->orderByDesc('id');

        $applications = $query->paginate(10)->withQueryString();

        return view('application.index', [
            'applications' => $applications,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $application = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$application, 404);
        if (session('role') === 'student' && $application->student_id !== $this->currentStudentId()) {
            abort(401);
        }
        return view('application.show', compact('application'));
    }

    public function create()
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $students = DB::table('student_details_view as s')
            ->leftJoin('applications as a', 'a.student_id', '=', 's.id')
            ->whereNull('a.id')
            ->select('s.id','s.name')
            ->orderBy('s.name')
            ->get();
        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $statuses = $this->statusOptions();
        return view('application.create', compact('students','institutions','statuses'));
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'distinct|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'status' => 'required|in:' . implode(',', $statuses),
            'submitted_at' => 'required|date',
            'decision_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $existing = Application::whereIn('student_id', $data['student_ids'])->pluck('student_id')->all();
        if ($existing) {
            return back()->withErrors([
                'student_ids' => 'One or more selected students already have an application',
            ])->withInput();
        }

        $periodId = DB::table('institution_quotas')
            ->where('institution_id', $data['institution_id'])
            ->orderByDesc('period_id')
            ->value('period_id');

        if (!$periodId) {
            return back()->withErrors([
                'institution_id' => 'Selected institution has no quota set',
            ]);
        }

        DB::transaction(function () use ($data, $periodId) {
            foreach ($data['student_ids'] as $studentId) {
                Application::create([
                    'student_id' => $studentId,
                    'institution_id' => $data['institution_id'],
                    'period_id' => $periodId,
                    'status' => $data['status'],
                    'submitted_at' => $data['submitted_at'],
                    'decision_at' => $data['decision_at'] ?? null,
                    'rejection_reason' => $data['rejection_reason'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);
            }
        });

        return redirect('/application')->with('status', 'Applications created');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $application = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$application, 404);

        $students = DB::table('applications as a')
            ->join('student_details_view as s', 's.id', '=', 'a.student_id')
            ->where('a.institution_id', $application->institution_id)
            ->where('a.id', '!=', $application->id)
            ->select('s.id','s.name')
            ->orderBy('s.name')
            ->get();

        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $statuses = $this->statusOptions();
        return view('application.edit', compact('application','students','institutions','statuses'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $application = Application::findOrFail($id);
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'distinct|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'status' => 'required|in:' . implode(',', $statuses),
            'submitted_at' => 'required|date',
            'decision_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'apply_all' => 'sometimes|boolean',
        ]);

        if (!in_array($application->student_id, $data['student_ids'])) {
            return back()->withErrors([
                'student_ids' => 'Original student must be included',
            ]);
        }

        $applyAll = $request->boolean('apply_all');
        if ($applyAll) {
            $studentIds = Application::where('institution_id', $application->institution_id)
                ->pluck('student_id')
                ->all();
        } else {
            $studentIds = $data['student_ids'];
            $existing = Application::where('institution_id', $application->institution_id)
                ->whereIn('student_id', $studentIds)
                ->pluck('student_id')
                ->all();
            $missing = array_diff($studentIds, $existing);
            if ($missing) {
                return back()->withErrors([
                    'student_ids' => 'One or more students do not have applications for this institution',
                ]);
            }
        }

        $periodId = DB::table('institution_quotas')
            ->where('institution_id', $data['institution_id'])
            ->orderByDesc('period_id')
            ->value('period_id');

        if (!$periodId) {
            return back()->withErrors([
                'institution_id' => 'Selected institution has no quota set',
            ]);
        }

        $updateData = [
            'institution_id' => $data['institution_id'],
            'period_id' => $periodId,
            'status' => $data['status'],
            'submitted_at' => $data['submitted_at'],
            'decision_at' => $data['decision_at'] ?? null,
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        DB::transaction(function () use ($application, $updateData, $applyAll, $studentIds) {
            $query = Application::where('institution_id', $application->institution_id);
            if (!$applyAll) {
                $query->whereIn('student_id', $studentIds);
            }
            $query->update($updateData);
        });

        return redirect('/application')->with('status', 'Applications updated');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $application = Application::findOrFail($id);
        $application->delete();
        return redirect('/application');
    }
}
