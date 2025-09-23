@extends('layouts.app')

@section('title', 'Applications')

@section('content')
@php($role = session('role'))
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="mb-0">Applications</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="application-search-form" class="position-relative">
            <div class="input-group" style="min-width: 280px;">
                <input type="search" name="q" id="application-search-input" class="form-control" placeholder="Search applications" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="application-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#applicationFilter" aria-controls="applicationFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/applications/create" class="btn btn-primary">Create Application</a>
    </div>
</div>

@if(count($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $param => $label)
            @php($query = collect(request()->query())->except([$param, 'page'])->filter(fn($value) => $value !== null && $value !== '')->toArray())
            @php($queryString = http_build_query($query))
            <a href="{{ url()->current() . ($queryString ? '?' . $queryString : '') }}" class="btn btn-sm btn-outline-secondary">
                {{ $label }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-3">
        <thead>
            <tr class="text-nowrap">
                <th scope="col">No</th>
                <th scope="col">Student Name</th>
                <th scope="col">Institution Name</th>
                <th scope="col">Year</th>
                <th scope="col">Term</th>
                <th scope="col">Status Application</th>
                <th scope="col">Student Access</th>
                <th scope="col">Submitted At</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($applications as $application)
            <tr>
                <td>{{ ($applications->currentPage() - 1) * $applications->perPage() + $loop->iteration }}</td>
                <td>{{ $application->student_name }}</td>
                <td>{{ $application->institution_name }}</td>
                <td>{{ $application->period_year }}</td>
                <td>{{ $application->period_term }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $application->status)) }}</td>
                <td>{{ $application->student_access ? 'True' : 'False' }}</td>
                <td>{{ \Illuminate\Support\Carbon::parse($application->submitted_at)->format('Y-m-d') }}</td>
                <td class="text-nowrap">
                    <a href="/applications/{{ $application->id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    <a href="/applications/{{ $application->id }}/update" class="btn btn-sm btn-warning">Update</a>
                    <form action="/applications/{{ $application->id }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this application?');">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No applications found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Applications: {{ $applications->total() }}</p>
<p class="text-muted">Page {{ $applications->currentPage() }} out of {{ $applications->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($applications->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $applications->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($applications->hasMorePages())
        <a href="{{ $applications->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="applicationFilter" aria-labelledby="applicationFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="applicationFilterLabel">Filter Applications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="application-filter-form" class="d-flex flex-column gap-3">
            <div>
                <label for="filter-student-name" class="form-label">Student Name</label>
                <input type="text" name="student_name" id="filter-student-name" class="form-control" value="{{ request('student_name') }}">
            </div>
            <div>
                <label for="filter-institution-name" class="form-label">Institution Name</label>
                <input type="text" name="institution_name" id="filter-institution-name" class="form-control" value="{{ request('institution_name') }}">
            </div>
            <div>
                <label for="filter-period" class="form-label">Period</label>
                <select name="period_id" id="filter-period" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select period</option>
                    @foreach($periods as $period)
                        <option value="{{ $period['id'] }}" @selected((string)request('period_id') === (string)$period['id'])>{{ $period['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="filter-status" class="form-label">Status Application</label>
                <select name="status" id="filter-status" class="form-select">
                    <option value="">Any</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label d-block">Student Access</label>
                @php($studentAccess = request('student_access', 'any'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-any" value="any" @checked($studentAccess === 'any')>
                    <label class="form-check-label" for="student-access-any">Any</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-true" value="true" @checked($studentAccess === 'true')>
                    <label class="form-check-label" for="student-access-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="student_access" id="student-access-false" value="false" @checked($studentAccess === 'false')>
                    <label class="form-check-label" for="student-access-false">False</label>
                </div>
            </div>
            <div>
                <label for="filter-submitted" class="form-label">Submitted At</label>
                <input type="date" name="submitted_at" id="filter-submitted" class="form-control" value="{{ request('submitted_at') }}">
            </div>
            <div>
                <label class="form-label d-block">Have Notes?</label>
                @php($hasNotes = request('has_notes', 'any'))
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-any" value="any" @checked($hasNotes === 'any')>
                    <label class="form-check-label" for="has-notes-any">Any</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-true" value="true" @checked($hasNotes === 'true')>
                    <label class="form-check-label" for="has-notes-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="has-notes-false" value="false" @checked($hasNotes === 'false')>
                    <label class="form-check-label" for="has-notes-false">False</label>
                </div>
            </div>
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="application-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const searchForm = document.getElementById('application-search-form');
    const searchInput = document.getElementById('application-search-input');
    let timer;

    function submitSearch() {
        const formData = new FormData(searchForm);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (key === 'q' && value.trim() === '') {
                continue;
            }
            params.append(key, value);
        }
        params.delete('page');
        const query = params.toString();
        window.location = searchForm.getAttribute('action') + (query ? '?' + query : '');
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(submitSearch, 300);
    });

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitSearch();
    });

    const filterForm = document.getElementById('application-filter-form');
    const resetButton = document.getElementById('application-filter-reset');

    filterForm.addEventListener('submit', () => {
        const studentAccessAny = document.getElementById('student-access-any');
        if (studentAccessAny.checked) {
            studentAccessAny.value = '';
        } else {
            studentAccessAny.value = 'any';
        }

        const hasNotesAny = document.getElementById('has-notes-any');
        if (hasNotesAny.checked) {
            hasNotesAny.value = '';
        } else {
            hasNotesAny.value = 'any';
        }
    });

    resetButton.addEventListener('click', () => {
        window.location = '{{ url('/applications') }}';
    });
})();
</script>
@endpush
@endsection
