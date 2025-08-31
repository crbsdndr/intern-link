@extends('layouts.app')

@section('title', 'Add Internship')

@section('content')
<h1>Add Internship</h1>
@include('internship.form', ['action' => '/internship', 'method' => 'POST', 'internship' => null, 'applications' => $applications, 'periods' => $periods])
@endsection
