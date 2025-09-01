@extends('layouts.app')

@section('title', 'Supervisors')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Supervisors</h1>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary" title="Filter"><i class="bi bi-funnel"></i></button>
        <a href="/supervisor/add" class="btn btn-primary">Add</a>
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Supervisor Number</th>
            <th>Name</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($supervisors as $supervisor)
        <tr>
            <td>{{ $supervisor->supervisor_number }}</td>
            <td>{{ $supervisor->name }}</td>
            <td>{{ $supervisor->department }}</td>
            <td>
                <a href="/supervisor/{{ $supervisor->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/supervisor/{{ $supervisor->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/supervisor/{{ $supervisor->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4">No supervisors found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
