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
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = [
        'name',
        'city',
        'province',
        'industry',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_position',
    ];

    private const DISPLAY_COLUMNS = [
        'name',
        'city',
        'province',
        'industry',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_position',
        'period_year',
        'period_term',
        'quota',
        'used',
    ];

    protected function loadRegions(): array
    {
        $cities = json_decode(file_get_contents(resource_path('data/cities.json')), true);
        $provinces = json_decode(file_get_contents(resource_path('data/provinces.json')), true);
        return [$cities, $provinces];
    }

    protected function loadIndustries(): array
    {
        return DB::table('institutions')
            ->whereNotNull('industry')
            ->where('industry', '<>', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry')
            ->toArray();
    }

    public function index(Request $request)
    {
        $query = DB::table('institution_details_view as iv')
            ->join('institutions as i', 'i.id', '=', 'iv.id')
            ->select([
                'iv.id',
                'iv.name',
                'iv.address',
                'iv.city',
                'iv.province',
                'iv.website',
                'iv.industry',
                'iv.notes',
                'iv.photo',
                'iv.contact_name',
                'iv.contact_email',
                'iv.contact_phone',
                'iv.contact_position',
                'iv.contact_primary',
                'iv.period_year',
                'iv.period_term',
                'iv.quota',
                'iv.used',
                'i.created_at',
                'i.updated_at',
            ]);

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

        $textFilters = [
            'name' => 'Name',
            'address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'website' => 'Website',
            'industry' => 'Industry',
            'contact_name' => 'Contact Name',
            'contact_email' => 'Contact E-Mail',
            'contact_phone' => 'Contact Phone',
            'contact_position' => 'Contact Position',
        ];

        foreach ($textFilters as $param => $label) {
            $value = trim((string) $request->query($param, ''));
            if ($value !== '') {
                $query->where("iv.$param", 'like', '%' . $value . '%');
                $filters[$param] = $label . ': ' . $value;
            }
        }

        $hasNotes = $request->query('has_notes');
        if ($hasNotes === 'true') {
            $query->whereNotNull('iv.notes')->where('iv.notes', '<>', '');
            $filters['has_notes'] = 'Have Notes: True';
        } elseif ($hasNotes === 'false') {
            $query->where(function ($sub) {
                $sub->whereNull('iv.notes')->orWhere('iv.notes', '=', '');
            });
            $filters['has_notes'] = 'Have Notes: False';
        }

        $hasPhoto = $request->query('has_photo');
        if ($hasPhoto === 'true') {
            $query->whereNotNull('iv.photo')->where('iv.photo', '<>', '');
            $filters['has_photo'] = 'Have Photo: True';
        } elseif ($hasPhoto === 'false') {
            $query->where(function ($sub) {
                $sub->whereNull('iv.photo')->orWhere('iv.photo', '=', '');
            });
            $filters['has_photo'] = 'Have Photo: False';
        }

        $contactPrimary = $request->query('contact_primary');
        if ($contactPrimary === 'true') {
            $query->where('iv.contact_primary', true);
            $filters['contact_primary'] = 'Contact Primary: True';
        } elseif ($contactPrimary === 'false') {
            $query->where('iv.contact_primary', false);
            $filters['contact_primary'] = 'Contact Primary: False';
        }

        $numberFilters = [
            'period_year' => 'Period Year',
            'period_term' => 'Period Term',
            'quota' => 'Quota',
            'used' => 'Quota Used',
        ];

        foreach ($numberFilters as $param => $label) {
            $value = $request->query($param, null);
            if ($value !== null && $value !== '') {
                $query->where("iv.$param", (int) $value);
                $filters[$param] = $label . ': ' . $value;
            }
        }

        if ($q = trim($request->query('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $lower = strtolower($q);
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(iv.' . $col . ') LIKE ?', ['%' . $lower . '%']);
                }

                if (is_numeric($q)) {
                    $numeric = (int) $q;
                    $sub->orWhere('iv.period_year', '=', $numeric)
                        ->orWhere('iv.period_term', '=', $numeric)
                        ->orWhere('iv.quota', '=', $numeric)
                        ->orWhere('iv.used', '=', $numeric);
                }
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $sortableColumns = array_merge(
            array_fill_keys(self::DISPLAY_COLUMNS, null),
            ['id' => null, 'created_at' => null, 'updated_at' => null]
        );

        if (!array_key_exists($sortField, $sortableColumns)) {
            $sortField = 'created_at';
        }

        $sortMappings = [
            'created_at' => 'i.created_at',
            'updated_at' => 'i.updated_at',
            'id' => 'iv.id',
        ];
        $column = $sortMappings[$sortField] ?? ('iv.' . $sortField);
        $sortDir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($column, $sortDir);

        $institutions = $query->paginate(10)->withQueryString();

        [$cities, $provinces] = $this->loadRegions();
        $industries = $this->loadIndustries();

        return view('institution.index', [
            'institutions' => $institutions,
            'filters' => $filters,
            'cities' => $cities,
            'provinces' => $provinces,
            'industries' => $industries,
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
        $periods = Period::orderByDesc('year')->orderByDesc('term')->get();
        $industries = $this->loadIndustries();
        return view('institution.create', compact('cities', 'provinces', 'periods', 'industries'));
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $data = $request->validate([
            'name' => 'required|string|max:150|unique:institutions,name',
            'address' => 'nullable|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'website' => 'nullable|string|max:255',
            'industry' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_position' => 'nullable|string|max:100',
            'contact_primary' => 'nullable|in:true,false',
            'period_selection' => 'required|string',
            'new_period_year' => 'required_if:period_selection,create_new|integer',
            'new_period_term' => 'required_if:period_selection,create_new|integer',
            'quota' => 'required|integer|min:0',
        ]);

        if ($data['period_selection'] !== 'create_new' && !Period::whereKey($data['period_selection'])->exists()) {
            return back()->withErrors(['period_selection' => 'Selected period is invalid.'])->withInput();
        }

        $contactPrimary = ($data['contact_primary'] ?? '') === 'true';

        DB::transaction(function () use ($data, $contactPrimary) {
            $institution = Institution::create([
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'province' => $data['province'],
                'website' => $data['website'] ?? null,
                'industry' => $data['industry'],
                'notes' => $data['notes'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]);

            InstitutionContact::create([
                'institution_id' => $institution->id,
                'name' => $data['contact_name'],
                'email' => $data['contact_email'] ?? null,
                'phone' => $data['contact_phone'] ?? null,
                'position' => $data['contact_position'] ?? null,
                'is_primary' => $contactPrimary,
            ]);

            $period = $this->resolvePeriod(
                $data['period_selection'],
                $data['new_period_year'] ?? null,
                $data['new_period_term'] ?? null
            );

            InstitutionQuota::create([
                'institution_id' => $institution->id,
                'period_id' => $period->id,
                'quota' => $data['quota'],
                'used' => 0,
            ]);
        });

        return redirect('/institutions');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $inst = Institution::findOrFail($id);
        $contact = $inst->contacts()->orderByDesc('is_primary')->first();
        $quota = $inst->quotas()->with('period')->orderByDesc('period_id')->first();
        $institution = (object) [
            'id' => $inst->id,
            'name' => $inst->name,
            'address' => $inst->address,
            'city' => $inst->city,
            'province' => $inst->province,
            'website' => $inst->website,
            'industry' => $inst->industry,
            'notes' => $inst->notes,
            'photo' => $inst->photo,
            'contact_name' => $contact->name ?? null,
            'contact_email' => $contact->email ?? null,
            'contact_phone' => $contact->phone ?? null,
            'contact_position' => $contact->position ?? null,
            'contact_primary' => $contact->is_primary ?? false,
            'period_id' => $quota->period_id ?? null,
            'period_year' => optional($quota->period ?? null)->year,
            'period_term' => optional($quota->period ?? null)->term,
            'quota' => $quota->quota ?? null,
        ];
        [$cities, $provinces] = $this->loadRegions();
        $periods = Period::orderByDesc('year')->orderByDesc('term')->get();
        $industries = $this->loadIndustries();
        return view('institution.edit', compact('institution', 'cities', 'provinces', 'periods', 'industries'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $institution = Institution::findOrFail($id);
        $data = $request->validate([
            'address' => 'nullable|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'website' => 'nullable|string|max:255',
            'industry' => 'required|string|max:100',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string|max:255',
            'contact_name' => 'required|string|max:150',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_position' => 'nullable|string|max:100',
            'contact_primary' => 'nullable|in:true,false',
            'period_selection' => 'required|string',
            'new_period_year' => 'required_if:period_selection,create_new|integer',
            'new_period_term' => 'required_if:period_selection,create_new|integer',
            'quota' => 'required|integer|min:0',
        ]);

        if ($data['period_selection'] !== 'create_new' && !Period::whereKey($data['period_selection'])->exists()) {
            return back()->withErrors(['period_selection' => 'Selected period is invalid.'])->withInput();
        }

        $contactPrimary = ($data['contact_primary'] ?? '') === 'true';

        DB::transaction(function () use ($data, $institution, $contactPrimary) {
            $institution->update([
                'address' => $data['address'] ?? null,
                'city' => $data['city'],
                'province' => $data['province'],
                'website' => $data['website'] ?? null,
                'industry' => $data['industry'],
                'notes' => $data['notes'] ?? null,
                'photo' => $data['photo'] ?? null,
            ]);

            $contact = $institution->contacts()->orderByDesc('is_primary')->first();
            $contactPayload = [
                'name' => $data['contact_name'],
                'email' => $data['contact_email'] ?? null,
                'phone' => $data['contact_phone'] ?? null,
                'position' => $data['contact_position'] ?? null,
                'is_primary' => $contactPrimary,
            ];

            if ($contact) {
                $contact->update($contactPayload);
            } else {
                $institution->contacts()->create($contactPayload);
            }

            $period = $this->resolvePeriod(
                $data['period_selection'],
                $data['new_period_year'] ?? null,
                $data['new_period_term'] ?? null
            );

            $quota = InstitutionQuota::firstOrNew([
                'institution_id' => $institution->id,
                'period_id' => $period->id,
            ]);

            $quota->quota = $data['quota'];
            if (!$quota->exists) {
                $quota->used = 0;
            }
            $quota->save();
        });

        return redirect('/institutions');
    }

    private function resolvePeriod(string $selection, ?int $newYear, ?int $newTerm): Period
    {
        if ($selection === 'create_new') {
            return Period::firstOrCreate([
                'year' => $newYear,
                'term' => $newTerm,
            ]);
        }

        return Period::findOrFail((int) $selection);
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $institution = Institution::findOrFail($id);
        $institution->delete();
        return redirect('/institutions');
    }
}
