@extends('layouts.app')

@section('title', 'Students')

@section('content')
@php($isStudent = session('role') === 'student')
@php($queryParams = request()->query())
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Students</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ route('students.index') }}" id="student-search-form" class="position-relative">
            <div class="input-group" style="min-width:280px;">
                <input type="search" name="q" id="student-search-input" class="form-control" placeholder="Search students..." aria-label="Search" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary" type="submit" id="student-search-submit">
                    <i class="bi bi-search"></i>
                </button>
                <button class="btn btn-outline-secondary" type="button" id="student-search-clear" @if(!request('q')) style="display:none;" @endif>
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div id="student-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-2 d-none">
                <div class="spinner-border spinner-border-sm text-secondary"></div>
            </div>
            @foreach(request()->except('q','page') as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#studentFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        @if($isStudent)
            <button class="btn btn-primary" disabled>Create Student</button>
        @else
            <a href="{{ route('students.create') }}" class="btn btn-primary">Create Student</a>
        @endif
    </div>
</div>

@if(count($filters))
    <div class="mb-3">
        @foreach($filters as $param => $label)
            @php($queryWithout = $queryParams)
            @php(unset($queryWithout[$param], $queryWithout['page']))
            @php($queryString = http_build_query($queryWithout))
            <a href="{{ route('students.index') . ($queryString ? '?' . $queryString : '') }}" class="badge bg-secondary text-decoration-none me-2">
                {{ $label }} <i class="bi bi-x ms-1"></i>
            </a>
        @endforeach
    </div>
@endif

<table class="table table-bordered align-middle">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>NIS</th>
            <th>NISN</th>
            <th>Major</th>
            <th>Class</th>
            <th>Batch</th>
            <th class="text-center" style="width:160px;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
        <tr>
            <td>{{ $student->name }}</td>
            <td>{{ $student->email }}</td>
            <td>{{ $student->phone ?? '-' }}</td>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->national_sn }}</td>
            <td>{{ $student->major }}</td>
            <td>{{ $student->class }}</td>
            <td>{{ $student->batch }}</td>
            <td class="text-center">
                <a href="{{ route('students.show', $student->id) }}" class="btn btn-sm btn-secondary">View</a>
                <a href="{{ route('students.edit', $student->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="9" class="text-center">
                @if(request('q'))
                    No students found for "{{ request('q') }}".
                @else
                    No students found.
                @endif
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="text-muted">Total Students: {{ $students->total() }}</p>

<div class="d-flex justify-content-between align-items-center">
    <span>Page {{ $students->currentPage() }} out of {{ $students->lastPage() }}</span>
    <div class="d-flex gap-2">
        @if ($students->onFirstPage())
            <span class="text-muted">Back</span>
        @else
            <a href="{{ $students->previousPageUrl() }}" class="btn btn-outline-secondary">Back</a>
        @endif
        @if ($students->hasMorePages())
            <a href="{{ $students->nextPageUrl() }}" class="btn btn-outline-secondary">Next</a>
        @else
            <span class="text-muted">Next</span>
        @endif
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="studentFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="student-filter-form">
            <input type="hidden" name="q" value="{{ request('q') }}">
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" class="form-control" name="name" value="{{ request('name') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" value="{{ request('email') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone" value="{{ request('phone') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Is Email Verified?</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_email_verified" id="is_email_verified_true" value="true" @checked(request('is_email_verified') === 'true')>
                        <label class="form-check-label" for="is_email_verified_true">True</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_email_verified" id="is_email_verified_false" value="false" @checked(request('is_email_verified') === 'false')>
                        <label class="form-check-label" for="is_email_verified_false">False</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="is_email_verified" id="is_email_verified_any" value="" @checked(request('is_email_verified') === null || request('is_email_verified') === '')>
                        <label class="form-check-label" for="is_email_verified_any">Any</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Verified At</label>
                <input type="date" class="form-control" name="email_verified_at" value="{{ request('email_verified_at') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Student Number</label>
                <input type="text" class="form-control" name="student_number" value="{{ request('student_number') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">National Student Number</label>
                <input type="text" class="form-control" name="national_sn" value="{{ request('national_sn') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Major</label>
                <input type="text" class="form-control" name="major" value="{{ request('major') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Class</label>
                <input type="text" class="form-control" name="class" value="{{ request('class') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Batch (Year)</label>
                <input type="number" class="form-control" name="batch" min="1900" max="2100" step="1" value="{{ request('batch') }}">
            </div>
            <div class="mb-3">
                <label class="form-label">Have notes?</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_notes" id="has_notes_true" value="true" @checked(request('has_notes') === 'true')>
                        <label class="form-check-label" for="has_notes_true">True</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_notes" id="has_notes_false" value="false" @checked(request('has_notes') === 'false')>
                        <label class="form-check-label" for="has_notes_false">False</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_notes" id="has_notes_any" value="" @checked(request('has_notes') === null || request('has_notes') === '')>
                        <label class="form-check-label" for="has_notes_any">Any</label>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Have photo?</label>
                <div class="d-flex gap-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_photo" id="has_photo_true" value="true" @checked(request('has_photo') === 'true')>
                        <label class="form-check-label" for="has_photo_true">True</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_photo" id="has_photo_false" value="false" @checked(request('has_photo') === 'false')>
                        <label class="form-check-label" for="has_photo_false">False</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="has_photo" id="has_photo_any" value="" @checked(request('has_photo') === null || request('has_photo') === '')>
                        <label class="form-check-label" for="has_photo_any">Any</label>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" id="student-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
var studentSearchForm = document.getElementById('student-search-form');
var studentSearchInput = document.getElementById('student-search-input');
var studentSearchClear = document.getElementById('student-search-clear');
var studentSearchSpinner = document.getElementById('student-search-spinner');
var studentSearchTimer;

function submitStudentSearch(){
    studentSearchSpinner.classList.remove('d-none');
    var params = new URLSearchParams(new FormData(studentSearchForm));
    if(!studentSearchInput.value) { params.delete('q'); }
    params.delete('page');
    var query = params.toString();
    window.location = studentSearchForm.getAttribute('action') + (query ? '?' + query : '');
}

studentSearchInput.addEventListener('input', function(){
    studentSearchClear.style.display = this.value ? 'block' : 'none';
    clearTimeout(studentSearchTimer);
    studentSearchTimer = setTimeout(submitStudentSearch, 300);
});

studentSearchForm.addEventListener('submit', function(e){
    e.preventDefault();
    submitStudentSearch();
});

studentSearchClear.addEventListener('click', function(){
    studentSearchInput.value = '';
    submitStudentSearch();
});

var filterReset = document.getElementById('student-filter-reset');
if (filterReset) {
    filterReset.addEventListener('click', function(){
        window.location = '{{ route('students.index') }}';
    });
}
</script>
@endsection
