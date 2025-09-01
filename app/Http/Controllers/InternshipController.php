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
        return ['planned', 'ongoing', 'completed', 'terminated'];
    }

    public function index()
    {
        $internships = DB::table('internship_details_view')
            ->select('id', 'student_name', 'institution_name', 'start_date', 'end_date')
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
        $applications = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name')
            ->orderBy('id')
            ->get();
        $statuses = $this->statusOptions();
        return view('internship.create', compact('applications', 'statuses'));
    }

    public function store(Request $request)
    {
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);

        $app = DB::table('applications')
            ->select('student_id', 'institution_id', 'period_id')
            ->where('id', $data['application_id'])
            ->first();
        abort_if(!$app, 400);
        $data['student_id'] = $app->student_id;
        $data['institution_id'] = $app->institution_id;
        $data['period_id'] = $app->period_id;

        Internship::create($data);
        return redirect('/internship');
    }

    public function edit($id)
    {
        $internship = DB::table('internship_details_view')->where('id', $id)->first();
        abort_if(!$internship, 404);
        $applications = DB::table('application_details_view')
            ->select('id', 'student_name', 'institution_name')
            ->orderBy('id')
            ->get();
        $statuses = $this->statusOptions();
        return view('internship.edit', compact('internship', 'applications', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $internship = Internship::findOrFail($id);
        $statuses = $this->statusOptions();
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:' . implode(',', $statuses),
        ]);

        $app = DB::table('applications')
            ->select('student_id', 'institution_id', 'period_id')
            ->where('id', $data['application_id'])
            ->first();
        abort_if(!$app, 400);
        $data['student_id'] = $app->student_id;
        $data['institution_id'] = $app->institution_id;
        $data['period_id'] = $app->period_id;

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
