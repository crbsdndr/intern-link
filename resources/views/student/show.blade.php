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
            <li>Class: {{ $student->class }}</li>
            <li>Batch: {{ $student->batch }}</li>
            <li>Notes: {{ $student->notes }}</li>
        </ul>
    </div>
</div>
@php($isStudent = session('role') === 'student')
<div class="mt-3">
    <a href="/student" class="btn btn-secondary">Back</a>
    @if($isStudent)
        <a href="/student/{{ $student->id }}/edit" class="btn btn-warning">Edit</a>
        <form action="/student/{{ $student->id }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    @else
        <a href="/student/{{ $student->id }}/edit" class="btn btn-warning">Edit</a>
        <form action="/student/{{ $student->id }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Delete</button>
        </form>
    @endif
</div>
@endsection
