<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Period;
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
        return view('application.show', compact('application'));
    }

    public function create()
    {
        $students = DB::table('student_details_view')->select('id','name')->orderBy('name')->get();
        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $statuses = $this->statusOptions();
        return view('application.create', compact('students','institutions','statuses'));
    }

    public function store(Request $request)
    {
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'status' => 'required|in:' . implode(',', $statuses),
            'submitted_at' => 'required|date',
            'decision_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $period = Period::orderBy('year', 'desc')->orderBy('term', 'desc')->first();
        $data['period_id'] = $period ? $period->id : null;

        Application::create($data);
        return redirect('/application');
    }

    public function edit($id)
    {
        $application = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$application, 404);
        $students = DB::table('student_details_view')->select('id','name')->orderBy('name')->get();
        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $statuses = $this->statusOptions();
        return view('application.edit', compact('application','students','institutions','statuses'));
    }

    public function update(Request $request, $id)
    {
        $application = Application::findOrFail($id);
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'student_id' => 'required|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'status' => 'required|in:' . implode(',', $statuses),
            'submitted_at' => 'required|date',
            'decision_at' => 'nullable|date',
            'rejection_reason' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $application->update($data);
        return redirect('/application');
    }

    public function destroy($id)
    {
        $application = Application::findOrFail($id);
        $application->delete();
        return redirect('/application');
    }
}
