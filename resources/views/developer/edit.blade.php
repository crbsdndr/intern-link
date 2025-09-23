@extends('layouts.app')

@section('title', 'Update Developer')

@section('content')
<h1>Update Developer</h1>
@include('developer.form', ['action' => url('/developers/' . $developer->id), 'method' => 'PUT', 'developer' => $developer])
@endsection
