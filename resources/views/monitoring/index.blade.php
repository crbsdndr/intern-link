@extends('layouts.app')

@php use Illuminate\Support\Str; use Illuminate\Support\Arr; @endphp

@section('title', 'Monitoring Logs')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Monitoring Logs</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="monitoring-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
                <input type="search" name="q" id="monitoring-search-input" class="form-control" placeholder="Search monitoring logs" value="{{ request('q') }}" autocomplete="off">
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
            <button class="btn btn-primary" disabled>Create Monitoring Log</button>
        @else
            <a href="/monitoring/add" class="btn btn-primary">Create Monitoring Log</a>
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
                <th scope="col">Date</th>
                <th scope="col">Student</th>
                <th scope="col">Institution</th>
                <th scope="col">Supervisor</th>
                <th scope="col">Type</th>
                <th scope="col">Title</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>{{ $logs->total() - ($logs->currentPage() - 1) * $logs->perPage() - $loop->index }}</td>
                <td>{{ $log->log_date }}</td>
                <td>{{ $log->student_name }}</td>
                <td>{{ $log->institution_name }}</td>
                <td>{{ $log->supervisor_name ?? '—' }}</td>
                <td>{{ $log->log_type }}</td>
                <td>{{ $log->title ?? Str::limit($log->content, 20) }}</td>
                <td class="text-nowrap">
                    <a href="/monitoring/{{ $log->id }}/see" class="btn btn-sm btn-secondary">Read</a>
                    @if($isStudent)
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @else
                        <a href="/monitoring/{{ $log->id }}/edit" class="btn btn-sm btn-warning">Update</a>
                        <form action="/monitoring/{{ $log->id }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this log?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No monitoring logs found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Monitoring Logs: {{ $logs->total() }}</p>
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

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="monitoringFilter" aria-labelledby="monitoringFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="monitoringFilterLabel">Filter Monitoring Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="monitoring-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}">
            @endif
            @php($logRange = request('log_date'))
            @php($logStart = $logEnd = '')
            @if($logRange && str_starts_with($logRange, 'range:'))
                @php([$logStart, $logEnd] = array_pad(explode(',', substr($logRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">Log Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="log_date_start" value="{{ $logStart }}">
                    <input type="date" class="form-control" id="log_date_end" value="{{ $logEnd }}">
                </div>
                <input type="hidden" name="log_date" id="log_date_range">
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
                <button type="button" class="btn btn-secondary flex-fill" id="monitoring-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const monitoringSearchForm = document.getElementById('monitoring-search-form');
const monitoringSearchInput = document.getElementById('monitoring-search-input');
let monitoringSearchTimer;

function submitMonitoringSearch() {
    const formData = new FormData(monitoringSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = monitoringSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

monitoringSearchInput.addEventListener('input', () => {
    clearTimeout(monitoringSearchTimer);
    monitoringSearchTimer = setTimeout(submitMonitoringSearch, 300);
});

monitoringSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitMonitoringSearch();
});

const monitoringFilterForm = document.getElementById('monitoring-filter-form');
const monitoringFilterReset = document.getElementById('monitoring-filter-reset');

monitoringFilterForm.addEventListener('submit', () => {
    const logStart = document.getElementById('log_date_start').value;
    const logEnd = document.getElementById('log_date_end').value;
    const logHidden = document.getElementById('log_date_range');
    if (logStart || logEnd) {
        logHidden.value = 'range:' + logStart + ',' + logEnd;
    } else {
        logHidden.disabled = true;
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

monitoringFilterReset.addEventListener('click', () => {
    window.location = monitoringFilterReset.dataset.resetUrl;
});
</script>
@endsection
