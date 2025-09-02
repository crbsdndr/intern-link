<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{StudentController, SupervisorController, ApplicationController, InternshipController, MonitoringLogController};

Route::get('/student', [StudentController::class, 'index']);
Route::get('/supervisor', [SupervisorController::class, 'index']);
Route::get('/application', [ApplicationController::class, 'index']);
Route::get('/internship', [InternshipController::class, 'index']);
Route::get('/monitoring', [MonitoringLogController::class, 'index']);
