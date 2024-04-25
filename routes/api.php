<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ScheduleController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {
        //UsersController
        Route::get('/users', [UsersController::class, 'getUser']);
        Route::get('/users/{id}', [UsersController::class, 'get']);
        Route::post('/users/{id}', [UsersController::class, 'update']);
        Route::post('/users', [UsersController::class, 'create']);
        Route::delete('/users/{id}', [UsersController::class, 'destroy']);
        Route::post('/user/import', [UsersController::class, 'import']);

        //CarsController
        Route::get('/cars', [CarsController::class, 'getCar']);
        Route::post('/cars', [CarsController::class, 'create']);
        Route::get('/cars/{id}', [CarsController::class, 'get']);
        Route::post('/cars/{id}', [CarsController::class, 'update']);
        Route::delete('/cars/{id}', [CarsController::class, 'destroy']);
        Route::post('car/import', [CarsController::class, 'import']);

        //DepartmentController
        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::post('/departments', [DepartmentController::class, 'store']);
        Route::post('/departments/{department}', [DepartmentController::class, 'update']);
        Route::get('/departments/{department}', [DepartmentController::class, 'show']);
        Route::post('/department/import', [DepartmentController::class, 'import']);
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');

        //ScheduleController
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
        Route::post('/schedules', [ScheduleController::class, 'add']);
        Route::post('/schedules/{id}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
        Route::post('/schedule/import', [ScheduleController::class, 'import']);
    });
    Route::middleware('qtvt')->group(function () {
        Route::get('/users', [UsersController::class, 'getUser']);
        Route::get('/cars', [CarsController::class, 'getCar']);
        Route::post('/cars', [CarsController::class, 'create']);
        Route::get('/cars/{id}', [CarsController::class, 'get']);
        Route::post('/cars/{id}', [CarsController::class, 'update']);
        Route::delete('/cars/{id}', [CarsController::class, 'destroy']);
        Route::post('car/import', [CarsController::class, 'import']);
    });
    Route::middleware('qtct')->group(function () {
        //ScheduleController
        Route::get('/cars', [CarsController::class, 'getCar']);
        Route::get('/departments', [DepartmentController::class, 'index']);
        Route::get('/schedules', [ScheduleController::class, 'index']);
        Route::get('/schedules/{schedule}', [ScheduleController::class, 'show']);
        Route::post('/schedules', [ScheduleController::class, 'add']);
        Route::post('/schedules/{id}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy']);
        Route::post('/schedule/import', [ScheduleController::class, 'import']);
    });
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);