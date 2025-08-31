<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{
    private function statusOptions(): array
    {
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            return collect(DB::select("SELECT unnest(enum_range(NULL::internship_status_enum)) AS status"))->pluck('status')->all();
        }
        return ['planned','ongoing','completed','terminated'];
    }

    public function index()
    {
        $internships = DB::table('internship_details_view')
            ->select('id','student_name','institution_name','start_date','end_date')
            ->orderBy('id')
            ->get();
        return view('internship.index', compact('internships'));
    }

    public function show($id)
    {
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        return view('internship.show', compact('internship'));
    }

    public function create()
    {
        $applications = DB::table('application_details_view')->where('status','accepted')->orderBy('student_name')->get();
        $students = DB::table('student_details_view')->select('id','name')->orderBy('name')->get();
        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $periods = DB::table('periods')->select('id','year','term')->orderBy('year','desc')->orderBy('term','desc')->get();
        $statuses = $this->statusOptions();
        return view('internship.create', compact('applications','students','institutions','periods','statuses'));
    }

    public function store(Request $request)
    {
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'student_id' => 'required|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'period_id' => 'required|exists:periods,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);

        $application = DB::table('application_details_view')->where('id', $data['application_id'])->first();
        if (!$application ||
            $application->student_id != $data['student_id'] ||
            $application->institution_id != $data['institution_id'] ||
            $application->period_id != $data['period_id']) {
            return back()->withErrors(['application_id' => 'Application does not match selected student, institution, or period'])->withInput();
        }

        Internship::create($data);
        return redirect('/internship');
    }

    public function edit($id)
    {
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        $applications = DB::table('application_details_view')->where('status','accepted')->orderBy('student_name')->get();
        $students = DB::table('student_details_view')->select('id','name')->orderBy('name')->get();
        $institutions = DB::table('institutions')->select('id','name')->orderBy('name')->get();
        $periods = DB::table('periods')->select('id','year','term')->orderBy('year','desc')->orderBy('term','desc')->get();
        $statuses = $this->statusOptions();
        return view('internship.edit', compact('internship','applications','students','institutions','periods','statuses'));
    }

    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'student_id' => 'required|exists:students,id',
            'institution_id' => 'required|exists:institutions,id',
            'period_id' => 'required|exists:periods,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);
        $application = DB::table('application_details_view')->where('id', $data['application_id'])->first();
        if (!$application ||
            $application->student_id != $data['student_id'] ||
            $application->institution_id != $data['institution_id'] ||
            $application->period_id != $data['period_id']) {
            return back()->withErrors(['application_id' => 'Application does not match selected student, institution, or period'])->withInput();
        }
        $internship->update($data);
        return redirect('/internship');
    }

    public function destroy($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return redirect('/internship');
    }
}
