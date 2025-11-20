<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\DistrictController;
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

    Route::group(['prefix' => '/address'], function () {
        Route::get('/', [AddressController::class, 'list']);
        Route::post('/', [AddressController::class, 'add']);
        Route::post('/update', [AddressController::class, 'update']);
        Route::post('/delete', [AddressController::class, 'delete']);
        Route::post('/set-main', [AddressController::class, 'setMain']);
    });
});

Route::get('/province', [DistrictController::class, 'province']);
Route::get('/cities/{id}', [DistrictController::class, 'cities']);
