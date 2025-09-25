@extends('layouts.app')

@section('title', 'Developer Details')

@section('content')
<div class="page-header">
    <h1>Developer Details</h1>
</div>
<div class="section-card">
    <dl class="row mb-0">
        <dt class="col-sm-4">Name</dt>
        <dd class="col-sm-8">{{ $developer->name }}</dd>

        <dt class="col-sm-4">Email</dt>
        <dd class="col-sm-8">{{ $developer->email }}</dd>

        <dt class="col-sm-4">Phone</dt>
        <dd class="col-sm-8">{{ $developer->phone ?? '—' }}</dd>

        <dt class="col-sm-4">Email Verified At</dt>
        <dd class="col-sm-8">
            @if($developer->email_verified_at)
                {{ $developer->email_verified_at }}
            @else
                <span class="fw-semibold">False</span>
            @endif
        </dd>
    </dl>
</div>
<div class="d-flex flex-wrap gap-2">
    <a href="/developers" class="btn btn-secondary">Back</a>
    @if(session('user_id') == $developer->id)
        <a href="/developers/{{ $developer->id }}/update" class="btn btn-primary">Update</a>
    @endif
</div>
@endsection
