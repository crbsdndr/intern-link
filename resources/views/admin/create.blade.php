@extends('layouts.app')

@section('title', 'Add Admin')

@section('content')
<h1>Add Admin</h1>
@include('admin.form', ['action' => '/admin', 'method' => 'POST', 'admin' => null])
@endsection
