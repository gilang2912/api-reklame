<?php

use Illuminate\Support\Facades\Route;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

Route::post('/login', 'AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('/dashboard', 'DashboardController@index');

    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@index');
        Route::post('/create', 'UserController@store');
        Route::get('/{id}', 'UserController@show');
        Route::put('/{id}', 'UserController@update');
        Route::delete('/{id}', 'UserController@destroy');
        Route::post('/change-pass', 'UserController@changePassword');
    });

    Route::group(['prefix' => 'roles'], function () {
        Route::get('/', 'UserController@role');
        Route::post('/create', '<UserControl></UserControl>ler@storeRole');
        Route::post('/grant', 'UserController@grantRole');
    });

    Route::group(['prefix' => 'objek-pajak'], function () {
        Route::get('/', 'ObjekPajakController@index');
        Route::post('/create', 'ObjekPajakController@store');
        Route::post('/detail', 'ObjekPajakController@show');
        Route::put('/update', 'ObjekPajakController@update');
        Route::post('/delete', 'ObjekPajakController@destroy');
        Route::get('/withpayment', 'ObjekPajakController@showWithPayment');
    });

    Route::get('/me', 'AuthController@me');

    Route::post('/paylist', 'PaymentController');

    Route::post('/logout', 'AuthController@logout');
});
