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
            <li><strong>{{ $student->name }}</strong></li>
            <li>Email: {{ $student->email }}</li>
            <li>Phone: {{ $student->phone }}</li>
            <li>Role: {{ $student->role }}</li>
            <li>User ID: {{ $student->user_id }}</li>
            <li>Student Number: {{ $student->student_number }}</li>
            <li>National Student Number: {{ $student->national_sn }}</li>
            <li>Major: {{ $student->major }}</li>
            <li>Batch: {{ $student->batch }}</li>
            <li>Notes: {{ $student->notes }}</li>
        </ul>
    </div>
</div>
<a href="/student" class="btn btn-secondary mt-3">Back</a>
@endsection
