<?php

use Illuminate\Http\Request;
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

Route::post('/login', 'Users@login');

Route::get('/stores/list', 'Stores@index')->middleware('auth:api');
Route::post('/stores', 'Stores@store')->middleware('auth:api');
Route::get('/stores/{id}', 'Stores@show')->middleware('auth:api');
Route::put('/stores/{id}', 'Stores@update')->middleware('auth:api');
Route::delete('/stores/{id}', 'Stores@destroy')->middleware('auth:api');

Route::get('/user/{id}', function ($id) {
    return response(json_encode($id), 200);
})->middleware('auth:api');
