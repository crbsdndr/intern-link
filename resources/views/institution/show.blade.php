@extends('layouts.app')

@section('title', 'Institution Details')

@section('content')
<h1>Institution Details</h1>
<div class="row">
    <div class="col-md-3">
        @if($institution->photo)
            <img src="{{ $institution->photo }}" alt="{{ $institution->name }}" class="img-fluid">
        @else
            <div class="bg-secondary text-white text-center p-3">No Photo</div>
        @endif
    </div>
    <div class="col-md-9">
        <ul class="list-unstyled">
            <li><strong>{{ $institution->name }}</strong></li>
            <li>Address: {{ $institution->address }}</li>
            <li>City: {{ $institution->city }}</li>
            <li>Province: {{ $institution->province }}</li>
            <li>Website: {{ $institution->website }}</li>
            <li>Industry: {{ $institution->industry }}</li>
            <li>Contact Name: {{ $institution->contact_name }}</li>
            <li>Contact Email: {{ $institution->contact_email }}</li>
            <li>Contact Phone: {{ $institution->contact_phone }}</li>
            <li>Contact Position: {{ $institution->contact_position }}</li>
            <li>Contact Primary: {{ $institution->contact_primary ? 'Yes' : 'No' }}</li>
            <li>Period Year: {{ $institution->period_year }}</li>
            <li>Period Term: {{ $institution->period_term }}</li>
            <li>Quota: {{ $institution->quota }}</li>
            <li>Used: {{ $institution->used }}</li>
            <li>Notes: {{ $institution->quota_notes }}</li>
        </ul>
    </div>
</div>
<a href="/institution" class="btn btn-secondary mt-3">Back</a>
@endsection
