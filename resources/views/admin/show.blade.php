@extends('layouts.app')

@section('title', 'Admin Details')

@section('content')
<h1>Admin Details</h1>
<div class="row">
    <div class="col-md-9">
        <ul class="list-unstyled">
            <li><strong>{{ $admin->name }}</strong></li>
            <li>Email: {{ $admin->email }}</li>
            <li>Phone: {{ $admin->phone }}</li>
            <li>Role: {{ $admin->role }}</li>
            <li>User ID: {{ $admin->id }}</li>
            <li>Created At: {{ $admin->created_at }}</li>
            <li>Updated At: {{ $admin->updated_at }}</li>
        </ul>
    </div>
</div>
<a href="/admin" class="btn btn-secondary mt-3">Back</a>
@endsection
