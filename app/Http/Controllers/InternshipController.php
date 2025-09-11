<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InternshipController extends Controller
{
    private function statusOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::internship_status_enum)) AS status"))->pluck('status')->all();
        }
        return ['planned', 'ongoing', 'completed', 'terminated'];
    }

    public function index(Request $request)
    {
        $query = DB::table('internship_details_view')
            ->select('id', 'student_name', 'institution_name', 'start_date', 'end_date', 'status', 'created_at', 'updated_at');
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

        foreach (['start_date' => 'Start', 'end_date' => 'End', 'created_at' => 'Created', 'updated_at' => 'Updated'] as $param => $label) {
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
            $startCast = $driver === 'pgsql' ? 'start_date::text' : 'CAST(start_date AS CHAR)';
            $endCast = $driver === 'pgsql' ? 'end_date::text' : 'CAST(end_date AS CHAR)';
            $query->where(function ($q) use ($term, $startCast, $endCast) {
                $q->whereRaw('LOWER(student_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(institution_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER($startCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($endCast) LIKE ?", ["%{$term}%"]);
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = ['start_date', 'end_date', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir)->orderByDesc('id');

        $internships = $query->paginate(10)->withQueryString();

        return view('internship.index', [
            'internships' => $internships,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        if (session('role') === 'student' && $internship->student_id !== $this->currentStudentId()) {
            abort(401);
        }
        return view('internship.show', compact('internship'));
    }

    public function create()
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $applications = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name', 'institution_id')
            ->where('status', 'accepted')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('internships')
                    ->whereColumn('internships.application_id', 'application_details_view.id');
            })
            ->orderBy('id')
            ->get();
        $statuses = $this->statusOptions();
        return view('internship.create', compact('applications', 'statuses'));
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'integer|distinct',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);

        $apps = DB::table('applications')
            ->select('id', 'student_id', 'institution_id', 'period_id', 'status')
            ->whereIn('id', $data['application_ids'])
            ->get()->keyBy('id');

        if ($apps->count() !== count($data['application_ids'])) {
            return back()->withErrors(['application_ids' => 'Invalid applications'])->withInput();
        }

        $existingApps = DB::table('internships')
            ->whereIn('application_id', $data['application_ids'])
            ->pluck('application_id')
            ->all();
        $existingStudents = DB::table('internships')
            ->whereIn('student_id', $apps->pluck('student_id'))
            ->pluck('student_id')
            ->all();

        $existingAppSet = array_flip($existingApps);
        $existingStudentSet = array_flip($existingStudents);
        $seenStudents = [];
        $errors = [];

        foreach ($data['application_ids'] as $idx => $id) {
            $app = $apps[$id];
            if ($app->status !== 'accepted') {
                $errors["application_ids.$idx"] = 'Application must be accepted';
            }
            if (isset($existingAppSet[$id])) {
                $errors["application_ids.$idx"] = 'Application already has internship';
            }
            if (isset($existingStudentSet[$app->student_id])) {
                $errors["application_ids.$idx"] = 'Student already has internship';
            }
            if (isset($seenStudents[$app->student_id])) {
                $errors["application_ids.$idx"] = 'Duplicate student';
            } else {
                $seenStudents[$app->student_id] = true;
            }
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        $first = $apps[$data['application_ids'][0]];

        DB::transaction(function () use ($data, $apps, $first) {
            foreach ($data['application_ids'] as $id) {
                $app = $apps[$id];
                if ($app->institution_id !== $first->institution_id) {
                    throw ValidationException::withMessages(['application_ids' => 'Applications must be from the same institution']);
                }
                Internship::create([
                    'application_id' => $app->id,
                    'student_id' => $app->student_id,
                    'institution_id' => $app->institution_id,
                    'period_id' => $app->period_id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => $data['status'],
                ]);
            }
        });

        return redirect('/internship');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        $applications = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name', 'institution_id')
            ->where('status', 'accepted')
            ->where('institution_id', $internship->institution_id)
            ->orderBy('id')
            ->get();
        $statuses = $this->statusOptions();
        return view('internship.edit', compact('internship', 'applications', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $internship = Internship::findOrFail($id);
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_ids' => 'required|array|min:1',
            'application_ids.*' => 'integer|distinct',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);

        if (!in_array($internship->application_id, $data['application_ids'])) {
            array_unshift($data['application_ids'], $internship->application_id);
        }

        $apps = DB::table('applications')
            ->select('id', 'student_id', 'institution_id', 'period_id', 'status')
            ->whereIn('id', $data['application_ids'])
            ->get()->keyBy('id');

        if ($apps->count() !== count($data['application_ids'])) {
            return back()->withErrors(['application_ids' => 'Invalid applications'])->withInput();
        }

        $existingStudents = DB::table('internships')
            ->whereIn('student_id', $apps->pluck('student_id'))
            ->whereNotIn('application_id', $data['application_ids'])
            ->pluck('student_id')
            ->all();

        $existingStudentSet = array_flip($existingStudents);
        $seenStudents = [];
        $errors = [];

        foreach ($data['application_ids'] as $idx => $aid) {
            $app = $apps[$aid];
            if ($app->status !== 'accepted') {
                $errors["application_ids.$idx"] = 'Application must be accepted';
            }
            if (isset($existingStudentSet[$app->student_id])) {
                $errors["application_ids.$idx"] = 'Student already has internship';
            }
            if (isset($seenStudents[$app->student_id])) {
                $errors["application_ids.$idx"] = 'Duplicate student';
            } else {
                $seenStudents[$app->student_id] = true;
            }
        }

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        $first = $apps[$internship->application_id];

        $internships = Internship::whereIn('application_id', $data['application_ids'])->get()->keyBy('application_id');
        if ($internships->count() !== count($data['application_ids'])) {
            return back()->withErrors(['application_ids' => 'Internship not found for selected applications'])->withInput();
        }

        DB::transaction(function () use ($data, $apps, $first, $internships) {
            foreach ($data['application_ids'] as $id) {
                $app = $apps[$id];
                if ($app->institution_id !== $first->institution_id) {
                    throw ValidationException::withMessages(['application_ids' => 'Applications must be from the same institution']);
                }
                $internships[$id]->update([
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'status' => $data['status'],
                ]);
            }
        });

        return redirect('/internship');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return redirect('/internship');
    }
}
