@extends('layouts.app')

@section('title', 'Students')

@section('content')
@php($role = session('role'))
@php($isStudent = $role === 'student')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Students</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="student-search-form" class="position-relative">
            <div class="input-group" style="min-width: 260px;">
                <input type="search" name="q" id="student-search-input" class="form-control" placeholder="Search students" value="{{ request('q') }}" autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit" id="student-search-submit">Search</button>
            </div>
            @foreach(request()->except(['q','page']) as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#studentFilter" aria-controls="studentFilter">
            Filter
            @if(count($filters))
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Student</button>
        @else
            <a href="/students/create" class="btn btn-primary">Create Student</a>
        @endif
    </div>
</div>

@if(count($filters))
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach($filters as $param => $label)
            @php($query = request()->except([$param, 'page']))
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
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Student Number</th>
                <th scope="col">National SN</th>
                <th scope="col">Major</th>
                <th scope="col">Class</th>
                <th scope="col">Batch</th>
                <th scope="col" class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($students as $student)
            <tr>
                <td>{{ $students->firstItem() + $loop->index }}</td>
                <td>{{ $student->name }}</td>
                <td>{{ $student->email }}</td>
                <td>{{ $student->phone ?? '—' }}</td>
                <td>{{ $student->student_number }}</td>
                <td>{{ $student->national_sn }}</td>
                <td>{{ $student->major }}</td>
                <td>{{ $student->class }}</td>
                <td>{{ $student->batch }}</td>
                <td class="text-nowrap text-center">
                    <a href="/students/{{ $student->id }}/read" class="btn btn-sm btn-secondary">Read</a>
                    <a href="/students/{{ $student->id }}/update" class="btn btn-sm btn-warning">Update</a>
                    <form action="/students/{{ $student->id }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">No students found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted mb-1">Total Students: {{ $students->total() }}</p>
<p class="text-muted">Page {{ $students->currentPage() }} out of {{ $students->lastPage() }}</p>

<div class="d-flex justify-content-between align-items-center mb-4">
    @if ($students->onFirstPage())
        <span class="btn btn-outline-secondary disabled">Back</span>
    @else
        <a href="{{ $students->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
    @endif

    @if ($students->hasMorePages())
        <a href="{{ $students->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
    @else
        <span class="btn btn-outline-secondary disabled">Next</span>
    @endif
</div>

@php($resetBase = request('q') ? url()->current() . '?q=' . urlencode(request('q')) : url()->current())
<div class="offcanvas offcanvas-end" tabindex="-1" id="studentFilter" aria-labelledby="studentFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="studentFilterLabel">Filter Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="student-filter-form" class="d-flex flex-column gap-3">
            @if(request('q'))
                <input type="hidden" name="q" value="{{ request('q') }}">
            @endif
            <div>
                <label class="form-label" for="filter-name">Name</label>
                <input type="text" class="form-control" id="filter-name" name="name" value="{{ request('name') }}">
            </div>
            <div>
                <label class="form-label" for="filter-email">Email</label>
                <input type="email" class="form-control" id="filter-email" name="email" value="{{ request('email') }}">
            </div>
            <div>
                <label class="form-label" for="filter-phone">Phone</label>
                <input type="text" class="form-control" id="filter-phone" name="phone" value="{{ request('phone') }}">
            </div>
            @php($emailVerified = request('email_verified'))
            <div>
                <span class="form-label d-block">Is Email Verified?</span>
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
            <div>
                <label class="form-label" for="filter-email-verified-at">Email Verified At</label>
                <input type="date" class="form-control" id="filter-email-verified-at" name="email_verified_at" value="{{ request('email_verified_at') }}">
            </div>
            <div>
                <label class="form-label" for="filter-student-number">Student Number</label>
                <input type="text" class="form-control" id="filter-student-number" name="student_number" value="{{ request('student_number') }}">
            </div>
            <div>
                <label class="form-label" for="filter-national-sn">National Student Number</label>
                <input type="text" class="form-control" id="filter-national-sn" name="national_sn" value="{{ request('national_sn') }}">
            </div>
            <div>
                <label class="form-label" for="filter-major">Major</label>
                <input type="text" class="form-control" id="filter-major" name="major" value="{{ request('major') }}">
            </div>
            <div>
                <label class="form-label" for="filter-class">Class</label>
                <input type="text" class="form-control" id="filter-class" name="class" value="{{ request('class') }}">
            </div>
            <div>
                <label class="form-label" for="filter-batch">Batch</label>
                <input type="number" class="form-control" id="filter-batch" name="batch" value="{{ request('batch') }}" min="1900" max="2100" step="1">
            </div>
            @php($hasNotes = request('has_notes'))
            <div>
                <span class="form-label d-block">Have notes?</span>
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
            @php($hasPhoto = request('has_photo'))
            <div>
                <span class="form-label d-block">Have photo?</span>
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
                <button type="button" class="btn btn-secondary flex-fill" id="student-filter-reset" data-reset-url="{{ $resetBase }}">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
const studentSearchForm = document.getElementById('student-search-form');

function submitStudentSearch() {
    const formData = new FormData(studentSearchForm);
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        if (key === 'q' && value.trim() === '') {
            continue;
        }
        params.append(key, value);
    }
    params.delete('page');
    const query = params.toString();
    window.location = studentSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

studentSearchForm.addEventListener('submit', event => {
    event.preventDefault();
    submitStudentSearch();
});

const studentFilterForm = document.getElementById('student-filter-form');
const studentFilterReset = document.getElementById('student-filter-reset');

studentFilterForm.addEventListener('submit', () => {
    ['email_verified', 'has_notes', 'has_photo'].forEach(name => {
        const checked = studentFilterForm.querySelector(`input[name="${name}"]:checked`);
        if (checked && checked.value === 'any') {
            checked.disabled = true;
        }
    });
    const emailVerifiedAt = document.getElementById('filter-email-verified-at');
    if (emailVerifiedAt && !emailVerifiedAt.value) {
        emailVerifiedAt.disabled = true;
    }
});

studentFilterReset.addEventListener('click', () => {
    window.location = studentFilterReset.dataset.resetUrl;
});
</script>
@endsection
