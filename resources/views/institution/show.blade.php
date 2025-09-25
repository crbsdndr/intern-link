@extends('layouts.app')

@section('title', 'Institution Details')

@section('content')
<div class="page-header">
    <h1>Institution Details</h1>
</div>
<div class="section-card">
    <div class="row g-4 align-items-start">
        <div class="col-md-4 col-lg-3">
            @if($institution->photo)
                <img src="{{ $institution->photo }}" alt="{{ $institution->name }}" class="img-fluid rounded-4 border border-light-subtle w-100 mb-3" style="object-fit: cover; aspect-ratio: 4 / 3;">
                <p class="small text-break mb-0"><a href="{{ $institution->photo }}" target="_blank" rel="noopener">{{ $institution->photo }}</a></p>
            @else
                <div class="border border-light-subtle rounded-4 d-flex align-items-center justify-content-center bg-light-subtle" style="aspect-ratio: 4 / 3;">
                    <span class="text-muted">No Photo</span>
                </div>
            @endif
        </div>
        <div class="col-md-8 col-lg-9">
        <dl class="row mb-0">
            <dt class="col-sm-4">Name</dt>
            <dd class="col-sm-8">{{ $institution->name }}</dd>

            <dt class="col-sm-4">Address</dt>
            <dd class="col-sm-8">{{ $institution->address ?? '—' }}</dd>

            <dt class="col-sm-4">City</dt>
            <dd class="col-sm-8">{{ $institution->city ?? '—' }}</dd>

            <dt class="col-sm-4">Province</dt>
            <dd class="col-sm-8">{{ $institution->province ?? '—' }}</dd>

            <dt class="col-sm-4">Website</dt>
            <dd class="col-sm-8">
                @if($institution->website)
                    <a href="{{ $institution->website }}" target="_blank" rel="noopener">{{ $institution->website }}</a>
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-4">Industry</dt>
            <dd class="col-sm-8">{{ $institution->industry ?? '—' }}</dd>

            <dt class="col-sm-4">Notes</dt>
            <dd class="col-sm-8">{{ $institution->notes ?? '—' }}</dd>

            <dt class="col-sm-4">Contact Name</dt>
            <dd class="col-sm-8">{{ $institution->contact_name ?? '—' }}</dd>

            <dt class="col-sm-4">Contact E-Mail</dt>
            <dd class="col-sm-8">{{ $institution->contact_email ?? '—' }}</dd>

            <dt class="col-sm-4">Contact Phone</dt>
            <dd class="col-sm-8">{{ $institution->contact_phone ?? '—' }}</dd>

            <dt class="col-sm-4">Contact Position</dt>
            <dd class="col-sm-8">{{ $institution->contact_position ?? '—' }}</dd>

            <dt class="col-sm-4">Contact Is Primary?</dt>
            <dd class="col-sm-8">
                @if(!is_null($institution->contact_primary))
                    {{ $institution->contact_primary ? 'True' : 'False' }}
                @else
                    —
                @endif
            </dd>

            <dt class="col-sm-4">Period Year</dt>
            <dd class="col-sm-8">{{ $institution->period_year ?? '—' }}</dd>

            <dt class="col-sm-4">Period Term</dt>
            <dd class="col-sm-8">{{ $institution->period_term ?? '—' }}</dd>

            <dt class="col-sm-4">Quota</dt>
            <dd class="col-sm-8">{{ $institution->quota ?? '—' }}</dd>

            <dt class="col-sm-4">Used</dt>
            <dd class="col-sm-8">{{ $institution->used ?? '—' }}</dd>
        </dl>
        </dl>
        </div>
    </div>
</div>
<a href="/institutions" class="btn btn-secondary">Back</a>
@endsection
