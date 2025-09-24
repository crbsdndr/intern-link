@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Internships')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="mb-0">Internships</h1>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="get" action="{{ url()->current() }}" id="internship-search-form" class="position-relative">
            <div class="input-group" style="min-width: 280px;">
                <input type="search" name="q" id="internship-search-input" class="form-control" placeholder="Search internships" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="internship-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#internshipFilter" aria-controls="internshipFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Internship</button>
        @else
            <a href="/internships/create" class="btn btn-primary">Create Internship</a>
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
                <th scope="col">Student Name</th>
                <th scope="col">Institution Name</th>
                <th scope="col">Period Year</th>
                <th scope="col">Period Term</th>
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($internships as $internship)
            <tr>
                <td>{{ $internship->student_name }}</td>
                <td>{{ $internship->institution_name }}</td>
                <td>{{ $internship->period_year }}</td>
                <td>{{ $internship->period_term }}</td>
                <td>{{ $internship->start_date }}</td>
                <td>{{ $internship->end_date }}</td>
                <td>{{ ucwords(str_replace('_', ' ', $internship->status)) }}</td>
                <td class="text-nowrap">
                    <a href="/internships/{{ $internship->id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    @if($isStudent)
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @else
                        <a href="/internships/{{ $internship->id }}/update" class="btn btn-sm btn-warning">Update</a>
                        <form action="/internships/{{ $internship->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this internship?');">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">No internships found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Internships: {{ $internships->total() }}</p>
<p class="text-muted">Page {{ $internships->currentPage() }} out of {{ $internships->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($internships->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $internships->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($internships->hasMorePages())
        <a href="{{ $internships->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetUrl = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="internshipFilter" aria-labelledby="internshipFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="internshipFilterLabel">Filter Internships</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="internship-filter-form" class="d-flex flex-column gap-3">
            <div>
                <label class="form-label" for="filter-student">Student Name</label>
                <select name="student_id" id="filter-student" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select student</option>
                    @foreach($students as $student)
                        <option value="{{ $student['id'] }}" @selected((string)$selections['student_id'] === (string)$student['id'])>{{ $student['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="filter-institution">Institution Name</label>
                <select name="institution_id" id="filter-institution" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select institution</option>
                    @foreach($institutions as $institution)
                        <option value="{{ $institution['id'] }}" @selected((string)$selections['institution_id'] === (string)$institution['id'])>{{ $institution['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="filter-period">Period</label>
                <select name="period_id" id="filter-period" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select period</option>
                    @foreach($periods as $period)
                        <option value="{{ $period['id'] }}" @selected((string)$selections['period_id'] === (string)$period['id'])>{{ $period['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Start Date</label>
                <div class="d-flex gap-2">
                    <input type="date" name="start_date_from" class="form-control" value="{{ $selections['start_date_from'] }}">
                    <input type="date" name="start_date_to" class="form-control" value="{{ $selections['start_date_to'] }}">
                </div>
            </div>
            <div>
                <label class="form-label">End Date</label>
                <div class="d-flex gap-2">
                    <input type="date" name="end_date_from" class="form-control" value="{{ $selections['end_date_from'] }}">
                    <input type="date" name="end_date_to" class="form-control" value="{{ $selections['end_date_to'] }}">
                </div>
            </div>
            <div>
                <label class="form-label" for="filter-status">Status</label>
                <select name="status" id="filter-status" class="form-select tom-select" data-tom-allow-empty="true">
                    <option value="">Select status</option>
                    @foreach($statuses as $status)
                        @php($statusLabel = ucwords(str_replace('_', ' ', $status)))
                        <option value="{{ $status }}" @selected($selections['status'] === $status)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="internship-filter-reset" data-reset-url="{{ $resetUrl }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const searchForm = document.getElementById('internship-search-form');

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

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        submitSearch();
    });

    const filterForm = document.getElementById('internship-filter-form');
    const resetButton = document.getElementById('internship-filter-reset');

    filterForm.addEventListener('submit', () => {
        filterForm.querySelectorAll('select, input').forEach((el) => {
            if (!el.name) {
                return;
            }
            if ((el.tagName === 'SELECT' || el.type === 'date' || el.type === 'text') && !el.value) {
                el.disabled = true;
            }
        });
    });

    resetButton.addEventListener('click', () => {
        window.location = resetButton.dataset.resetUrl;
    });
})();
</script>
@endpush
