@extends('layouts.app')

@section('title', 'Add Developer')

@section('content')
<h1>Add Developer</h1>
@include('developer.form', ['action' => '/developer', 'method' => 'POST', 'developer' => null])
@endsection
