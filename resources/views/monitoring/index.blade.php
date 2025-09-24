@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Internships')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="mb-0">Internships</h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="get" action="{{ url()->current() }}" id="monitoring-search-form" class="position-relative">
            <div class="input-group" style="min-width: 280px;">
                <input type="search" name="q" id="monitoring-search-input" class="form-control" placeholder="Search monitorings" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="monitoring-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#monitoringFilter" aria-controls="monitoringFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Monitoring</button>
        @else
            <a href="/monitorings/create" class="btn btn-primary">Create Monitoring</a>
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
            <tr class="text-nowrap">
                <th scope="col">Title</th>
                <th scope="col">Log Date</th>
                <th scope="col">Type</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $log->title ?? '—' }}</td>
                <td>{{ $log->log_date }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $log->log_type)) }}</td>
                <td class="text-nowrap">
                    <a href="/monitorings/{{ $log->monitoring_log_id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    @if($isStudent)
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @else
                        <a href="/monitorings/{{ $log->monitoring_log_id }}/update" class="btn btn-sm btn-warning">Update</a>
                        <form action="/monitorings/{{ $log->monitoring_log_id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this monitoring?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No monitoring logs found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Monitorings: {{ $logs->total() }}</p>
<p class="text-muted">Page {{ $logs->currentPage() }} out of {{ $logs->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($logs->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $logs->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($logs->hasMorePages())
        <a href="{{ $logs->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetUrl = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="monitoringFilter" aria-labelledby="monitoringFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="monitoringFilterLabel">Filter Monitorings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="monitoring-filter-form" class="d-flex flex-column gap-3">
            <div>
                <label class="form-label" for="filter-title">Title</label>
                <input type="text" name="title" id="filter-title" class="form-control" value="{{ $selections['title'] }}">
            </div>
            <div>
                <label class="form-label" for="filter-student">Student Name</label>
                <select name="student_id" id="filter-student" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select student</option>
                    @foreach($students as $student)
                        <option value="{{ $student['id'] }}" @selected((string)($selections['student_id'] ?? '') === (string)$student['id'])>{{ $student['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="filter-institution">Institution Name</label>
                <select name="institution_id" id="filter-institution" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select institution</option>
                    @foreach($institutions as $institution)
                        <option value="{{ $institution['id'] }}" @selected((string)($selections['institution_id'] ?? '') === (string)$institution['id'])>{{ $institution['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Log Date</label>
                <div class="d-flex gap-2">
                    <input type="date" name="log_date_from" class="form-control" value="{{ $selections['log_date_from'] }}">
                    <input type="date" name="log_date_to" class="form-control" value="{{ $selections['log_date_to'] }}">
                </div>
            </div>
            <div>
                <span class="form-label d-block">Have Content?</span>
                @php($hasContent = $selections['has_content'] ?? 'any')
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_content" id="has-content-true" value="true" @checked($hasContent === 'true')>
                    <label class="form-check-label" for="has-content-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_content" id="has-content-false" value="false" @checked($hasContent === 'false')>
                    <label class="form-check-label" for="has-content-false">False</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_content" id="has-content-any" value="any" @checked(!in_array($hasContent, ['true','false'], true))>
                    <label class="form-check-label" for="has-content-any">Any</label>
                </div>
            </div>
            <div>
                <label class="form-label" for="filter-type">Type</label>
                <select name="type" id="filter-type" class="form-select">
                    <option value="">Select type</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" @selected(($selections['type'] ?? '') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="monitoring-filter-reset" data-reset-url="{{ $resetUrl }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const searchForm = document.getElementById('monitoring-search-form');
    const searchInput = document.getElementById('monitoring-search-input');
    let searchTimer;

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
        clearTimeout(searchTimer);
        searchTimer = setTimeout(submitSearch, 300);
    });

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitSearch();
    });

    const filterReset = document.getElementById('monitoring-filter-reset');
    filterReset.addEventListener('click', () => {
        window.location = filterReset.dataset.resetUrl;
    });
})();
</script>
@endpush
