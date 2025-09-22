@extends('layouts.app')

@section('title', 'Admin Details')

@section('content')
<h1>Admin Details</h1>
<div class="card">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Name:</strong> {{ $admin->name }}</li>
            <li class="mb-2"><strong>Email:</strong> {{ $admin->email }}</li>
            <li class="mb-2"><strong>Phone:</strong> {{ $admin->phone ?? '—' }}</li>
            <li>
                <strong>Email Verified At:</strong>
                @if($admin->email_verified_at)
                    {{ $admin->email_verified_at }}
                @else
                    <span class="fw-semibold">False</span>
                @endif
            </li>
        </ul>
    </div>
</div>
<a href="/admins" class="btn btn-secondary mt-3">Back</a>
@endsection
