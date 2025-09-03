@extends('layouts.app')

@section('title', 'Developer Detail')

@section('content')
<h1>Developer Detail</h1>
<table class="table">
    <tr><th>Name</th><td>{{ $developer->name }}</td></tr>
    <tr><th>Email</th><td>{{ $developer->email }}</td></tr>
    <tr><th>Phone</th><td>{{ $developer->phone }}</td></tr>
    <tr><th>Role</th><td>{{ $developer->role }}</td></tr>
</table>
<a href="/developer" class="btn btn-secondary">Back</a>
@if(session('user_id') == $developer->id)
    <a href="/developer/{{ $developer->id }}/edit" class="btn btn-primary">Edit</a>
@endif
@endsection
