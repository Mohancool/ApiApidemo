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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('home', 'CategoryController@index');
Route::post('login', 'UserController@login');
Route::post('register', 'UserController@register');
Route::post('subcategories', 'SubCategoryController@index');
Route::post('childcategories', 'ChildCategoryController@index');
Route::post('recommended', 'ProductController@recommendedData');
Route::post('additional', 'ProductController@additionalItems');
Route::post('category-products', 'ProductController@productByCategory');
Route::post('add-cart', 'CartController@addToCart');
Route::post('remove-cart', 'CartController@removeCart');
Route::post('cart-list', 'CartController@cartList');
Route::post('add-wishlist', 'WishlistController@addToWishlist');
Route::post('get-wishlist', 'WishlistController@getWishlist');
Route::post('otp-verify', 'UserController@verifyOtp');
Route::post('update-profile', 'UserController@updateProfile');
Route::post('get-coupans', 'CouponController@index');
Route::post('get-orders', 'OrderController@index');





