@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Institutions')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Institutions</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="institution-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
                <input type="search" name="q" id="institution-search-input" class="form-control" placeholder="Search institutions" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="institution-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#institutionFilter" aria-controls="institutionFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Institution</button>
        @else
            <a href="/institutions/create" class="btn btn-primary">Create Institution</a>
        @endif
    </div>
</div>

@if(count($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $param => $label)
            @php($query = Arr::except(request()->query(), [$param, 'page']))
            @php($queryString = http_build_query(array_filter($query, fn($value) => $value !== null && $value !== '')))
            <a href="{{ url()->current() . ($queryString ? '?' . $queryString : '') }}" class="btn btn-sm btn-outline-secondary">
                {{ $label }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-3">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">City</th>
                <th scope="col">Province</th>
                <th scope="col">Industry</th>
                <th scope="col">Contact Name</th>
                <th scope="col">Contact E-Mail</th>
                <th scope="col">Contact Phone</th>
                <th scope="col">Contact Position</th>
                <th scope="col">Period Year</th>
                <th scope="col">Period Term</th>
                <th scope="col">Quota</th>
                <th scope="col">Quota Used</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($institutions as $institution)
            <tr>
                <td>{{ $institution->name }}</td>
                <td>{{ $institution->city }}</td>
                <td>{{ $institution->province }}</td>
                <td>{{ $institution->industry }}</td>
                <td>{{ $institution->contact_name ?? '—' }}</td>
                <td>{{ $institution->contact_email ?? '—' }}</td>
                <td>{{ $institution->contact_phone ?? '—' }}</td>
                <td>{{ $institution->contact_position ?? '—' }}</td>
                <td>{{ $institution->period_year ?? '—' }}</td>
                <td>{{ $institution->period_term ?? '—' }}</td>
                <td>{{ $institution->quota ?? '—' }}</td>
                <td>{{ $institution->used ?? '—' }}</td>
                <td class="text-nowrap">
                    <a href="/institutions/{{ $institution->id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    @if(!$isStudent)
                        <a href="/institutions/{{ $institution->id }}/update" class="btn btn-sm btn-warning">Update</a>
                        <form action="/institutions/{{ $institution->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this institution?');">Delete</button>
                        </form>
                    @else
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class="text-center">No institutions found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Institutions: {{ $institutions->total() }}</p>
<p class="text-muted">Page {{ $institutions->currentPage() }} out of {{ $institutions->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($institutions->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $institutions->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($institutions->hasMorePages())
        <a href="{{ $institutions->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="institutionFilter" aria-labelledby="institutionFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="institutionFilterLabel">Filter Institutions</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="institution-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div>
                <label class="form-label" for="filter-name">Name</label>
                <input type="text" class="form-control" id="filter-name" name="name" value="{{ request('name') }}">
            </div>
            <div>
                <label class="form-label" for="filter-address">Address</label>
                <textarea class="form-control" id="filter-address" name="address" rows="2">{{ request('address') }}</textarea>
            </div>
            <div>
                <label class="form-label" for="filter-city">City</label>
                <select class="form-select tom-select" id="filter-city" name="city">
                    <option value="">Select city</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="filter-province">Province</label>
                <select class="form-select tom-select" id="filter-province" name="province">
                    <option value="">Select province</option>
                    @foreach($provinces as $province)
                        <option value="{{ $province }}" @selected(request('province') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="filter-website">Website</label>
                <input type="text" class="form-control" id="filter-website" name="website" value="{{ request('website') }}">
            </div>
            <div>
                <label class="form-label" for="filter-industry">Industry</label>
                <select class="form-select tom-select" id="filter-industry" name="industry" data-tom-create="true">
                    <option value="">Select industry</option>
                    @foreach($industries as $industry)
                        <option value="{{ $industry }}" @selected(request('industry') === $industry)>{{ $industry }}</option>
                    @endforeach
                    @if(request('industry') && !in_array(request('industry'), $industries))
                        <option value="{{ request('industry') }}" selected>{{ request('industry') }}</option>
                    @endif
                </select>
            </div>
            @php($hasNotes = request('has_notes'))
            <div>
                <span class="form-label d-block">Have Notes?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-true" value="true" {{ $hasNotes === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-false" value="false" {{ $hasNotes === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-any" value="any" {{ !in_array($hasNotes, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-any">Any</label>
                </div>
            </div>
            @php($hasPhoto = request('has_photo'))
            <div>
                <span class="form-label d-block">Have Photo?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-true" value="true" {{ $hasPhoto === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-false" value="false" {{ $hasPhoto === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-any" value="any" {{ !in_array($hasPhoto, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-any">Any</label>
                </div>
            </div>
            @php($contactPrimary = request('contact_primary'))
            <div>
                <span class="form-label d-block">Contact Primary?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="contact_primary" id="filter-contact-primary-true" value="true" {{ $contactPrimary === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-contact-primary-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="contact_primary" id="filter-contact-primary-false" value="false" {{ $contactPrimary === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-contact-primary-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="contact_primary" id="filter-contact-primary-any" value="any" {{ !in_array($contactPrimary, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-contact-primary-any">Any</label>
                </div>
            </div>
            <div>
                <label class="form-label" for="filter-period-year">Period Year</label>
                <input type="number" class="form-control" id="filter-period-year" name="period_year" value="{{ request('period_year') }}">
            </div>
            <div>
                <label class="form-label" for="filter-period-term">Period Term</label>
                <input type="number" class="form-control" id="filter-period-term" name="period_term" value="{{ request('period_term') }}">
            </div>
            <div>
                <label class="form-label" for="filter-quota">Quota</label>
                <input type="number" class="form-control" id="filter-quota" name="quota" value="{{ request('quota') }}">
            </div>
            <div>
                <label class="form-label" for="filter-used">Quota Used</label>
                <input type="number" class="form-control" id="filter-used" name="used" value="{{ request('used') }}">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="institution-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const institutionSearchForm = document.getElementById('institution-search-form');
const institutionSearchInput = document.getElementById('institution-search-input');
let institutionSearchTimer;

function submitInstitutionSearch() {
    const formData = new FormData(institutionSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = institutionSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

institutionSearchInput.addEventListener('input', () => {
    clearTimeout(institutionSearchTimer);
    institutionSearchTimer = setTimeout(submitInstitutionSearch, 300);
});

institutionSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitInstitutionSearch();
});

const institutionFilterForm = document.getElementById('institution-filter-form');
const institutionFilterReset = document.getElementById('institution-filter-reset');

institutionFilterForm.addEventListener('submit', () => {
    ['has_notes', 'has_photo', 'contact_primary'].forEach(name => {
        const radio = institutionFilterForm.querySelector(`input[name="${name}"]:checked`);
        if (radio && radio.value === 'any') {
            radio.disabled = true;
        }
    });
});

institutionFilterReset.addEventListener('click', () => {
    window.location = institutionFilterReset.dataset.resetUrl;
});
</script>
@endsection
