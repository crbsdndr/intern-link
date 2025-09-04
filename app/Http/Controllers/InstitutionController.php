<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionContact;
use App\Models\InstitutionQuota;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InstitutionController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = ['name', 'city', 'province', 'industry'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'city', 'province', 'industry'];

    protected function loadRegions(): array
    {
        $cities = json_decode(file_get_contents(resource_path('data/cities.json')), true);
        $provinces = json_decode(file_get_contents(resource_path('data/provinces.json')), true);
        return [$cities, $provinces];
    }

    public function index(Request $request)
    {
        $query = DB::table('institution_details_view as iv')
            ->join('institutions as i', 'i.id', '=', 'iv.id')
            ->select('iv.id', 'iv.name', 'iv.city', 'iv.province', 'iv.industry', 'i.created_at', 'i.updated_at');

        if (session('role') === 'student') {
            $studentId = $this->currentStudentId();
            $query->whereIn('iv.id', function ($q) use ($studentId) {
                $q->select('institution_id')->from('applications')->where('student_id', $studentId)
                    ->union(
                        DB::table('internships')->select('institution_id')->where('student_id', $studentId)
                    );
            });
        }

        $filters = [];

        if ($city = $request->query('city~')) {
            $query->where('iv.city', 'like', '%' . $city . '%');
            $filters['city~'] = 'City: ' . $city;
        }

        if ($province = $request->query('province~')) {
            $query->where('iv.province', 'like', '%' . $province . '%');
            $filters['province~'] = 'Province: ' . $province;
        }

        if ($created = $request->query('created_at')) {
            if (Str::startsWith($created, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($created, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('i.created_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('i.created_at', '<=', $end);
                }
                $filters['created_at'] = 'Created: ' . $start . ' - ' . $end;
            }
        }

        if ($updated = $request->query('updated_at')) {
            if (Str::startsWith($updated, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($updated, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('i.updated_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('i.updated_at', '<=', $end);
                }
                $filters['updated_at'] = 'Updated: ' . $start . ' - ' . $end;
            }
        }

        if ($q = trim($request->query('q', ''))) {
            $qLower = strtolower($q);
            $query->where(function ($sub) use ($qLower) {
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(iv.' . $col . ') LIKE ?', ['%' . $qLower . '%']);
                }
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = array_merge(self::DISPLAY_COLUMNS, ['created_at', 'updated_at']);
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $institutions = $query->paginate(10)->withQueryString();

        return view('institution.index', [
            'institutions' => $institutions,
            'filters' => $filters,
        ]);
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
            'industry' => 'required|string|max:100',
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
                'industry' => $data['industry'],
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
            'industry' => 'required|string|max:100',
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
                'industry' => $data['industry'],
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
