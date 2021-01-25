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

Route::get('/stores', 'Stores@index')->middleware('auth:api');
Route::post('/stores', 'Stores@search')->middleware('auth:api');
Route::put('/stores', 'Stores@create')->middleware('auth:api');
Route::get('/stores/{id}', 'Stores@read')->middleware('auth:api');
Route::put('/stores/{id}', 'Stores@update')->middleware('auth:api');
Route::delete('/stores/{id}', 'Stores@destroy')->middleware('auth:api');

Route::get('/categories', 'Categories@index')->middleware('auth:api');
Route::post('/categories', 'Categories@search')->middleware('auth:api');
Route::put('/categories', 'Categories@create')->middleware('auth:api');
Route::get('/categories/{id}', 'Categories@read')->middleware('auth:api');
Route::put('/categories/{id}', 'Categories@update')->middleware('auth:api');
Route::delete('/categories/{id}', 'Categories@destroy')->middleware('auth:api');

Route::get('/products', 'Products@index')->middleware('auth:api');
Route::post('/products', 'Products@search')->middleware('auth:api');
Route::put('/products', 'Products@create')->middleware('auth:api');
Route::get('/products/{id}', 'Products@read')->middleware('auth:api');
Route::put('/products/{id}', 'Products@update')->middleware('auth:api');
Route::delete('/products/{id}', 'Products@destroy')->middleware('auth:api');
