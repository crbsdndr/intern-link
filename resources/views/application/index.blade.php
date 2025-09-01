@extends('layouts.app')

@section('title', 'Applications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Applications</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" title="Filter"><i class="bi bi-funnel"></i></button>
        <a href="/application/add" class="btn btn-primary">Add</a>
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Institution Name</th>
            <th>Year</th>
            <th>Term</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($applications as $application)
        <tr>
            <td>{{ $application->student_name }}</td>
            <td>{{ $application->institution_name }}</td>
            <td>{{ $application->period_year }}</td>
            <td>{{ $application->period_term }}</td>
            <td>
                <a href="/application/{{ $application->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/application/{{ $application->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/application/{{ $application->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5">No applications found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
