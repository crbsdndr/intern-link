@extends('layouts.app')

@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Students</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary position-relative" data-bs-toggle="offcanvas" data-bs-target="#studentFilter" title="Filter">
            <i class="bi bi-funnel"></i>
            @if(count($filters))
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">{{ count($filters) }}</span>
            @endif
        </button>
        <a href="/student/add" class="btn btn-primary">Add</a>
    </div>
</div>

@if(count($filters))
@php($current = request()->query())
<div class="mb-3">
    @foreach($filters as $param => $label)
        @php($q = $current)
        @php(unset($q[$param]))
        <a href="{{ url()->current() . ($q ? '?' . http_build_query($q) : '') }}" class="badge bg-secondary text-decoration-none me-2">
            {{ $label }} <i class="bi bi-x ms-1"></i>
        </a>
    @endforeach
</div>
@endif

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student Number</th>
            <th>Name</th>
            <th>Major</th>
            <th>Batch</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($students as $student)
        <tr>
            <td>{{ $student->student_number }}</td>
            <td>{{ $student->name }}</td>
            <td>{{ $student->major }}</td>
            <td>{{ $student->batch }}</td>
            <td>
                <a href="/student/{{ $student->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/student/{{ $student->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/student/{{ $student->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No students found.</td></tr>
        @endforelse
    </tbody>
</table>

{{ $students->links() }}

<div class="offcanvas offcanvas-end" tabindex="-1" id="studentFilter">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Filter</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="student-filter-form">
            <div class="mb-3">
                <label class="form-label">Major</label>
                <input type="text" class="form-control" name="major~" value="{{ request('major~') }}">
            </div>
            @php($batchValue = '')
            @if(request()->has('batch') && str_starts_with(request('batch'), 'in:'))
                @php($batchValue = substr(request('batch'), 3))
            @endif
            <div class="mb-3">
                <label class="form-label">Batch</label>
                <input type="text" class="form-control" id="filter-batch" placeholder="2023/2024,2024/2025" value="{{ $batchValue }}">
                <input type="hidden" name="batch" id="filter-batch-hidden">
            </div>
            @php($createdRange = request('created_at'))
            @php($createdStart = $createdEnd = '')
            @if($createdRange && str_starts_with($createdRange, 'range:'))
                @php([$createdStart, $createdEnd] = array_pad(explode(',', substr($createdRange, 6)), 2, ''))
            @endif
            <div class="mb-3">
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
            <div class="mb-3">
                <label class="form-label">Updated At</label>
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" id="updated_at_start" value="{{ $updatedStart }}">
                    <input type="date" class="form-control" id="updated_at_end" value="{{ $updatedEnd }}">
                </div>
                <input type="hidden" name="updated_at" id="updated_at_range">
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary" id="student-filter-reset">Reset</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('student-filter-form').addEventListener('submit', function(){
    var batch = document.getElementById('filter-batch').value.trim();
    var batchHidden = document.getElementById('filter-batch-hidden');
    if(batch){
        batchHidden.value = 'in:' + batch.split(',').map(function(s){return s.trim();}).join(',');
    }else{
        batchHidden.disabled = true;
    }

    var cs = document.getElementById('created_at_start').value;
    var ce = document.getElementById('created_at_end').value;
    var ch = document.getElementById('created_at_range');
    if(cs || ce){
        ch.value = 'range:' + cs + ',' + ce;
    }else{
        ch.disabled = true;
    }

    var us = document.getElementById('updated_at_start').value;
    var ue = document.getElementById('updated_at_end').value;
    var uh = document.getElementById('updated_at_range');
    if(us || ue){
        uh.value = 'range:' + us + ',' + ue;
    }else{
        uh.disabled = true;
    }
});

document.getElementById('student-filter-reset').addEventListener('click', function(){
    window.location = window.location.pathname;
});
</script>
@endsection

