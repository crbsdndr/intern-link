@extends('layouts.app')

@section('title', 'Students')

@section('content')
@php($isStudent = session('role') === 'student')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="mb-0">Students</h1>
    <div class="d-flex align-items-center gap-2">
        <form method="get" action="{{ url()->current() }}" id="student-search-form" class="position-relative">
            <div class="input-group" style="min-width:280px;">
                <input type="search" name="q" id="student-search-input" class="form-control" placeholder="Search..." aria-label="Search students" value="{{ request('q') }}">
                <button class="btn btn-outline-secondary" type="submit" id="student-search-submit" aria-label="Search">
                    <i class="bi bi-search"></i>
                </button>
                <button class="btn btn-outline-secondary" type="button" id="student-search-clear" @if(!request('q')) style="display:none;" @endif aria-label="Clear search">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div id="student-search-spinner" class="position-absolute top-50 end-0 translate-middle-y me-2 d-none" aria-hidden="true">
                <div class="spinner-border spinner-border-sm text-secondary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            @foreach(request()->except('q','page') as $param => $value)
                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
            @endforeach
        </form>
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#studentFilter" aria-controls="studentFilter" title="Filter">
            <i class="bi bi-funnel"></i>
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
    <div class="mb-3">
        @foreach($filters as $param => $label)
            @php($queryWithoutParam = Arr::except(request()->query(), [$param, 'page']))
            <a href="{{ url()->current() . ($queryWithoutParam ? '?' . http_build_query($queryWithoutParam) : '') }}" class="badge bg-secondary text-decoration-none me-2">
                {{ $label }} <i class="bi bi-x ms-1"></i>
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered align-middle mb-3">
        <thead class="table-light">
            <tr>
                <th scope="col">No</th>
                <th scope="col">Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">NIS</th>
                <th scope="col">NISN</th>
                <th scope="col">Major</th>
                <th scope="col">Class</th>
                <th scope="col">Batch</th>
                <th scope="col" class="text-center">Action</th>
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
                    <a href="/students/{{ $student->id }}/read" class="btn btn-sm btn-secondary">View</a>
                    <a href="/students/{{ $student->id }}/update" class="btn btn-sm btn-warning">Edit</a>
                    <form action="/students/{{ $student->id }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this student?');">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="10" class="text-center">
                    @if(request('q'))
                        No results found for "{{ request('q') }}".
                    @else
                        No students found.
                    @endif
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

<p class="text-muted">Total students: {{ $students->total() }}</p>

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
            @php($emailVerifiedValue = request()->has('email_verified') ? request('email_verified') : '')
            <div>
                <span class="form-label d-block">Is Email Verified?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="filter-email-verified-any" value="" {{ $emailVerifiedValue === '' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-email-verified-any">Any</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="filter-email-verified-true" value="true" {{ $emailVerifiedValue === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-email-verified-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="email_verified" id="filter-email-verified-false" value="false" {{ $emailVerifiedValue === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-email-verified-false">False</label>
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
            @php($hasNotesValue = request()->has('has_notes') ? request('has_notes') : '')
            <div>
                <span class="form-label d-block">Have notes?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-any" value="" {{ $hasNotesValue === '' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-any">Any</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-true" value="true" {{ $hasNotesValue === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_notes" id="filter-has-notes-false" value="false" {{ $hasNotesValue === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-notes-false">False</label>
                </div>
            </div>
            @php($hasPhotoValue = request()->has('has_photo') ? request('has_photo') : '')
            <div>
                <span class="form-label d-block">Have photo?</span>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-any" value="" {{ $hasPhotoValue === '' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-any">Any</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-true" value="true" {{ $hasPhotoValue === 'true' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-true">True</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="has_photo" id="filter-has-photo-false" value="false" {{ $hasPhotoValue === 'false' ? 'checked' : '' }}>
                    <label class="form-check-label" for="filter-has-photo-false">False</label>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary flex-fill" id="student-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary flex-fill">Apply</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const studentSearchForm = document.getElementById('student-search-form');
    const studentSearchInput = document.getElementById('student-search-input');
    const studentSearchClear = document.getElementById('student-search-clear');
    const studentSearchSpinner = document.getElementById('student-search-spinner');
    let studentSearchTimer;

    function submitStudentSearch(){
        studentSearchSpinner.classList.remove('d-none');
        const formData = new FormData(studentSearchForm);
        if(!studentSearchInput.value){
            formData.delete('q');
        }
        formData.delete('page');
        const params = new URLSearchParams(formData);
        const query = params.toString();
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

    document.getElementById('student-filter-reset').addEventListener('click', function(){
        const url = new URL(window.location.href);
        ['name','email','phone','email_verified','email_verified_at','student_number','national_sn','major','class','batch','has_notes','has_photo','page']
            .forEach(param => url.searchParams.delete(param));
        window.location = url.pathname + (url.search ? url.search : '');
    });
})();
</script>
@endpush
@endsection
