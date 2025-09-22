@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Applications')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Applications</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="application-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
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
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Application</button>
        @else
            <a href="/application/add" class="btn btn-primary">Create Application</a>
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
                <th scope="col">No</th>
                <th scope="col">Student Name</th>
                <th scope="col">Institution Name</th>
                <th scope="col">Year</th>
                <th scope="col">Term</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($applications as $application)
            <tr>
                <td>{{ $applications->total() - ($applications->currentPage() - 1) * $applications->perPage() - $loop->index }}</td>
                <td>{{ $application->student_name }}</td>
                <td>{{ $application->institution_name }}</td>
                <td>{{ $application->period_year }}</td>
                <td>{{ $application->period_term }}</td>
                <td class="text-nowrap">
                    <a href="/application/{{ $application->id }}/see" class="btn btn-sm btn-secondary">Read</a>
                    @if($isStudent)
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @else
                        <a href="/application/{{ $application->id }}/edit" class="btn btn-sm btn-warning">Update</a>
                        <form action="/application/{{ $application->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this application?');">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No applications found.</td>
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

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="applicationFilter" aria-labelledby="applicationFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="applicationFilterLabel">Filter Applications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="application-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @php($statusValues = [])
            @if(request()->has('status') && str_starts_with(request('status'), 'in:'))
                @php($statusValues = explode(',', substr(request('status'), 3)))
            @endif
            <div>
                <label class="form-label">Status</label>
                @foreach(['submitted','under_review','accepted','rejected','cancelled'] as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $s }}" id="status-{{ $s }}" @checked(in_array($s, $statusValues))>
                        <label class="form-check-label" for="status-{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</label>
                    </div>
                @endforeach
                <input type="hidden" name="status" id="status-hidden">
            </div>
            @php($submittedRange = request('submitted_at'))
            @php($submittedStart = $submittedEnd = '')
            @if($submittedRange && str_starts_with($submittedRange, 'range:'))
                @php([$submittedStart, $submittedEnd] = array_pad(explode(',', substr($submittedRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">Submitted At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="submitted_at_start" value="{{ $submittedStart }}">
                    <input type="date" class="form-control" id="submitted_at_end" value="{{ $submittedEnd }}">
                </div>
                <input type="hidden" name="submitted_at" id="submitted_at_range">
            </div>
            @php($createdRange = request('created_at'))
            @php($createdStart = $createdEnd = '')
            @if($createdRange && str_starts_with($createdRange, 'range:'))
                @php([$createdStart, $createdEnd] = array_pad(explode(',', substr($createdRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">Created At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="created_at_start" value="{{ $createdStart }}">
                    <input type="date" class="form-control" id="created_at_end" value="{{ $createdEnd }}">
                </div>
                <input type="hidden" name="created_at" id="created_at_range">
            </div>
            @php($updatedRange = request('updated_at'))
            @php($updatedStart = $updatedEnd = '')
            @if($updatedRange && str_starts_with($updatedRange, 'range:'))
                @php([$updatedStart, $updatedEnd] = array_pad(explode(',', substr($updatedRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">Updated At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="updated_at_start" value="{{ $updatedStart }}">
                    <input type="date" class="form-control" id="updated_at_end" value="{{ $updatedEnd }}">
                </div>
                <input type="hidden" name="updated_at" id="updated_at_range">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="application-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const applicationSearchForm = document.getElementById('application-search-form');
const applicationSearchInput = document.getElementById('application-search-input');
let applicationSearchTimer;

function submitApplicationSearch() {
    const formData = new FormData(applicationSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = applicationSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

applicationSearchInput.addEventListener('input', () => {
    clearTimeout(applicationSearchTimer);
    applicationSearchTimer = setTimeout(submitApplicationSearch, 300);
});

applicationSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitApplicationSearch();
});

const applicationFilterForm = document.getElementById('application-filter-form');
const applicationFilterReset = document.getElementById('application-filter-reset');

applicationFilterForm.addEventListener('submit', () => {
    const statusChecked = Array.from(document.querySelectorAll('input[id^="status-"]:checked")).map(cb => cb.value);
    const statusHidden = document.getElementById('status-hidden');
    if (statusChecked.length) {
        statusHidden.value = 'in:' + statusChecked.join(',');
    } else {
        statusHidden.disabled = true;
    }

    const submittedStart = document.getElementById('submitted_at_start').value;
    const submittedEnd = document.getElementById('submitted_at_end').value;
    const submittedHidden = document.getElementById('submitted_at_range');
    if (submittedStart || submittedEnd) {
        submittedHidden.value = 'range:' + submittedStart + ',' + submittedEnd;
    } else {
        submittedHidden.disabled = true;
    }

    const createdStart = document.getElementById('created_at_start').value;
    const createdEnd = document.getElementById('created_at_end').value;
    const createdHidden = document.getElementById('created_at_range');
    if (createdStart || createdEnd) {
        createdHidden.value = 'range:' + createdStart + ',' + createdEnd;
    } else {
        createdHidden.disabled = true;
    }

    const updatedStart = document.getElementById('updated_at_start').value;
    const updatedEnd = document.getElementById('updated_at_end').value;
    const updatedHidden = document.getElementById('updated_at_range');
    if (updatedStart || updatedEnd) {
        updatedHidden.value = 'range:' + updatedStart + ',' + updatedEnd;
    } else {
        updatedHidden.disabled = true;
    }
});

applicationFilterReset.addEventListener('click', () => {
    window.location = applicationFilterReset.dataset.resetUrl;
});
</script>
@endsection
