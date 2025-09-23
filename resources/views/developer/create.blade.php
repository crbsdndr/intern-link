@extends('layouts.app')

@section('title', 'Create Developer')

@section('content')
<h1>Create Developer</h1>
@include('developer.form', ['action' => url('/developers'), 'method' => 'POST', 'developer' => null])
@endsection
