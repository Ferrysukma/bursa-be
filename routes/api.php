<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
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

    Route::group(['prefix' => '/store'], function () {
        Route::post('/', [StoreController::class, 'open']);
        Route::post('/update', [StoreController::class, 'update']);
    });

    Route::group(['prefix' => '/products'], function () {
        Route::get('/list-by-store', [ProductController::class, 'listByStore']);
        Route::post('/add', [ProductController::class, 'add']);
        Route::post('/update', [ProductController::class, 'update']);
        Route::post('/delete', [ProductController::class, 'delete']);
    });

    Route::group(['prefix' => '/cart'], function () {
        Route::get('/', [CartController::class, 'list']);
        Route::post('/', [CartController::class, 'add']);
        Route::post('/update', [CartController::class, 'update']);
        Route::post('/delete', [CartController::class, 'delete']);
    });
});

Route::group(['prefix' => '/category'], function () {
    Route::get('/', [CategoryController::class, 'list']);
    Route::post('/', [CategoryController::class, 'add']);
    Route::post('/update', [CategoryController::class, 'update']);
});

Route::group(['prefix' => '/products'], function () {
    Route::get('/list-all', [ProductController::class, 'listAll']);
    Route::get('/{slug}', [ProductController::class, 'detail']);
});

Route::get('/province', [DistrictController::class, 'province']);
Route::get('/cities/{id}', [DistrictController::class, 'cities']);
