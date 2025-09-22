@extends('layouts.app')

@section('title', 'Supervisor Details')

@section('content')
<h1>Supervisor Details</h1>
<div class="card">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Photo:</strong> {{ $supervisor->photo ?? '—' }}</li>
            <li class="mb-2"><strong>Name:</strong> {{ $supervisor->name }}</li>
            <li class="mb-2"><strong>Email:</strong> {{ $supervisor->email }}</li>
            <li class="mb-2"><strong>Phone:</strong> {{ $supervisor->phone ?? '—' }}</li>
            <li class="mb-2"><strong>Email Verified At:</strong> {{ $supervisor->email_verified_at ? $supervisor->email_verified_at : 'False' }}</li>
            <li class="mb-2"><strong>Supervisor Number:</strong> {{ $supervisor->supervisor_number }}</li>
            <li class="mb-2"><strong>Department:</strong> {{ $supervisor->department ?? '—' }}</li>
            <li><strong>Notes:</strong> {{ $supervisor->notes ?? '—' }}</li>
        </ul>
    </div>
</div>
<a href="/supervisors" class="btn btn-secondary mt-3">Back</a>
@endsection
