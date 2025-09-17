@extends('layouts.app')

@section('title', 'Create Student')

@section('content')
<h1>Create Student</h1>
@include('student.form', ['action' => '/students', 'method' => 'POST', 'student' => null])
@endsection
