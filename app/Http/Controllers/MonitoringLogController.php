<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MonitoringLogController extends Controller
{
    private function typeOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::monitor_type_enum)) AS type"))->pluck('type')->all();
        }

        return ['weekly', 'issue', 'final', 'other'];
    }

    private function internshipOptions()
    {
        return DB::table('internship_details_view')
            ->select('id', 'student_name', 'institution_name', 'institution_id')
            ->orderBy('student_name')
            ->orderBy('institution_name')
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->id,
                'label' => $item->student_name . ' - ' . $item->institution_name,
                'institution_id' => (int) $item->institution_id,
            ]);
    }

    private function isValidDate(?string $value): bool
    {
        if (!$value) {
            return false;
        }

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function monitoringExists(int $internshipId, string $logDate, string $type, ?int $excludeId = null): bool
    {
        $query = MonitoringLog::query()
            ->where('internship_id', $internshipId)
            ->whereDate('log_date', $logDate)
            ->where('type', $type);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function index(Request $request)
    {
        $role = session('role');
        $studentId = $this->currentStudentId();
        $types = $this->typeOptions();

        $query = DB::table('v_monitoring_log_summary as ms')
            ->join('internship_details_view as iv', 'iv.id', '=', 'ms.internship_id')
            ->select(
                'ms.monitoring_log_id',
                'ms.title',
                'ms.log_date',
                'ms.log_type',
                'ms.content',
                'iv.student_name',
                'iv.institution_name',
                'iv.student_id',
                'iv.institution_id'
            );

        if ($role === 'student' && $studentId) {
            $query->where('iv.student_id', $studentId);
        }

        $filters = [];
        $selections = [
            'title' => trim((string) $request->query('title', '')),
            'student_id' => $request->query('student_id'),
            'institution_id' => $request->query('institution_id'),
            'log_date_from' => $request->query('log_date_from'),
            'log_date_to' => $request->query('log_date_to'),
            'has_content' => $request->query('has_content', 'any'),
            'type' => $request->query('type'),
        ];

        if ($selections['title'] !== '') {
            $term = Str::lower($selections['title']);
            $query->whereRaw("LOWER(COALESCE(ms.title, '')) LIKE ?", ["%{$term}%"]);
            $filters['title'] = 'Title: ' . $selections['title'];
        }

        $students = DB::table('v_monitoring_log_summary as ms')
            ->join('internship_details_view as iv', 'iv.id', '=', 'ms.internship_id')
            ->when($role === 'student' && $studentId, fn ($q) => $q->where('iv.student_id', $studentId))
            ->select('iv.student_id', 'iv.student_name')
            ->whereNotNull('iv.student_id')
            ->groupBy('iv.student_id', 'iv.student_name')
            ->orderBy('iv.student_name')
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->student_id,
                'label' => $item->student_name,
            ]);
        $studentLabels = $students->pluck('label', 'id');

        if (($studentFilter = $selections['student_id']) !== null && $studentFilter !== '' && is_numeric($studentFilter)) {
            $studentFilter = (int) $studentFilter;
            $query->where('iv.student_id', $studentFilter);
            $filters['student_id'] = 'Student Name: ' . ($studentLabels->get($studentFilter) ?? $studentFilter);
            $selections['student_id'] = $studentFilter;
        } else {
            $selections['student_id'] = null;
        }

        $institutions = DB::table('v_monitoring_log_summary as ms')
            ->join('internship_details_view as iv', 'iv.id', '=', 'ms.internship_id')
            ->when($role === 'student' && $studentId, fn ($q) => $q->where('iv.student_id', $studentId))
            ->select('iv.institution_id', 'iv.institution_name')
            ->whereNotNull('iv.institution_id')
            ->groupBy('iv.institution_id', 'iv.institution_name')
            ->orderBy('iv.institution_name')
            ->get()
            ->map(fn ($item) => [
                'id' => (int) $item->institution_id,
                'label' => $item->institution_name,
            ]);
        $institutionLabels = $institutions->pluck('label', 'id');

        if (($institutionFilter = $selections['institution_id']) !== null && $institutionFilter !== '' && is_numeric($institutionFilter)) {
            $institutionFilter = (int) $institutionFilter;
            $query->where('iv.institution_id', $institutionFilter);
            $filters['institution_id'] = 'Institution Name: ' . ($institutionLabels->get($institutionFilter) ?? $institutionFilter);
            $selections['institution_id'] = $institutionFilter;
        } else {
            $selections['institution_id'] = null;
        }

        $logDateFrom = $selections['log_date_from'];
        $logDateTo = $selections['log_date_to'];
        if (!$this->isValidDate($logDateFrom)) {
            $logDateFrom = null;
            $selections['log_date_from'] = null;
        }
        if (!$this->isValidDate($logDateTo)) {
            $logDateTo = null;
            $selections['log_date_to'] = null;
        }
        if ($logDateFrom) {
            $query->whereDate('ms.log_date', '>=', $logDateFrom);
        }
        if ($logDateTo) {
            $query->whereDate('ms.log_date', '<=', $logDateTo);
        }
        if ($logDateFrom || $logDateTo) {
            if ($logDateFrom && $logDateTo) {
                $filters['log_date'] = 'Log Date: ' . $logDateFrom . ' - ' . $logDateTo;
            } elseif ($logDateFrom) {
                $filters['log_date'] = 'Log Date: ' . $logDateFrom;
            } else {
                $filters['log_date'] = 'Log Date: up to ' . $logDateTo;
            }
        }

        $hasContent = $selections['has_content'];
        $contentExpression = DB::getDriverName() === 'pgsql'
            ? "trim(coalesce(ms.content, ''))"
            : "TRIM(COALESCE(ms.content, ''))";
        if (in_array($hasContent, ['true', 'false'], true)) {
            if ($hasContent === 'true') {
                $query->whereRaw("$contentExpression <> ''");
                $filters['has_content'] = 'Have Content?: True';
            } else {
                $query->whereRaw("$contentExpression = ''");
                $filters['has_content'] = 'Have Content?: False';
            }
        } else {
            $selections['has_content'] = 'any';
        }

        if ($selections['type'] && in_array($selections['type'], $types, true)) {
            $query->where('ms.log_type', $selections['type']);
            $filters['type'] = 'Type: ' . $selections['type'];
        } else {
            $selections['type'] = null;
        }

        if ($search = trim((string) $request->query('q'))) {
            $term = Str::lower($search);
            $driver = DB::getDriverName();
            $dateCast = $driver === 'pgsql' ? 'ms.log_date::text' : 'CAST(ms.log_date AS CHAR)';
            $typeCast = $driver === 'pgsql' ? 'ms.log_type::text' : 'CAST(ms.log_type AS CHAR)';
            $query->where(function ($q) use ($term, $dateCast, $typeCast) {
                $q->whereRaw("LOWER(COALESCE(ms.title, '')) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($dateCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($typeCast) LIKE ?", ["%{$term}%"]);
            });
        }

        $logs = $query
            ->orderByDesc('ms.log_date')
            ->orderByDesc('ms.monitoring_log_id')
            ->paginate(10)
            ->withQueryString();

        return view('monitoring.index', [
            'logs' => $logs,
            'filters' => $filters,
            'students' => $students,
            'institutions' => $institutions,
            'types' => $types,
            'selections' => $selections,
        ]);
    }

    public function show($id)
    {
        $log = DB::table('v_monitoring_log_detail')->where('monitoring_log_id', $id)->first();
        abort_if(!$log, 404);

        if (session('role') === 'student') {
            $studentId = $this->currentStudentId();
            abort_unless($studentId && (int) $log->student_id === $studentId, 401);
        }

        return view('monitoring.show', compact('log'));
    }

    public function create()
    {
        if (session('role') === 'student') {
            abort(401);
        }

        $internships = $this->internshipOptions();
        $types = $this->typeOptions();

        return view('monitoring.create', [
            'internships' => $internships,
            'types' => $types,
        ]);
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }

        $types = $this->typeOptions();

        $data = $request->validate([
            'internship_id' => 'required|integer|exists:internships,id',
            'additional_internship_ids' => 'array',
            'additional_internship_ids.*' => 'integer|distinct|exists:internships,id',
            'log_date' => 'required|date',
            'type' => 'required|in:' . implode(',', $types),
            'title' => 'nullable|string|max:150',
            'content' => 'required|string',
            'apply_to_all' => 'nullable|boolean',
        ]);

        $baseInternshipId = (int) $data['internship_id'];
        $baseInternship = DB::table('internship_details_view')
            ->select('id', 'institution_id')
            ->where('id', $baseInternshipId)
            ->first();

        if (!$baseInternship) {
            throw ValidationException::withMessages([
                'internship_id' => 'Selected internship is invalid.',
            ]);
        }

        $additionalIds = collect($data['additional_internship_ids'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value !== $baseInternshipId)
            ->unique()
            ->values();

        if ($additionalIds->isNotEmpty()) {
            $invalid = DB::table('internship_details_view')
                ->whereIn('id', $additionalIds)
                ->where('institution_id', '!=', $baseInternship->institution_id)
                ->exists();
            if ($invalid) {
                throw ValidationException::withMessages([
                    'additional_internship_ids' => 'Additional internships must belong to the same institution.',
                ]);
            }
        }

        $title = $data['title'] !== null && trim($data['title']) !== '' ? trim($data['title']) : null;
        $content = trim($data['content']);

        $targetIds = collect([$baseInternshipId])->merge($additionalIds);

        if ($request->boolean('apply_to_all')) {
            $institutionInternships = DB::table('internship_details_view')
                ->where('institution_id', $baseInternship->institution_id)
                ->pluck('id');
            $targetIds = $targetIds->merge($institutionInternships);
        }

        $targetIds = $targetIds->unique()->values();

        DB::transaction(function () use ($targetIds, $baseInternshipId, $data, $title, $content) {
            foreach ($targetIds as $internshipId) {
                if ((int) $internshipId !== $baseInternshipId && $this->monitoringExists((int) $internshipId, $data['log_date'], $data['type'])) {
                    continue;
                }

                MonitoringLog::create([
                    'internship_id' => (int) $internshipId,
                    'log_date' => $data['log_date'],
                    'type' => $data['type'],
                    'title' => $title,
                    'content' => $content,
                    'supervisor_id' => null,
                ]);
            }
        });

        return redirect('/monitorings');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }

        $log = DB::table('v_monitoring_log_detail')->where('monitoring_log_id', $id)->first();
        abort_if(!$log, 404);

        $internships = $this->internshipOptions();
        $types = $this->typeOptions();

        return view('monitoring.edit', [
            'log' => $log,
            'internships' => $internships,
            'types' => $types,
        ]);
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }

        $log = MonitoringLog::findOrFail($id);
        $types = $this->typeOptions();

        $data = $request->validate([
            'internship_id' => 'required|integer|exists:internships,id',
            'additional_internship_ids' => 'array',
            'additional_internship_ids.*' => 'integer|distinct|exists:internships,id',
            'log_date' => 'required|date',
            'type' => 'required|in:' . implode(',', $types),
            'title' => 'nullable|string|max:150',
            'content' => 'required|string',
            'apply_to_all' => 'nullable|boolean',
        ]);

        $baseInternshipId = (int) $data['internship_id'];
        if ($baseInternshipId !== (int) $log->internship_id) {
            throw ValidationException::withMessages([
                'internship_id' => 'Internship cannot be changed for this monitoring log.',
            ]);
        }

        $baseInternship = DB::table('internship_details_view')
            ->select('id', 'institution_id')
            ->where('id', $baseInternshipId)
            ->first();

        if (!$baseInternship) {
            throw ValidationException::withMessages([
                'internship_id' => 'Selected internship is invalid.',
            ]);
        }

        $additionalIds = collect($data['additional_internship_ids'] ?? [])
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value !== $baseInternshipId)
            ->unique()
            ->values();

        if ($additionalIds->isNotEmpty()) {
            $invalid = DB::table('internship_details_view')
                ->whereIn('id', $additionalIds)
                ->where('institution_id', '!=', $baseInternship->institution_id)
                ->exists();
            if ($invalid) {
                throw ValidationException::withMessages([
                    'additional_internship_ids' => 'Additional internships must belong to the same institution.',
                ]);
            }
        }

        $title = $data['title'] !== null && trim($data['title']) !== '' ? trim($data['title']) : null;
        $content = trim($data['content']);

        DB::transaction(function () use ($log, $data, $title, $content, $request, $baseInternship, $additionalIds, $baseInternshipId) {
            $log->update([
                'log_date' => $data['log_date'],
                'type' => $data['type'],
                'title' => $title,
                'content' => $content,
                'supervisor_id' => null,
            ]);

            $targetIds = collect([]);
            if ($request->boolean('apply_to_all')) {
                $targetIds = $targetIds->merge(
                    DB::table('internship_details_view')
                        ->where('institution_id', $baseInternship->institution_id)
                        ->pluck('id')
                );
            }
            $targetIds = $targetIds->merge($additionalIds)->unique()->values();

            foreach ($targetIds as $internshipId) {
                $internshipId = (int) $internshipId;
                if ($internshipId === $baseInternshipId) {
                    continue;
                }

                if ($this->monitoringExists($internshipId, $data['log_date'], $data['type'])) {
                    continue;
                }

                MonitoringLog::create([
                    'internship_id' => $internshipId,
                    'log_date' => $data['log_date'],
                    'type' => $data['type'],
                    'title' => $title,
                    'content' => $content,
                    'supervisor_id' => null,
                ]);
            }
        });

        return redirect('/monitorings');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }

        $log = MonitoringLog::findOrFail($id);
        $log->delete();

        return redirect('/monitorings');
    }
}
