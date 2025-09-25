@extends('layouts.app')

@section('title', 'Supervisors')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
@php($isSupervisor = $role === 'supervisor')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Supervisors</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="supervisor-search-form" class="search-form">
            <div class="input-group">
                <input type="search" name="q" id="supervisor-search-input" class="form-control" placeholder="Search supervisors" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-primary" type="submit" id="supervisor-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#supervisorFilter" aria-controls="supervisorFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if(in_array($role, ['admin', 'developer'], true))
            <a href="/supervisors/create" class="btn btn-primary">Create Supervisor</a>
        @endif
    </div>
</div>

@if(count($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $filter)
            @php($query = request()->except([$filter['param'], 'page']))
            @php($queryString = http_build_query(array_filter($query, fn($value) => $value !== null && $value !== '')))
            <a href="{{ url()->current() . ($queryString ? '?' . $queryString : '') }}" class="btn btn-sm btn-outline-primary">
                {{ $filter['label'] }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Department</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supervisors as $supervisor)
                <tr>
                    <td>{{ $supervisor->name }}</td>
                    <td>{{ $supervisor->email }}</td>
                    <td>{{ $supervisor->phone ?? '—' }}</td>
                    <td>{{ $supervisor->department ?? '—' }}</td>
                    <td class="text-nowrap">
                        <a href="/supervisors/{{ $supervisor->id }}/read" class="btn btn-sm btn-outline-secondary">Read</a>
                        @if($isSupervisor && $supervisor->id !== $currentSupervisorId)
                            <button class="btn btn-sm btn-warning" disabled>Update</button>
                            <button class="btn btn-sm btn-danger" disabled>Delete</button>
                        @elseif($isStudent)
                            <button class="btn btn-sm btn-warning" disabled>Update</button>
                            <button class="btn btn-sm btn-danger" disabled>Delete</button>
                        @else
                            <a href="/supervisors/{{ $supervisor->id }}/update" class="btn btn-sm btn-warning">Update</a>
                            <form action="/supervisors/{{ $supervisor->id }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this supervisor?')">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No supervisors found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Supervisors: {{ $supervisors->total() }}</p>
<p class="text-muted">Page {{ $supervisors->currentPage() }} out of {{ $supervisors->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($supervisors->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $supervisors->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($supervisors->hasMorePages())
        <a href="{{ $supervisors->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="supervisorFilter" aria-labelledby="supervisorFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="supervisorFilterLabel">Filter Supervisors</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="supervisor-filter-form">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div class="mb-3">
                <label class="form-label" for="filter-name">Name</label>
                <input type="text" class="form-control" name="name" id="filter-name" value="{{ request('name') }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="filter-email">Email</label>
                <input type="text" class="form-control" name="email" id="filter-email" value="{{ request('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="filter-phone">Phone</label>
                <input type="text" class="form-control" name="phone" id="filter-phone" value="{{ request('phone') }}">
            </div>
            <div class="mb-3">
                <label class="form-label" for="filter-department">Department</label>
                <input type="text" class="form-control" name="department" id="filter-department" value="{{ request('department') }}">
            </div>
            <div class="mb-3">
                <span class="form-label d-block">Is Email Verified?</span>
                @php($emailVerified = request('email_verified'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="email-verified-true" value="true" {{ $emailVerified === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="email-verified-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="email-verified-false" value="false" {{ $emailVerified === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="email-verified-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="email-verified-any" value="any" {{ !in_array($emailVerified, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="email-verified-any">Any</label>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="filter-email-verified-at">Email Verified At</label>
                <input type="date" class="form-control" name="email_verified_at" id="filter-email-verified-at" value="{{ request('email_verified_at') }}">
            </div>
            <div class="mb-3">
                <span class="form-label d-block">Have notes?</span>
                @php($hasNotes = request('has_notes'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-true" value="true" {{ $hasNotes === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-notes-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-false" value="false" {{ $hasNotes === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-notes-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-any" value="any" {{ !in_array($hasNotes, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-notes-any">Any</label>
                </div>
            </div>
            <div class="mb-4">
                <span class="form-label d-block">Have photo?</span>
                @php($hasPhoto = request('has_photo'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="has-photo-true" value="true" {{ $hasPhoto === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-photo-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="has-photo-false" value="false" {{ $hasPhoto === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-photo-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="has-photo-any" value="any" {{ !in_array($hasPhoto, ['true','false'], true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="has-photo-any">Any</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary flex-fill" id="supervisor-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const supervisorSearchForm = document.getElementById('supervisor-search-form');

function submitSupervisorSearch() {
    const formData = new FormData(supervisorSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = supervisorSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

supervisorSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitSupervisorSearch();
});

const filterForm = document.getElementById('supervisor-filter-form');
const resetButton = document.getElementById('supervisor-filter-reset');

filterForm.addEventListener('submit', () => {
    const optionalRadios = ['email_verified', 'has_notes', 'has_photo'];
    optionalRadios.forEach(name => {
        const checked = filterForm.querySelector(`input[name="${name}"]:checked`);
        if (checked && checked.value === 'any') {
            checked.disabled = true;
        }
    });
    const dateInput = document.getElementById('filter-email-verified-at');
    if (dateInput && !dateInput.value) {
        dateInput.disabled = true;
    }
});

resetButton.addEventListener('click', () => {
    window.location = resetButton.dataset.resetUrl;
});
</script>
@endsection
