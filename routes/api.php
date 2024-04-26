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
        Route::controller(UsersController::class)->group(function () {
            Route::get('/users','getUser');
            Route::get('/users/{id}', 'get');
            Route::post('/users/{id}', 'update');
            Route::post('/users', 'create');
            Route::delete('/users/{id}', 'destroy');
            Route::post('/user/import', 'import');
        });

        //CarsController
        Route::controller(CarsController::class)->group(function () {
            Route::get('/cars', 'getCar');
            Route::post('/cars', 'create');
            Route::get('/cars/{id}', 'get');
            Route::post('/cars/{id}', 'update');
            Route::delete('/cars/{id}', 'destroy');
            Route::post('car/import', 'import');
        });
       

        //DepartmentController
        Route::controller(DepartmentController::class)->group(function () {
            Route::get('/departments', 'index');
            Route::post('/departments', 'store');
            Route::post('/departments/{department}', 'update');
            Route::get('/departments/{department}', 'show');
            Route::post('/department/import', 'import');
            Route::delete('/departments/{department}', 'destroy');
        });


        //ScheduleController
        Route::controller(ScheduleController::class)->group(function () {
            Route::get('/schedules', 'index');
            Route::get('/schedules/{schedule}', 'show');
            Route::post('/schedules', 'add');
            Route::post('/schedules/{id}', 'update');
            Route::delete('/schedules/{id}', 'destroy');
            Route::post('/schedule/import', 'import');
        });
    });
    Route::middleware('qtvt')->group(function () {
        Route::get('/users', [UsersController::class, 'getUser']);
        //CarsController
        Route::controller(CarsController::class)->group(function () {
            Route::get('/cars', 'getCar');
            Route::post('/cars', 'create');
            Route::get('/cars/{id}', 'get');
            Route::post('/cars/{id}', 'update');
            Route::delete('/cars/{id}', 'destroy');
            Route::post('car/import', 'import');
        });
    });
    Route::middleware('qtct')->group(function () {
        //ScheduleController
        Route::get('/cars', [CarsController::class, 'getCar']);
        //ScheduleController
        Route::controller(ScheduleController::class)->group(function () {
            Route::get('/schedules', 'index');
            Route::get('/schedules/{schedule}','show');
            Route::post('/schedules', 'add');
            Route::post('/schedules/{id}', 'update');
            Route::delete('/schedules/{id}', 'destroy');
            Route::post('/schedule/import', 'import');
        });
    });
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);