@extends('layouts.app')

@section('title', 'Update Institution')

@section('content')
<h1>Update Institution</h1>
@include('institution.form', [
    'action' => "/institutions/{$institution->id}",
    'method' => 'PUT',
    'institution' => $institution,
    'cities' => $cities,
    'provinces' => $provinces,
    'industries' => $industries,
    'periods' => $periods,
])
@endsection
