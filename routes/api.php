<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\UserPageController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->group(function () {
        //UsersController
        Route::controller(UsersController::class)->group(function () {
            Route::get('/users', 'getUser');
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
            Route::post('/coordinates', [ScheduleController::class, 'storeCoordinates']);
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
        Route::controller(ScheduleController::class)->group(function () {
            Route::get('/schedules', 'index');
            Route::get('/schedules/{schedule}', 'show');
            Route::post('/schedules', 'add');
            Route::post('/schedules/{id}', 'update');
            Route::delete('/schedules/{id}', 'destroy');
            Route::post('/schedule/import', 'import');
            Route::post('/coordinates', [ScheduleController::class, 'storeCoordinates']);
        });
    });
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgotPassword', [ForgotPasswordController::class, 'forgotPassword']);

Route::get('users/{userId}/schedules', [UserPageController::class, 'getUserSchedules']);
Route::get('users/{userId}/schedulesDate', [UserPageController::class, 'getSchedulesGroupedByDate']);
Route::get('users/ScheduleLocation/{id}',[UserPageController::class, 'getLocation']);
Route::get('users/schedule/{schedule}', [UserPageController::class, 'getDetail']);
Route::get('users/notification/{id}', [UserPageController::class, 'getNotification']);
Route::get('users/notificationUnRead/{id}', [UserPageController::class, 'getNotificationUnRead']);
Route::put('/notifications/{notification}/mark-as-read', [UserPageController::class, 'markAsRead']);
Route::post('/cars/update/{id}', [UserPageController::class, 'updateRun']);
Route::post('/schedule/location/{id}', [UserPageController::class, 'sendLocation']);
Route::post('/schedule/sendLastLocation/{id}', [UserPageController::class, 'updateLastLocation']);
Route::get('/schedule/car/{id}', [UserPageController::class, 'getCarOfUser']);