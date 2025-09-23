<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    private const STUDENT_ACCESS_OPTIONS = ['true', 'false', 'any'];

    private function statusOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::application_status_enum)) AS status"))
                ->pluck('status')
                ->all();
        }

        return ['submitted', 'under_review', 'accepted', 'rejected', 'cancelled'];
    }

    private function periodOptions(): array
    {
        return DB::table('periods')
            ->select('id', 'year', 'term')
            ->orderByDesc('year')
            ->orderByDesc('term')
            ->get()
            ->map(fn ($period) => [
                'id' => $period->id,
                'label' => $period->year . ': ' . $period->term,
            ])
            ->all();
    }

    public function index(Request $request)
    {
        $role = session('role');
        $studentId = $this->currentStudentId();

        $query = DB::table('application_details_view');
        if ($role === 'student' && $studentId) {
            $query->where('student_id', $studentId);
        }

        $filters = [];
        $statuses = $this->statusOptions();

        if ($studentName = trim((string) $request->query('student_name'))) {
            $query->whereRaw('LOWER(student_name) LIKE ?', ['%' . strtolower($studentName) . '%']);
            $filters['student_name'] = 'Student: ' . $studentName;
        }

        if ($institutionName = trim((string) $request->query('institution_name'))) {
            $query->whereRaw('LOWER(institution_name) LIKE ?', ['%' . strtolower($institutionName) . '%']);
            $filters['institution_name'] = 'Institution: ' . $institutionName;
        }

        if ($periodId = $request->query('period_id')) {
            $query->where('period_id', $periodId);
            $period = DB::table('periods')->select('year', 'term')->where('id', $periodId)->first();
            if ($period) {
                $filters['period_id'] = 'Period: ' . $period->year . ': ' . $period->term;
            }
        }

        if ($status = $request->query('status')) {
            if (in_array($status, $statuses, true)) {
                $query->where('status', $status);
                $filters['status'] = 'Status: ' . $status;
            }
        }

        if (($studentAccess = $request->query('student_access')) && in_array($studentAccess, ['true', 'false'], true)) {
            $query->where('student_access', $studentAccess === 'true');
            $filters['student_access'] = 'Student Access: ' . ucfirst($studentAccess);
        }

        if ($submittedAt = $request->query('submitted_at')) {
            $query->whereDate('submitted_at', $submittedAt);
            $filters['submitted_at'] = 'Submitted At: ' . $submittedAt;
        }

        if (($hasNotes = $request->query('has_notes')) && in_array($hasNotes, ['true', 'false'], true)) {
            if ($hasNotes === 'true') {
                $query->whereNotNull('application_notes')->where('application_notes', '!=', '');
                $filters['has_notes'] = 'Notes: True';
            } else {
                $query->where(function ($q) {
                    $q->whereNull('application_notes')->orWhere('application_notes', '');
                });
                $filters['has_notes'] = 'Notes: False';
            }
        }

        if ($search = trim((string) $request->query('q'))) {
            $searchTerm = strtolower($search);
            $driver = DB::getDriverName();
            $yearExpr = $driver === 'pgsql' ? 'period_year::text' : 'CAST(period_year AS CHAR)';
            $termExpr = $driver === 'pgsql' ? 'period_term::text' : 'CAST(period_term AS CHAR)';
            if ($driver === 'pgsql') {
                $submittedExpr = "TO_CHAR(submitted_at, 'YYYY-MM-DD')";
            } elseif ($driver === 'sqlite') {
                $submittedExpr = "STRFTIME('%Y-%m-%d', submitted_at)";
            } else {
                $submittedExpr = "DATE_FORMAT(submitted_at, '%Y-%m-%d')";
            }
            $studentAccessExpr = "CASE WHEN student_access THEN 'true' ELSE 'false' END";

            $query->where(function ($q) use ($searchTerm, $yearExpr, $termExpr, $submittedExpr, $studentAccessExpr) {
                $q->whereRaw('LOWER(student_name) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(institution_name) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw("LOWER($yearExpr) LIKE ?", ['%' . $searchTerm . '%'])
                    ->orWhereRaw("LOWER($termExpr) LIKE ?", ['%' . $searchTerm . '%'])
                    ->orWhereRaw('LOWER(status) LIKE ?', ['%' . $searchTerm . '%'])
                    ->orWhereRaw("LOWER($studentAccessExpr) LIKE ?", ['%' . $searchTerm . '%'])
                    ->orWhereRaw("LOWER($submittedExpr) LIKE ?", ['%' . $searchTerm . '%']);
            });
        }

        $applications = $query
            ->orderByDesc('submitted_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('application.index', [
            'applications' => $applications,
            'filters' => $filters,
            'statuses' => $statuses,
            'periods' => $this->periodOptions(),
        ]);
    }

    public function create()
    {
        $role = session('role');
        $studentId = $this->currentStudentId();

        $studentsWithoutApplication = DB::table('student_details_view as sdv')
            ->leftJoin('applications as apps', 'apps.student_id', '=', 'sdv.id')
            ->whereNull('apps.id')
            ->select('sdv.id', 'sdv.name')
            ->orderBy('sdv.name')
            ->get();

        if ($role === 'student') {
            abort_unless($studentId, 401);

            $hasExisting = Application::where('student_id', $studentId)->exists();
            if ($hasExisting) {
                return redirect('/applications')->withErrors([
                    'student_ids' => 'You already have an application.',
                ]);
            }

            $students = DB::table('student_details_view')
                ->select('id', 'name')
                ->where('id', $studentId)
                ->orderBy('name')
                ->get();

            // Student cannot use bulk helper; keep dataset minimal for clarity.
            $studentsWithoutApplication = collect();
        } else {
            $students = $studentsWithoutApplication;
        }

        $institutions = DB::table('institution_details_view')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('application.create', [
            'students' => $students,
            'studentsWithoutApplication' => $studentsWithoutApplication,
            'institutions' => $institutions,
            'periods' => $this->periodOptions(),
            'statuses' => $this->statusOptions(),
            'canSetStudentAccess' => $role !== 'student',
            'isStudent' => $role === 'student',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $role = session('role');
        $statuses = $this->statusOptions();

        $rules = [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'distinct|integer|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'period_id' => 'required|exists:periods,id',
            'status' => ['required', Rule::in($statuses)],
            'submitted_at' => 'required|date',
            'notes' => 'nullable|string',
            'apply_missing' => 'nullable|boolean',
        ];

        if ($role === 'student') {
            $studentId = $this->currentStudentId();
            abort_unless($studentId, 401);

            $rules['student_ids'] = 'required|array|size:1';
            $rules['student_ids.*'] = 'integer|in:' . $studentId;
        } else {
            $rules['student_access'] = ['required', Rule::in(self::STUDENT_ACCESS_OPTIONS)];
        }

        $validated = $request->validate($rules);

        $selectedStudentIds = collect($validated['student_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($role === 'student') {
            $studentId = $this->currentStudentId();
            abort_unless($studentId, 401);

            $hasApplication = Application::where('student_id', $studentId)->exists();
            if ($hasApplication) {
                return redirect('/applications')->withErrors([
                    'student_ids' => 'You already have an application.',
                ]);
            }

            if ($selectedStudentIds !== [$studentId]) {
                return back()->withErrors([
                    'student_ids' => 'Invalid student selected for creation.',
                ])->withInput();
            }
        }

        $institutionId = (int) $validated['institution_id'];
        $periodId = (int) $validated['period_id'];

        $targetStudentIds = $selectedStudentIds;

        if ($role !== 'student' && $request->boolean('apply_missing')) {
            $missingIds = DB::table('student_details_view as sdv')
                ->leftJoin('applications as apps', 'apps.student_id', '=', 'sdv.id')
                ->whereNull('apps.id')
                ->pluck('sdv.id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $targetStudentIds = array_values(array_unique(array_merge($targetStudentIds, $missingIds)));
        }

        if (empty($targetStudentIds)) {
            return back()->withErrors([
                'student_ids' => 'Select at least one student.',
            ])->withInput();
        }

        $duplicateStudents = Application::whereIn('student_id', $targetStudentIds)
            ->where('institution_id', $institutionId)
            ->where('period_id', $periodId)
            ->pluck('student_id')
            ->all();

        if (!empty($duplicateStudents)) {
            return back()->withErrors([
                'student_ids' => 'An application already exists for one or more selected students with the chosen institution and period.',
            ])->withInput();
        }

        $studentAccess = false;
        if ($role !== 'student') {
            $input = $validated['student_access'] ?? 'any';
            $studentAccess = $input === 'true';
        }

        DB::transaction(function () use ($targetStudentIds, $institutionId, $periodId, $validated, $studentAccess) {
            foreach ($targetStudentIds as $studentId) {
                Application::create([
                    'student_id' => $studentId,
                    'institution_id' => $institutionId,
                    'period_id' => $periodId,
                    'status' => $validated['status'],
                    'student_access' => $studentAccess,
                    'submitted_at' => $validated['submitted_at'],
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        $count = count($targetStudentIds);
        $message = $count === 1
            ? 'Application created successfully.'
            : "Applications created successfully for {$count} students.";

        return redirect('/applications')->with('status', $message);
    }

    public function show(int $id)
    {
        $application = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$application, 404);

        $studentId = $this->currentStudentId();
        if (session('role') === 'student' && $application->student_id !== $studentId) {
            abort(401);
        }

        return view('application.show', [
            'application' => $application,
        ]);
    }

    public function edit(int $id)
    {
        $application = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$application, 404);

        $role = session('role');
        $studentId = $this->currentStudentId();

        if ($role === 'student') {
            if ($application->student_id !== $studentId || !$application->student_access) {
                abort(401);
            }
        }

        $studentsForInstitution = DB::table('application_details_view')
            ->select('student_id', 'student_name')
            ->where('institution_id', $application->institution_id)
            ->distinct()
            ->orderBy('student_name')
            ->get();

        $students = $role === 'student'
            ? $studentsForInstitution->where('student_id', $application->student_id)->values()
            : $studentsForInstitution;

        $institutions = DB::table('institution_details_view')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('application.edit', [
            'application' => $application,
            'students' => $students,
            'allStudentsForInstitution' => $studentsForInstitution,
            'institutions' => $institutions,
            'periods' => $this->periodOptions(),
            'statuses' => $this->statusOptions(),
            'canSetStudentAccess' => $role !== 'student',
            'isStudent' => $role === 'student',
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $application = Application::findOrFail($id);
        $viewRecord = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$viewRecord, 404);

        $role = session('role');
        $studentId = $this->currentStudentId();

        if ($role === 'student') {
            if ($viewRecord->student_id !== $studentId || !$viewRecord->student_access) {
                abort(401);
            }
        }

        $statuses = $this->statusOptions();

        $rules = [
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'distinct|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'period_id' => 'required|exists:periods,id',
            'status' => ['required', Rule::in($statuses)],
            'submitted_at' => 'required|date',
            'notes' => 'nullable|string',
            'apply_all' => 'nullable|boolean',
        ];

        if ($role !== 'student') {
            $rules['student_access'] = ['required', Rule::in(self::STUDENT_ACCESS_OPTIONS)];
        }

        $validated = $request->validate($rules);

        $selectedStudentIds = collect($validated['student_ids'])->map(fn ($value) => (int) $value)->all();
        if (!in_array($application->student_id, $selectedStudentIds, true)) {
            return back()->withErrors([
                'student_ids' => 'Original student must remain selected.',
            ])->withInput();
        }

        if ($role === 'student') {
            if ($selectedStudentIds !== [$application->student_id]) {
                return back()->withErrors([
                    'student_ids' => 'Students cannot modify other student assignments.',
                ])->withInput();
            }
        }

        $applyAll = $role !== 'student' && $request->boolean('apply_all');

        if (!$applyAll) {
            $existing = Application::where('institution_id', $application->institution_id)
                ->whereIn('student_id', $selectedStudentIds)
                ->pluck('student_id')
                ->all();

            if (count($existing) !== count($selectedStudentIds)) {
                return back()->withErrors([
                    'student_ids' => 'All selected students must already have an application with this institution.',
                ])->withInput();
            }
        }

        $targetQuery = Application::where('institution_id', $application->institution_id);
        if (!$applyAll) {
            $targetQuery->whereIn('student_id', $selectedStudentIds);
        }

        $targetApplications = $targetQuery->get(['id', 'student_id']);
        $targetIds = $targetApplications->pluck('id')->all();

        foreach ($targetApplications as $target) {
            $duplicate = Application::where('student_id', $target->student_id)
                ->where('institution_id', $validated['institution_id'])
                ->where('period_id', $validated['period_id'])
                ->whereNotIn('id', $targetIds)
                ->exists();

            if ($duplicate) {
                return back()->withErrors([
                    'student_ids' => 'Duplicate application detected for one of the selected students.',
                ])->withInput();
            }
        }

        $studentAccess = $application->student_access;
        if ($role !== 'student') {
            $input = $validated['student_access'] ?? 'any';
            $studentAccess = $input === 'true' ? true : ($input === 'false' ? false : $studentAccess);
        }

        $updateData = [
            'institution_id' => $validated['institution_id'],
            'period_id' => $validated['period_id'],
            'status' => $validated['status'],
            'submitted_at' => $validated['submitted_at'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($role !== 'student') {
            $updateData['student_access'] = $studentAccess;
        }

        Application::whereIn('id', $targetIds)->update($updateData);

        return redirect('/applications/' . $id . '/read/')->with('status', 'Application updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $application = Application::findOrFail($id);
        $viewRecord = DB::table('application_details_view')->where('id', $id)->first();
        abort_if(!$viewRecord, 404);

        $role = session('role');
        $studentId = $this->currentStudentId();

        if ($role === 'student') {
            if ($viewRecord->student_id !== $studentId || !$viewRecord->student_access) {
                abort(401);
            }
        }

        $application->delete();

        return redirect('/applications')->with('status', 'Application deleted successfully.');
    }
}
