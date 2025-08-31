<?php

namespace App\Http\Controllers;

use App\Models\Internship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InternshipController extends Controller
{

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
        $periods = DB::table('periods')->select('id','year','term')->orderBy('year','desc')->orderBy('term','desc')->get();
        return view('internship.create', compact('applications','periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'period_id' => 'required|exists:periods,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $application = DB::table('application_details_view')->where('id', $data['application_id'])->first();
        if (!$application) {
            return back()->withErrors(['application_id' => 'Invalid application'])->withInput();
        }
        if ($application->period_id != $data['period_id']) {
            return back()->withErrors(['period_id' => 'Selected period does not match application period'])->withInput();
        }

        Internship::create([
            'application_id' => $data['application_id'],
            'student_id' => $application->student_id,
            'institution_id' => $application->institution_id,
            'period_id' => $data['period_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
        ]);
        return redirect('/internship');
    }

    public function edit($id)
    {
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        $applications = DB::table('application_details_view')->where('status','accepted')->orderBy('student_name')->get();
        $periods = DB::table('periods')->select('id','year','term')->orderBy('year','desc')->orderBy('term','desc')->get();
        return view('internship.edit', compact('internship','applications','periods'));
    }

    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'period_id' => 'required|exists:periods,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        $application = DB::table('application_details_view')->where('id', $data['application_id'])->first();
        if (!$application) {
            return back()->withErrors(['application_id' => 'Invalid application'])->withInput();
        }
        if ($application->period_id != $data['period_id']) {
            return back()->withErrors(['period_id' => 'Selected period does not match application period'])->withInput();
        }

        $internship->update([
            'application_id' => $data['application_id'],
            'student_id' => $application->student_id,
            'institution_id' => $application->institution_id,
            'period_id' => $data['period_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'notes' => $data['notes'] ?? null,
        ]);
        return redirect('/internship');
    }

    public function destroy($id)
    {
        $internship = Internship::findOrFail($id);
        $internship->delete();
        return redirect('/internship');
    }
}
