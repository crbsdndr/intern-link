<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionContact;
use App\Models\InstitutionQuota;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitutionController extends Controller
{
    protected function loadRegions(): array
    {
        $cities = json_decode(file_get_contents(resource_path('data/cities.json')), true);
        $provinces = json_decode(file_get_contents(resource_path('data/provinces.json')), true);
        return [$cities, $provinces];
    }

    public function index()
    {
        $query = DB::table('institution_details_view')
            ->select('id', 'name', 'city', 'province')
            ->orderBy('name');
        if (session('role') === 'student') {
            $studentId = $this->currentStudentId();
            $query->whereIn('id', function ($q) use ($studentId) {
                $q->select('institution_id')->from('applications')->where('student_id', $studentId)
                    ->union(
                        DB::table('internships')->select('institution_id')->where('student_id', $studentId)
                    );
            });
        }
        $institutions = $query->get();
        return view('institution.index', compact('institutions'));
    }

    public function show($id)
    {
        $institution = DB::table('institution_details_view')->where('id', $id)->first();
        abort_if(!$institution, 404);
        if (session('role') === 'student') {
            $studentId = $this->currentStudentId();
            $related = DB::table('applications')->where('student_id', $studentId)->where('institution_id', $id)->exists() ||
                DB::table('internships')->where('student_id', $studentId)->where('institution_id', $id)->exists();
            if (!$related) {
                abort(401);
            }
        }
        return view('institution.show', compact('institution'));
    }

    public function create()
    {
        if (session('role') === 'student') {
            abort(401);
        }
        [$cities, $provinces] = $this->loadRegions();
        return view('institution.create', compact('cities', 'provinces'));
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $data = $request->validate([
            'name' => 'required|string|unique:institutions,name',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'website' => 'nullable|string',
            'photo' => 'nullable|string',
            'contact_name' => 'required|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'contact_position' => 'nullable|string',
            'contact_primary' => 'required|boolean',
            'period_year' => 'required|integer',
            'period_term' => 'required|integer',
            'quota' => 'required|integer|min:0',
            'used' => 'required|integer|min:0|lte:quota',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $institution = Institution::create([
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'province' => $data['province'],
                'website' => $data['website'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]);

            InstitutionContact::create([
                'institution_id' => $institution->id,
                'name' => $data['contact_name'],
                'email' => $data['contact_email'] ?? null,
                'phone' => $data['contact_phone'] ?? null,
                'position' => $data['contact_position'] ?? null,
                'is_primary' => $data['contact_primary'],
            ]);

            $period = Period::firstOrCreate([
                'year' => $data['period_year'],
                'term' => $data['period_term'],
            ]);

            InstitutionQuota::create([
                'institution_id' => $institution->id,
                'period_id' => $period->id,
                'quota' => $data['quota'],
                'used' => $data['used'],
                'notes' => $data['notes'] ?? null,
            ]);
        });

        return redirect('/institution');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $inst = Institution::findOrFail($id);
        $contact = $inst->contacts()->orderByDesc('is_primary')->first();
        $quota = $inst->quotas()->with('period')->orderByDesc('period_id')->first();
        $institution = (object) array_merge($inst->toArray(), [
            'contact_name' => $contact->name ?? null,
            'contact_email' => $contact->email ?? null,
            'contact_phone' => $contact->phone ?? null,
            'contact_position' => $contact->position ?? null,
            'contact_primary' => $contact->is_primary ?? false,
            'period_year' => optional($quota->period ?? null)->year,
            'period_term' => optional($quota->period ?? null)->term,
            'quota' => $quota->quota ?? null,
            'used' => $quota->used ?? null,
            'notes' => $quota->notes ?? null,
        ]);
        [$cities, $provinces] = $this->loadRegions();
        return view('institution.edit', compact('institution', 'cities', 'provinces'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $institution = Institution::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|unique:institutions,name,' . $institution->id,
            'address' => 'nullable|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'website' => 'nullable|string',
            'photo' => 'nullable|string',
            'contact_name' => 'required|string',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string',
            'contact_position' => 'nullable|string',
            'contact_primary' => 'required|boolean',
            'period_year' => 'required|integer',
            'period_term' => 'required|integer',
            'quota' => 'required|integer|min:0',
            'used' => 'required|integer|min:0|lte:quota',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $institution) {
            $institution->update([
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'province' => $data['province'],
                'website' => $data['website'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]);

            $contact = $institution->contacts()->orderByDesc('is_primary')->first();
            if ($contact) {
                $contact->update([
                    'name' => $data['contact_name'],
                    'email' => $data['contact_email'] ?? null,
                    'phone' => $data['contact_phone'] ?? null,
                    'position' => $data['contact_position'] ?? null,
                    'is_primary' => $data['contact_primary'],
                ]);
            } else {
                $institution->contacts()->create([
                    'name' => $data['contact_name'],
                    'email' => $data['contact_email'] ?? null,
                    'phone' => $data['contact_phone'] ?? null,
                    'position' => $data['contact_position'] ?? null,
                    'is_primary' => $data['contact_primary'],
                ]);
            }

            $period = Period::firstOrCreate([
                'year' => $data['period_year'],
                'term' => $data['period_term'],
            ]);

            $quota = InstitutionQuota::firstOrNew([
                'institution_id' => $institution->id,
                'period_id' => $period->id,
            ]);
            $quota->fill([
                'quota' => $data['quota'],
                'used' => $data['used'],
                'notes' => $data['notes'] ?? null,
            ]);
            $quota->save();
        });

        return redirect('/institution');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $institution = Institution::findOrFail($id);
        $institution->delete();
        return redirect('/institution');
    }
}
