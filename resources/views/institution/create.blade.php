@extends('layouts.app')

@section('title', 'Create Institution')

@section('content')
<h1>Create Institution</h1>
@include('institution.form', [
    'action' => '/institutions',
    'method' => 'POST',
    'institution' => null,
    'cities' => $cities,
    'provinces' => $provinces,
    'industries' => $industries,
    'periods' => $periods,
])
@endsection
