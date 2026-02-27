<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([], function () {
    //LOGIN
    Route::post('/login', [AuthController::class, 'login']);

    //CREATE USER AND ACCOUNT
    Route::post('/user', [UserController::class, 'create']);
});


Route::middleware('auth:sanctum')->group(function () {
    //LOGOUT
    Route::post('/logout', [AuthController::class, 'logout']);

    //USERS
    Route::get('/user', [UserController::class, 'show']);

    //TRANSACTIONS
    Route::post('/transfer', [TransactionController::class, 'transfer']);
    Route::post('/deposit', [TransactionController::class, 'deposit']);
    Route::post('/reversal', [TransactionController::class, 'reversal']);
    Route::get('/transactions', [TransactionController::class, 'getTransactions']);
});
