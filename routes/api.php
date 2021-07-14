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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/transaction/webhook', 'Frontend\TransactionController@store');
Route::post('/transaction/paypal', 'Frontend\TransactionController@payPal');
Route::get('/provinces', 'UserInfoController@getProvinces')->name('api.provinces');
Route::get('/province/{id}', 'UserInfoController@getFormAddress')->name('api.province');
Route::get('/amphures/{id}', 'UserInfoController@getAmphures')->name('api.amphures');
Route::get('/districts/{id}', 'UserInfoController@getDistricts')->name('api.districts');
Route::put('/cart', 'Frontend\CartController@updateCart')->name('api.cart');
Route::get('/cart', 'Frontend\CartController@getCartItems')->name('api.cart');
