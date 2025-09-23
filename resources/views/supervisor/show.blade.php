@extends('layouts.app')

@section('title', 'Supervisor Details')

@section('content')
<h1>Supervisor Details</h1>
<div class="row">
    <div class="col-md-3">
        @if($supervisor->photo)
            <img src="{{ $supervisor->photo }}" alt="{{ $supervisor->name }}" class="img-fluid">
        @else
            <div class="bg-secondary text-white text-center p-3">No Photo</div>
        @endif
    </div>
    <div class="col-md-9">
        <ul class="list-unstyled">
            <li><strong>{{ $supervisor->name }}</strong></li>
            <li>Email: {{ $supervisor->email }}</li>
            <li>Phone: {{ $supervisor->phone }}</li>
            <li>Role: {{ $supervisor->role }}</li>
            <li>User ID: {{ $supervisor->user_id }}</li>
            <li>Supervisor Number: {{ $supervisor->supervisor_number }}</li>
            <li>Department: {{ $supervisor->department }}</li>
            <li>Notes: {{ $supervisor->notes }}</li>
        </ul>
    </div>
</div>
<a href="/supervisor" class="btn btn-secondary mt-3">Back</a>
@endsection
