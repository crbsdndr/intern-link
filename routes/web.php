<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth/login', [AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('auth/login', [AuthController::class, 'login'])->name('login.perform');

Route::get('auth/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('auth/register', [AuthController::class, 'handleRegister'])->name('register.handle');
