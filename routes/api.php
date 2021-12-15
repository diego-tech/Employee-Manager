<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['api_auth', 'check_token_expired', 'check_user_profile'])->group(function () {
    Route::put('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login'])->withoutMiddleware(['api_auth', 'check_token_expired', 'check_user_profile']);
    Route::get('/employee_list', [UserController::class, 'employee_list']);
    Route::get('/employee_detail', [UserController::class, 'employee_detail']);
    Route::get('/see_profile', [UserController::class, 'see_profile'])->withoutMiddleware('check_user_profile');
    Route::post('/retrieve_password', [UserController::class, 'retrieve_password'])->withoutMiddleware(['api_auth', 'check_token_expired', 'check_user_profile']);
    Route::post('/modify_data', [UserController::class, 'modify_data']);
    Route::post('/modify_password', [UserController::class, 'modify_password'])->withoutMiddleware('check_user_profile');
});
