@extends('layouts.app')

@section('title', 'Supervisor Details')

@section('content')
<div class="page-header">
    <h1>Supervisor Details</h1>
</div>
<div class="section-card">
    <div class="row g-4 align-items-start">
        <div class="col-md-4 col-lg-3">
            @if($supervisor->photo)
                <img src="{{ $supervisor->photo }}" alt="{{ $supervisor->name }}" class="img-fluid rounded-4 border border-light-subtle w-100" style="object-fit: cover; aspect-ratio: 3 / 4;">
            @else
                <div class="border border-light-subtle rounded-4 d-flex align-items-center justify-content-center bg-light-subtle" style="aspect-ratio: 3 / 4;">
                    <span class="text-muted">No Photo</span>
                </div>
            @endif
        </div>
        <div class="col-md-8 col-lg-9">
            <dl class="row mb-0">
                <dt class="col-sm-4">Name</dt>
                <dd class="col-sm-8">{{ $supervisor->name }}</dd>

                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">{{ $supervisor->email }}</dd>

                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8">{{ $supervisor->phone ?? '—' }}</dd>

                <dt class="col-sm-4">Email Verified At</dt>
                <dd class="col-sm-8">{{ $supervisor->email_verified_at ?? 'False' }}</dd>

                <dt class="col-sm-4">Supervisor Number</dt>
                <dd class="col-sm-8">{{ $supervisor->supervisor_number }}</dd>

                <dt class="col-sm-4">Department</dt>
                <dd class="col-sm-8">{{ $supervisor->department ?? '—' }}</dd>

                <dt class="col-sm-4">Notes</dt>
                <dd class="col-sm-8">{{ $supervisor->notes ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
<a href="/supervisors" class="btn btn-secondary">Back</a>
@endsection
