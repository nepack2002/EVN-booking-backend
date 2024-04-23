<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarsController;
use App\Http\Controllers\ForgotPasswordController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('car/import', [CarsController::class, 'index1']);
// Route::get('/', [UsersController::class, 'getUser']);
// Route::post('car/import', [CarsController::class, 'import']);Route::get('/reset-password', [ForgotPasswordController::class, 'viewResetpassword'])->name('reset-password');



Route::post('/reset', [ForgotPasswordController::class, 'resetpassword'])->name('password.reset');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'viewResetpassword'])->name('reset-password');