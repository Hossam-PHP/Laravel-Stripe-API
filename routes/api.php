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


Route::group(['namespace'=>'App\Http\Controllers\API', 'prefix' => 'auth'], function () {

    Route::post('login', 'AuthController@login')->name('login');
    Route::post('register', 'AuthController@register')->name('register');

    Route::get(
	    '/',
	    'ProductController@getIndex'
	)->name('product.index');

	Route::get(
	    '/shopping-cart',
	    'ProductController@getCart'
	)->name('product.shoppingcart');

	Route::get(
	    '/add-to-cart/{id}',
	    'ProductController@getAddToCart'
	)->name('product.addtocart');

	Route::get(
	    '/reduce-by-one/{id}',
	    'ProductController@getReduceByOne'
	)->name('product.reducebyone');

	Route::get(
	    '/remove-item/{id}',
	    'ProductController@getRemoveItem'
	)->name('product.removeitem');

    Route::fallback(function(){
        return response()->json(['message' => 'Not Found!'], 404);
    });
});


Route::group(['middleware' => ['auth:api'], 'namespace'=>'App\Http\Controllers\API', 'prefix' => 'user'], function () {
     
    Route::post('logout', 'AuthController@logout');

    Route::post(
        '/checkout',
        'ProductController@postCheckout'
    )->name('checkout');

    Route::get(
        '/checkout',
        'ProductController@getCheckout'
    )->name('checkout');
    
    Route::fallback(function(){
        return response()->json(['message' => 'Not Found!'], 404);
    });
});