@extends('layouts.app')

@section('title', 'Create Student')

@section('content')
<h1>Create Student</h1>
@include('student.form', ['action' => route('students.store'), 'method' => 'POST', 'student' => null])
@endsection
