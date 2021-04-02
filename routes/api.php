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
Route::post('/logout', 'Users@logout');

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

Route::get('/prices', 'Prices@index')->middleware('auth:api');
Route::post('/prices', 'Prices@search')->middleware('auth:api');
Route::put('/prices', 'Prices@create')->middleware('auth:api');
Route::patch('/prices', 'Prices@buy')->middleware('auth:api');
Route::get('/prices/{id}', 'Prices@read')->middleware('auth:api');
Route::put('/prices/{id}', 'Prices@update')->middleware('auth:api');
Route::delete('/prices/{id}', 'Prices@destroy')->middleware('auth:api');

Route::get('/shopping-list', 'ShoppingList@index')->middleware('auth:api');
Route::put('/shopping-list', 'ShoppingList@create')->middleware('auth:api');

Route::put('/recognize-invoice', 'Recognize@invoice')->middleware('auth:api');
Route::put('/recognize-prices', 'Recognize@getPrices')->middleware('auth:api');

Route::post('/statistics', 'Statistics@search')->middleware('auth:api');
