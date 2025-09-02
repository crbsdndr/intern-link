<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home');
Route::view('/dashboard', 'home');

Route::view('/student', 'paginated', ['title' => 'Students', 'endpoint' => '/api/student']);
Route::view('/supervisor', 'paginated', ['title' => 'Supervisors', 'endpoint' => '/api/supervisor']);
Route::view('/application', 'paginated', ['title' => 'Applications', 'endpoint' => '/api/application']);
Route::view('/internship', 'paginated', ['title' => 'Internships', 'endpoint' => '/api/internship']);
Route::view('/monitoring', 'paginated', ['title' => 'Monitoring Logs', 'endpoint' => '/api/monitoring']);
