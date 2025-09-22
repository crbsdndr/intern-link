@extends('layouts.app')

@section('title', 'Create Application')

@section('content')
<h1>Create Application</h1>
@include('application.form', ['action' => url('/application'), 'method' => 'POST', 'application' => null, 'mode' => 'create'])
@endsection
