<?php

namespace App\Http\Controllers;

use App\Models\MonitoringLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function index()
    {
        $logs = DB::table('v_monitoring_log_summary')
            ->select('monitoring_log_id as id','log_date','student_name','institution_name','supervisor_name','log_type','score','title','content')
            ->orderByDesc('log_date')
            ->orderByDesc('id')
            ->get();
        return view('monitoring.index', compact('logs'));
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
