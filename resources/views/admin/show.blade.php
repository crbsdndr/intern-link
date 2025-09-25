@extends('layouts.app')

@section('title', 'Admin Details')

@section('content')
<div class="page-header">
    <h1>Admin Details</h1>
</div>
<div class="section-card">
    <dl class="row mb-0">
        <dt class="col-sm-4">Name</dt>
        <dd class="col-sm-8">{{ $admin->name }}</dd>

        <dt class="col-sm-4">Email</dt>
        <dd class="col-sm-8">{{ $admin->email }}</dd>

        <dt class="col-sm-4">Phone</dt>
        <dd class="col-sm-8">{{ $admin->phone ?? '—' }}</dd>

        <dt class="col-sm-4">Email Verified At</dt>
        <dd class="col-sm-8">
            @if($admin->email_verified_at)
                {{ $admin->email_verified_at }}
            @else
                <span class="fw-semibold">False</span>
            @endif
        </dd>
    </dl>
</div>
<a href="/admins" class="btn btn-secondary">Back</a>
@endsection
