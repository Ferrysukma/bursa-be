<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\ApiTokenMiddleware;

Route::group(['prefix' => '/auth'], function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/signin', [AuthController::class, 'signin']);
    Route::get('/signout', [AuthController::class, 'signout'])->middleware(ApiTokenMiddleware::class);
});

Route::middleware(ApiTokenMiddleware::class)->group(function () {
    Route::group(['prefix' => '/user'], function () {
        Route::get('/', [UserController::class, 'user']);
        Route::post('/update', [UserController::class, 'userUpdate']);
    });
});

