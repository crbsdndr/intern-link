@extends('layouts.app')

@section('title', 'Student Details')

@section('content')
<h1>Student Details</h1>
<div class="row">
    <div class="col-md-3">
        @if($student->photo)
            <img src="{{ $student->photo }}" alt="{{ $student->name }}" class="img-fluid">
        @else
            <div class="bg-secondary text-white text-center p-3">No Photo</div>
        @endif
    </div>
    <div class="col-md-9">
        <ul class="list-unstyled">
            <li><strong>Photo:</strong> {{ $student->photo ?? '-' }}</li>
            <li><strong>Name:</strong> {{ $student->name }}</li>
            <li><strong>Email:</strong> {{ $student->email }}</li>
            <li><strong>Phone:</strong> {{ $student->phone ?? '-' }}</li>
            <li><strong>Email Verified At:</strong> {{ $student->email_verified_at ?? 'False' }}</li>
            <li><strong>Student Number:</strong> {{ $student->student_number }}</li>
            <li><strong>National Student Number:</strong> {{ $student->national_sn }}</li>
            <li><strong>Major:</strong> {{ $student->major }}</li>
            <li><strong>Class:</strong> {{ $student->class }}</li>
            <li><strong>Batch:</strong> {{ $student->batch }}</li>
            <li><strong>Notes:</strong> {{ $student->notes ?? '-' }}</li>
        </ul>
    </div>
</div>
@php($isStudent = session('role') === 'student')
<div class="mt-3">
    <a href="{{ route('students.index') }}" class="btn btn-secondary">Back</a>
    @if($isStudent)
        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    @else
        <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('students.destroy', $student->id) }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    @endif
</div>
@endsection
