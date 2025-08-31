<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\ApplicationController;

Route::view('/', 'home');
Route::view('/dashboard', 'home');
Route::prefix('application')->group(function () {
    Route::get('/', [ApplicationController::class, 'index']);
    Route::get('/add', [ApplicationController::class, 'create']);
    Route::post('/', [ApplicationController::class, 'store']);
    Route::get('{id}/see', [ApplicationController::class, 'show']);
    Route::get('{id}/edit', [ApplicationController::class, 'edit']);
    Route::put('{id}', [ApplicationController::class, 'update']);
    Route::delete('{id}', [ApplicationController::class, 'destroy']);
});
Route::view('/internship', 'internship');
Route::view('/monitor', 'monitor');

Route::prefix('student')->group(function () {
    Route::get('/', [StudentController::class, 'index']);
    Route::get('/add', [StudentController::class, 'create']);
    Route::post('/', [StudentController::class, 'store']);
    Route::get('{id}/see', [StudentController::class, 'show']);
    Route::get('{id}/edit', [StudentController::class, 'edit']);
    Route::put('{id}', [StudentController::class, 'update']);
    Route::delete('{id}', [StudentController::class, 'destroy']);
});

Route::prefix('supervisor')->group(function () {
    Route::get('/', [SupervisorController::class, 'index']);
    Route::get('/add', [SupervisorController::class, 'create']);
    Route::post('/', [SupervisorController::class, 'store']);
    Route::get('{id}/see', [SupervisorController::class, 'show']);
    Route::get('{id}/edit', [SupervisorController::class, 'edit']);
    Route::put('{id}', [SupervisorController::class, 'update']);
    Route::delete('{id}', [SupervisorController::class, 'destroy']);
});

Route::prefix('institution')->group(function () {
    Route::get('/', [InstitutionController::class, 'index']);
    Route::get('/add', [InstitutionController::class, 'create']);
    Route::post('/', [InstitutionController::class, 'store']);
    Route::get('{id}/see', [InstitutionController::class, 'show']);
    Route::get('{id}/edit', [InstitutionController::class, 'edit']);
    Route::put('{id}', [InstitutionController::class, 'update']);
    Route::delete('{id}', [InstitutionController::class, 'destroy']);
});

Route::get('auth/login', [AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('auth/login', [AuthController::class, 'login'])->name('login.perform');

Route::get('auth/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('auth/register', [AuthController::class, 'handleRegister'])->name('register.handle');
