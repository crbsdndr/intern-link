@extends('layouts.app')

@section('title', 'Edit Institution')

@section('content')
<h1>Edit Institution</h1>
@include('institution.form', ['action' => "/institution/{$institution->id}", 'method' => 'PUT', 'institution' => $institution, 'cities' => $cities, 'provinces' => $provinces, 'students' => $students])
@endsection
