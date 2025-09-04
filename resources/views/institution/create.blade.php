@extends('layouts.app')

@section('title', 'Add Institution')

@section('content')
<h1>Add Institution</h1>
@include('institution.form', ['action' => '/institution', 'method' => 'POST', 'institution' => null, 'cities' => $cities, 'provinces' => $provinces, 'students' => $students])
@endsection
