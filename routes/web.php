<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;

Route::view('/', 'home');
Route::view('/dashboard', 'home');
Route::view('/supervisor', 'supervisor');
Route::view('/application', 'application');
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

Route::get('auth/login', [AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('auth/login', [AuthController::class, 'login'])->name('login.perform');

Route::get('auth/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('auth/register', [AuthController::class, 'handleRegister'])->name('register.handle');
