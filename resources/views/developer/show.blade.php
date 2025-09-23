@extends('layouts.app')

@section('title', 'Developer Details')

@section('content')
<h1>Developer Details</h1>
<div class="card">
    <div class="card-body">
        <ul class="list-unstyled mb-0">
            <li class="mb-2"><strong>Name:</strong> {{ $developer->name }}</li>
            <li class="mb-2"><strong>Email:</strong> {{ $developer->email }}</li>
            <li class="mb-2"><strong>Phone:</strong> {{ $developer->phone ?? '—' }}</li>
            <li>
                <strong>Email Verified At:</strong>
                @if($developer->email_verified_at)
                    {{ $developer->email_verified_at }}
                @else
                    <span class="fw-semibold">False</span>
                @endif
            </li>
        </ul>
    </div>
</div>
<a href="/developers" class="btn btn-secondary mt-3">Back</a>
@if(session('user_id') == $developer->id)
    <a href="/developers/{{ $developer->id }}/update" class="btn btn-primary mt-3">Update</a>
@endif
@endsection
