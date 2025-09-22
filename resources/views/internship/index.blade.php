@extends('layouts.app')

@php use Illuminate\Support\Arr; @endphp

@section('title', 'Internships')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Internships</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="internship-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
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
            <a href="/internship/add" class="btn btn-primary">Create Internship</a>
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
                <th scope="col">Start Date</th>
                <th scope="col">End Date</th>
                <th scope="col" class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($internships as $internship)
            <tr>
                <td>{{ $internships->total() - ($internships->currentPage() - 1) * $internships->perPage() - $loop->index }}</td>
                <td>{{ $internship->student_name }}</td>
                <td>{{ $internship->institution_name }}</td>
                <td>{{ $internship->start_date }}</td>
                <td>{{ $internship->end_date }}</td>
                <td class="text-nowrap">
                    <a href="/internship/{{ $internship->id }}/see" class="btn btn-sm btn-secondary">Read</a>
                    @if($isStudent)
                        <button class="btn btn-sm btn-warning" disabled>Update</button>
                        <button class="btn btn-sm btn-danger" disabled>Delete</button>
                    @else
                        <a href="/internship/{{ $internship->id }}/edit" class="btn btn-sm btn-warning">Update</a>
                        <form action="/internship/{{ $internship->id }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this internship?');">Delete</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">No internships found.</td>
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

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="internshipFilter" aria-labelledby="internshipFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="internshipFilterLabel">Filter Internships</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="internship-filter-form" class="d-flex flex-column gap-3">
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
                @foreach(['planned','ongoing','completed','terminated'] as $s)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="{{ $s }}" id="status-{{ $s }}" @checked(in_array($s, $statusValues))>
                        <label class="form-check-label" for="status-{{ $s }}">{{ ucwords($s) }}</label>
                    </div>
                @endforeach
                <input type="hidden" name="status" id="status-hidden">
            </div>
            @php($startRange = request('start_date'))
            @php($startStart = $startEnd = '')
            @if($startRange && str_starts_with($startRange, 'range:'))
                @php([$startStart, $startEnd] = array_pad(explode(',', substr($startRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">Start Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="start_date_start" value="{{ $startStart }}">
                    <input type="date" class="form-control" id="start_date_end" value="{{ $startEnd }}">
                </div>
                <input type="hidden" name="start_date" id="start_date_range">
            </div>
            @php($endRange = request('end_date'))
            @php($endStart = $endEnd = '')
            @if($endRange && str_starts_with($endRange, 'range:'))
                @php([$endStart, $endEnd] = array_pad(explode(',', substr($endRange, 6)), 2, ''))
            @endif
            <div>
                <label class="form-label">End Date</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="end_date_start" value="{{ $endStart }}">
                    <input type="date" class="form-control" id="end_date_end" value="{{ $endEnd }}">
                </div>
                <input type="hidden" name="end_date" id="end_date_range">
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
                <button type="button" class="btn btn-secondary flex-fill" id="internship-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const internshipSearchForm = document.getElementById('internship-search-form');
const internshipSearchInput = document.getElementById('internship-search-input');
let internshipSearchTimer;

function submitInternshipSearch() {
    const formData = new FormData(internshipSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = internshipSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

internshipSearchInput.addEventListener('input', () => {
    clearTimeout(internshipSearchTimer);
    internshipSearchTimer = setTimeout(submitInternshipSearch, 300);
});

internshipSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitInternshipSearch();
});

const internshipFilterForm = document.getElementById('internship-filter-form');
const internshipFilterReset = document.getElementById('internship-filter-reset');

internshipFilterForm.addEventListener('submit', () => {
    const statusChecked = Array.from(document.querySelectorAll('input[id^="status-"]:checked")).map(cb => cb.value);
    const statusHidden = document.getElementById('status-hidden');
    if (statusChecked.length) {
        statusHidden.value = 'in:' + statusChecked.join(',');
    } else {
        statusHidden.disabled = true;
    }

    const startStart = document.getElementById('start_date_start').value;
    const startEnd = document.getElementById('start_date_end').value;
    const startHidden = document.getElementById('start_date_range');
    if (startStart || startEnd) {
        startHidden.value = 'range:' + startStart + ',' + startEnd;
    } else {
        startHidden.disabled = true;
    }

    const endStart = document.getElementById('end_date_start').value;
    const endEnd = document.getElementById('end_date_end').value;
    const endHidden = document.getElementById('end_date_range');
    if (endStart || endEnd) {
        endHidden.value = 'range:' + endStart + ',' + endEnd;
    } else {
        endHidden.disabled = true;
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

internshipFilterReset.addEventListener('click', () => {
    window.location = internshipFilterReset.dataset.resetUrl;
});
</script>
@endsection
