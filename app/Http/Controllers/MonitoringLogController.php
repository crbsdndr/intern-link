<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MonitoringLogController extends Controller
{
    private function typeOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::monitor_type_enum)) AS type"))->pluck('type')->all();
        }
        return ['weekly','issue','final','other'];
    }

    private function supervisorOptions()
    {
        return DB::table('supervisor_details_view')->select('id','name')->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        $query = DB::table('monitoring_logs as ml')
            ->join('internship_details_view as it', 'it.id', '=', 'ml.internship_id')
            ->leftJoin('supervisor_details_view as sv', 'sv.id', '=', 'ml.supervisor_id')
            ->select('ml.id as id', 'ml.log_date', 'it.student_name', 'it.institution_name', 'sv.name as supervisor_name', 'ml.type as log_type', 'ml.score', 'ml.title', 'ml.content', 'ml.created_at', 'ml.updated_at');

        $filters = [];

        foreach (['log_date' => 'Date', 'created_at' => 'Created', 'updated_at' => 'Updated'] as $param => $label) {
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
            $dateCast = $driver === 'pgsql' ? 'ml.log_date::text' : 'CAST(ml.log_date AS CHAR)';
            $scoreCast = $driver === 'pgsql' ? 'ml.score::text' : 'CAST(ml.score AS CHAR)';
            $typeCast = $driver === 'pgsql' ? 'ml.type::text' : 'CAST(ml.type AS CHAR)';
            $query->where(function ($q) use ($term, $dateCast, $scoreCast, $typeCast) {
                $q->whereRaw('LOWER(ml.title) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER($typeCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw('LOWER(it.student_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(it.institution_name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(sv.name) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw("LOWER($dateCast) LIKE ?", ["%{$term}%"])
                    ->orWhereRaw("LOWER($scoreCast) LIKE ?", ["%{$term}%"]);
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = ['log_date', 'created_at', 'updated_at'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir)->orderByDesc('id');

        $logs = $query->paginate(10)->withQueryString();

        return view('monitoring.index', [
            'logs' => $logs,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $log = DB::table('v_monitoring_log_detail')->where('monitoring_log_id', $id)->first();
        abort_if(!$log, 404);
        return view('monitoring.show', compact('log'));
    }

    public function create()
    {
        $internships = DB::table('internship_details_view')
            ->select('id','student_name','institution_name')
            ->orderBy('student_name')
            ->get();
        $supervisors = $this->supervisorOptions();
        $types = $this->typeOptions();
        return view('monitoring.create', compact('internships','supervisors','types'));
    }

    public function store(Request $request)
    {
        $types = $this->typeOptions();
        $data = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'log_date' => 'required|date',
            'supervisor_id' => 'nullable|exists:supervisors,id',
            'type' => 'required|in:' . implode(',', $types),
            'score' => 'nullable|integer|min:0|max:100',
            'title' => 'nullable|string|max:150',
            'content' => 'required|string',
        ]);
        MonitoringLog::create($data);
        return redirect('/monitoring');
    }

    public function edit($id)
    {
        $log = DB::table('v_monitoring_log_detail')->where('monitoring_log_id', $id)->first();
        abort_if(!$log, 404);
        $internships = collect([(object)[
            'id' => $log->internship_id,
            'student_name' => $log->student_name,
            'institution_name' => $log->institution_name,
        ]]);
        $supervisors = $this->supervisorOptions();
        $types = $this->typeOptions();
        return view('monitoring.edit', compact('log','internships','supervisors','types'));
    }

    public function update(Request $request, $id)
    {
        $log = MonitoringLog::findOrFail($id);
        $types = $this->typeOptions();
        $data = $request->validate([
            'internship_id' => 'required|exists:internships,id',
            'log_date' => 'required|date',
            'supervisor_id' => 'nullable|exists:supervisors,id',
            'type' => 'required|in:' . implode(',', $types),
            'score' => 'nullable|integer|min:0|max:100',
            'title' => 'nullable|string|max:150',
            'content' => 'required|string',
        ]);
        $log->update($data);
        return redirect('/monitoring');
    }

    public function destroy($id)
    {
        $log = MonitoringLog::findOrFail($id);
        $log->delete();
        return redirect('/monitoring');
    }
}
