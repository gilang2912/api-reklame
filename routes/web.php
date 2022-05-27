<?php

use Illuminate\Support\Facades\Route;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

Route::post('/login', 'AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', 'UserController@index');
        Route::post('/create', 'UserController@store');
        Route::get('/{id}', 'UserController@show');
        Route::put('/{id}', 'UserController@update');
        Route::delete('/{id}', 'UserController@destroy');
    });

    Route::group(['prefix' => 'objek-pajak'], function () {
        Route::get('/', 'ObjekPajakController@index');
        Route::post('/create', 'ObjekPajakController@store');
    });

    Route::post('/paylist', 'PaymentController');
});
