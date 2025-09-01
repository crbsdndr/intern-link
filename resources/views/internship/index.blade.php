@extends('layouts.app')

@section('title', 'Internships')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Internships</h1>
    <a href="/internship/add" class="btn btn-primary">Add</a>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Institution Name</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($internships as $internship)
        <tr>
            <td>{{ $internship->student_name }}</td>
            <td>{{ $internship->institution_name }}</td>
            <td>{{ $internship->start_date }}</td>
            <td>{{ $internship->end_date }}</td>
            <td>
                <a href="/internship/{{ $internship->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/internship/{{ $internship->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/internship/{{ $internship->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No internships found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
