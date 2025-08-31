@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<h1>Add Student</h1>
@include('student.form', ['action' => '/student', 'method' => 'POST', 'student' => null])
@endsection
