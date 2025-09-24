<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $role = session('role');
        $studentId = $this->currentStudentId();

        $query = DB::table('internship_details_view')
            ->select('id', 'student_id', 'student_name', 'institution_id', 'institution_name', 'period_id', 'period_year', 'period_term', 'start_date', 'end_date', 'status');

        if ($role === 'student' && $studentId) {
            $query->where('student_id', $studentId);
        }

        $filters = [];
        $selections = [
            'student_id' => null,
            'institution_id' => null,
            'period_id' => null,
            'status' => null,
            'start_date_from' => null,
            'start_date_to' => null,
            'end_date_from' => null,
            'end_date_to' => null,
        ];

        $students = DB::table('internship_details_view')
            ->when($role === 'student' && $studentId, fn($q) => $q->where('student_id', $studentId))
            ->select('student_id', 'student_name')
            ->groupBy('student_id', 'student_name')
            ->orderBy('student_name')
            ->get()
            ->map(fn($item) => [
                'id' => $item->student_id,
                'label' => $item->student_name,
            ])
            ->values();
        $studentLabels = $students->pluck('label', 'id');

        $institutions = DB::table('internship_details_view')
            ->when($role === 'student' && $studentId, fn($q) => $q->where('student_id', $studentId))
            ->select('institution_id', 'institution_name')
            ->groupBy('institution_id', 'institution_name')
            ->orderBy('institution_name')
            ->get()
            ->map(fn($item) => [
                'id' => $item->institution_id,
                'label' => $item->institution_name,
            ])
            ->values();
        $institutionLabels = $institutions->pluck('label', 'id');

        $periods = DB::table('internship_details_view')
            ->when($role === 'student' && $studentId, fn($q) => $q->where('student_id', $studentId))
            ->select('period_id', 'period_year', 'period_term')
            ->groupBy('period_id', 'period_year', 'period_term')
            ->orderByDesc('period_year')
            ->orderByDesc('period_term')
            ->get()
            ->map(fn($item) => [
                'id' => $item->period_id,
                'label' => $item->period_year . ' - ' . $item->period_term,
            ])
            ->values();
        $periodLabels = $periods->pluck('label', 'id');

        $statuses = $this->statusOptions();

        if (($studentFilter = $request->query('student_id')) !== null && is_numeric($studentFilter)) {
            $studentFilter = (int) $studentFilter;
            $query->where('student_id', $studentFilter);
            $filters['student_id'] = 'Student Name: ' . ($studentLabels->get($studentFilter) ?? $studentFilter);
            $selections['student_id'] = $studentFilter;
        }

        if (($institutionFilter = $request->query('institution_id')) !== null && is_numeric($institutionFilter)) {
            $institutionFilter = (int) $institutionFilter;
            $query->where('institution_id', $institutionFilter);
            $filters['institution_id'] = 'Institution Name: ' . ($institutionLabels->get($institutionFilter) ?? $institutionFilter);
            $selections['institution_id'] = $institutionFilter;
        }

        if (($periodFilter = $request->query('period_id')) !== null && is_numeric($periodFilter)) {
            $periodFilter = (int) $periodFilter;
            $query->where('period_id', $periodFilter);
            $filters['period_id'] = 'Period: ' . ($periodLabels->get($periodFilter) ?? $periodFilter);
            $selections['period_id'] = $periodFilter;
        }

        if ($statusFilter = $request->query('status')) {
            if (in_array($statusFilter, $statuses, true)) {
                $query->where('status', $statusFilter);
                $readableStatus = ucwords(str_replace('_', ' ', $statusFilter));
                $filters['status'] = 'Status: ' . $readableStatus;
                $selections['status'] = $statusFilter;
            }
        }

        $startFrom = $request->query('start_date_from');
        $startTo = $request->query('start_date_to');
        if ($this->isValidDate($startFrom)) {
            $query->whereDate('start_date', '>=', $startFrom);
            $selections['start_date_from'] = $startFrom;
        } else {
            $startFrom = null;
        }
        if ($this->isValidDate($startTo)) {
            $query->whereDate('start_date', '<=', $startTo);
            $selections['start_date_to'] = $startTo;
        } else {
            $startTo = null;
        }
        if ($startFrom || $startTo) {
            if ($startFrom && $startTo) {
                $filters['start_date'] = 'Start Date: ' . $startFrom . ' - ' . $startTo;
            } elseif ($startFrom) {
                $filters['start_date'] = 'Start Date: ' . $startFrom;
            } else {
                $filters['start_date'] = 'Start Date: up to ' . $startTo;
            }
        }

        $endFrom = $request->query('end_date_from');
        $endTo = $request->query('end_date_to');
        if ($this->isValidDate($endFrom)) {
            $query->whereDate('end_date', '>=', $endFrom);
            $selections['end_date_from'] = $endFrom;
        } else {
            $endFrom = null;
        }
        if ($this->isValidDate($endTo)) {
            $query->whereDate('end_date', '<=', $endTo);
            $selections['end_date_to'] = $endTo;
        } else {
            $endTo = null;
        }
        if ($endFrom || $endTo) {
            if ($endFrom && $endTo) {
                $filters['end_date'] = 'End Date: ' . $endFrom . ' - ' . $endTo;
            } elseif ($endFrom) {
                $filters['end_date'] = 'End Date: ' . $endFrom;
            } else {
                $filters['end_date'] = 'End Date: up to ' . $endTo;
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $term = strtolower($search);
            $driver = DB::getDriverName();
            $startCast = $driver === 'pgsql' ? 'start_date::text' : 'CAST(start_date AS CHAR)';
            $endCast = $driver === 'pgsql' ? 'end_date::text' : 'CAST(end_date AS CHAR)';
            $yearCast = $driver === 'pgsql' ? 'period_year::text' : 'CAST(period_year AS CHAR)';
            $termCast = $driver === 'pgsql' ? 'period_term::text' : 'CAST(period_term AS CHAR)';

            $statusExpr = $driver === 'pgsql' ? 'status::text' : 'status';

            $query->where(function ($q) use ($term, $startCast, $endCast, $yearCast, $termCast, $statusExpr) {
                $q->whereRaw('LOWER(student_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(institution_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER($yearCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($termCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($startCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($endCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($statusExpr) LIKE ?", ["%{$term}%"]);
            });
        }

        $internships = $query
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('internship.index', [
            'internships' => $internships,
            'filters' => $filters,
            'students' => $students,
            'institutions' => $institutions,
            'periods' => $periods,
            'statuses' => $statuses,
            'selections' => $selections,
        ]);
    }

    public function show($id)
    {
        $internship = DB::table('internship_details_view as it')
            ->join('application_details_view as app', 'app.id', '=', 'it.application_id')
            ->select([
                'it.id',
                'it.application_id',
                'it.student_id',
                'app.student_name',
                'app.student_email',
                'app.student_phone',
                'app.student_number',
                'app.national_sn',
                'app.student_major',
                'app.student_class',
                'app.student_batch',
                'app.student_notes',
                'app.student_photo',
                'app.institution_id',
                'app.institution_name',
                'app.institution_address',
                'app.institution_city',
                'app.institution_province',
                'app.institution_website',
                'app.institution_industry',
                'app.institution_notes',
                'app.institution_photo',
                'app.institution_contact_name',
                'app.institution_contact_email',
                'app.institution_contact_phone',
                'app.institution_contact_position',
                'app.institution_contact_primary',
                'app.institution_quota',
                'app.institution_quota_used',
                'app.institution_quota_period_year',
                'app.institution_quota_period_term',
                DB::raw('NULL as institution_quota_notes'),
                'app.period_year as application_period_year',
                'app.period_term as application_period_term',
                'app.status as application_status',
                'app.student_access',
                'app.submitted_at',
                'app.application_notes',
                'it.start_date',
                'it.end_date',
                'it.status as internship_status',
            ])
            ->where('it.id', $id)
            ->first();

        abort_if(!$internship, 404);

        if (session('role') === 'student' && $internship->student_id !== $this->currentStudentId()) {
            abort(401);
        }

        return view('internship.show', ['internship' => $internship]);
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
            ->orderBy('student_name')
            ->orderBy('institution_name')
            ->get()
            ->map(fn($app) => [
                'id' => (int) $app->id,
                'label' => $app->student_name . ' - ' . $app->institution_name,
                'institution_id' => (int) $app->institution_id,
            ])
            ->values();
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
            'application_ids.*' => 'integer|distinct|exists:applications,id',
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

        return redirect('/internships');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        $applications = DB::table('internship_details_view')
            ->select('application_id as id', 'student_name', 'institution_name', 'institution_id')
            ->where('institution_id', $internship->institution_id)
            ->orderBy('student_name')
            ->orderBy('institution_name')
            ->get()
            ->unique('id')
            ->map(fn($app) => [
                'id' => (int) $app->id,
                'label' => $app->student_name . ' - ' . $app->institution_name,
                'institution_id' => (int) $app->institution_id,
            ])
            ->values();
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
            'application_ids.*' => 'integer|distinct|exists:applications,id',
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

        return redirect('/internships');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return redirect('/internships');
    }

    private function isValidDate(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }
}
