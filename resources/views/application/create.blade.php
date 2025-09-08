@extends('layouts.app')

@section('title', 'Add Application')

@section('content')
<h1>Add Application</h1>
@include('application.form', ['action' => '/application', 'method' => 'POST', 'application' => null, 'multi' => true])
@endsection
