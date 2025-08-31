@extends('layouts.app')

@section('title', 'Institutions')

@section('content')
<div class="d-flex justify-content-between mb-3">
    <h1>Institutions</h1>
    <a href="/institution/add" class="btn btn-primary">Add</a>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>City</th>
            <th>Province</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @forelse($institutions as $institution)
        <tr>
            <td>{{ $institution->name }}</td>
            <td>{{ $institution->city }}</td>
            <td>{{ $institution->province }}</td>
            <td>
                <a href="/institution/{{ $institution->id }}/see" class="btn btn-sm btn-secondary">View</a>
                <a href="/institution/{{ $institution->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                <form action="/institution/{{ $institution->id }}" method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4">No institutions found.</td></tr>
        @endforelse
    </tbody>
</table>
@endsection
