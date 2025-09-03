@extends('layouts.app')

@section('title', 'Edit Developer')

@section('content')
<h1>Edit Developer</h1>
@include('developer.form', ['action' => "/developer/{$developer->id}", 'method' => 'PUT', 'developer' => $developer])
@endsection
