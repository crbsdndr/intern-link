@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<div class="page-header">
    <h1>Student Details</h1>
</div>
<div class="section-card">
    <div class="row g-4 align-items-start">
        <div class="col-md-4 col-lg-3">
            @if($student->photo)
                <img src="{{ $student->photo }}" alt="{{ $student->name }}" class="img-fluid rounded-4 border border-light-subtle w-100" style="object-fit: cover; aspect-ratio: 3 / 4;">
            @else
                <div class="border border-light-subtle rounded-4 d-flex align-items-center justify-content-center bg-light-subtle" style="aspect-ratio: 3 / 4;">
                    <span class="text-muted">No Photo</span>
                </div>
            @endif
        </div>
        <div class="col-md-8 col-lg-9">
            <dl class="row mb-0">
            <dt class="col-sm-4">Name</dt>
            <dd class="col-sm-8">{{ $student->name }}</dd>

            <dt class="col-sm-4">Email</dt>
            <dd class="col-sm-8">{{ $student->email }}</dd>

            <dt class="col-sm-4">Phone</dt>
            <dd class="col-sm-8">{{ $student->phone ?? '—' }}</dd>

            <dt class="col-sm-4">Email Verified At</dt>
            <dd class="col-sm-8">
                @if($student->email_verified_at)
                    {{ $student->email_verified_at }}
                @else
                    <span class="fw-semibold">False</span>
                @endif
            </dd>

            <dt class="col-sm-4">Student Number</dt>
            <dd class="col-sm-8">{{ $student->student_number }}</dd>

            <dt class="col-sm-4">National Student Number</dt>
            <dd class="col-sm-8">{{ $student->national_sn }}</dd>

            <dt class="col-sm-4">Major</dt>
            <dd class="col-sm-8">{{ $student->major }}</dd>

            <dt class="col-sm-4">Class</dt>
            <dd class="col-sm-8">{{ $student->class }}</dd>

            <dt class="col-sm-4">Batch</dt>
            <dd class="col-sm-8">{{ $student->batch }}</dd>

            <dt class="col-sm-4">Notes</dt>
            <dd class="col-sm-8">{{ $student->notes ?? '—' }}</dd>
        </dl>
        </div>
    </div>
</div>
<div class="mt-4 d-flex flex-wrap gap-2">
    <a href="/students" class="btn btn-secondary">Back</a>
    <a href="/students/{{ $student->id }}/update" class="btn btn-warning">Update</a>
    <form action="/students/{{ $student->id }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this student?');">Delete</button>
    </form>
</div>
@endsection
