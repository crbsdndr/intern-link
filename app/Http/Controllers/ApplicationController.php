<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function index()
    {
        $applications = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name', 'period_year', 'period_term')
            ->orderBy('id')
            ->get();
        return view('application.index', compact('applications'));
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
