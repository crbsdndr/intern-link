<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\MonitoringLogController;
use App\Http\Controllers\MetaController;

Route::view('/introduction', 'introduction');

Route::match(['get', 'post'], '/login', [AuthController::class, 'login'])->name('login');
Route::match(['get', 'post'], '/signup', [AuthController::class, 'signup'])->name('signup');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.session')->group(function () {
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

    Route::prefix('internship')->group(function () {
        Route::get('/', [InternshipController::class, 'index']);
        Route::get('/add', [InternshipController::class, 'create']);
        Route::post('/', [InternshipController::class, 'store']);
        Route::get('{id}/see', [InternshipController::class, 'show']);
        Route::get('{id}/edit', [InternshipController::class, 'edit']);
        Route::put('{id}', [InternshipController::class, 'update']);
        Route::delete('{id}', [InternshipController::class, 'destroy']);
    });

    Route::prefix('monitoring')->group(function () {
        Route::get('/', [MonitoringLogController::class, 'index']);
        Route::get('/add', [MonitoringLogController::class, 'create']);
        Route::post('/', [MonitoringLogController::class, 'store']);
        Route::get('{id}/see', [MonitoringLogController::class, 'show']);
        Route::get('{id}/edit', [MonitoringLogController::class, 'edit']);
        Route::put('{id}', [MonitoringLogController::class, 'update']);
        Route::delete('{id}', [MonitoringLogController::class, 'destroy']);
    });

    Route::prefix('meta')->group(function () {
        Route::get('/monitor-types', [MetaController::class, 'monitorTypes']);
        Route::get('/supervisors', [MetaController::class, 'supervisors']);
    });

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
        Route::middleware('role:admin,developer')->group(function () {
            Route::get('/add', [SupervisorController::class, 'create']);
            Route::post('/', [SupervisorController::class, 'store']);
        });
        Route::middleware('supervisor.self')->group(function () {
            Route::get('{id}/see', [SupervisorController::class, 'show']);
            Route::get('{id}/edit', [SupervisorController::class, 'edit']);
            Route::put('{id}', [SupervisorController::class, 'update']);
            Route::delete('{id}', [SupervisorController::class, 'destroy']);
        });
    });

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/add', [AdminUserController::class, 'create']);
        Route::post('/', [AdminUserController::class, 'store']);
        Route::middleware('admin.self')->group(function () {
            Route::get('{id}/see', [AdminUserController::class, 'show']);
            Route::get('{id}/edit', [AdminUserController::class, 'edit']);
            Route::put('{id}', [AdminUserController::class, 'update']);
            Route::delete('{id}', [AdminUserController::class, 'destroy']);
        });
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
});
