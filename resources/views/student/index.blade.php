@extends('layouts.app')

@section('title', 'Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Students</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" title="Filter"><i class="bi bi-funnel"></i></button>
        <a href="/student/add" class="btn btn-primary">Add</a>
    </div>
</div>
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
@endsection
