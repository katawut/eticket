<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('login/{provider}', 'Auth\LoginController@redirectToProvider')->middleware('setlocale');
Route::get('login/{provider}/callback', 'Auth\LoginController@handleProviderCallback')->middleware('setlocale');

Route::name('frontend.')->namespace('Frontend')->middleware('setlocale')->group(function () {
  Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'th'])) :
      session()->forget('site_lang');
      session()->put('site_lang', $locale);
    endif;
    return redirect()->back();
  })->name('lang');

  // MAIN PAGE
  Route::resource('/', 'TicketController');

  // WEB CLEAR CACHE
  Route::get('/clearCache', function () {
    Cache::flush();
    return 'Cache Clear';
  });

  // WEB CLEAR SESSION
  Route::get('/clearSession', function () {
    session()->flush();
    return 'Clear All Session';
  });

  Route::get('/terms', function () {
    return view('frontend.terms');
  })->name('terms');

  Route::get('/register_terms', function () {
    return view('frontend.reg_terms');
  })->name('reg_terms');

  Route::get('/policy', function () {
    return view('frontend.policy');
  })->name('policy');

  Route::get('/policy_refund', function () {
    return view('frontend.policy_refund');
  })->name('policy_refund');

  Route::get('/checklogin', function () {
    if (Auth::check()) :
      $status['login'] = TRUE;
    else :
      $status['login'] = FALSE;
    endif;
    return response()->json($status);
  })->name('checklogin');

  Route::get('/checkout/paypal/{orderId}', 'CheckoutController@paypal')->middleware('verified')->name('checkout.paypal');
  Route::get('/checkout/thankyou', 'CheckoutController@thankyou')->middleware('verified')->name('thankyou');
  Route::get('/checkout/{orderId}/complete', 'CheckoutController@complete')->middleware('verified')->name('checkout.complete');
  Route::get('/checkout/confirm', 'CheckoutController@confirm')->middleware('verified')->name('checkout.confirm');
  Route::get('/checkout/cancel_order', 'CheckoutController@destroy')->middleware('verified')->name('checkout.cancel');
  Route::resource('/cart', 'CartController');
  Route::resource('/products', 'TicketController');
  Route::resource('/checkout', 'CheckoutController')->middleware('verified');
  Route::resource('/orders', 'OrderHistoryController')->middleware('verified');

  // Frontend Profile
  // Route::resource('/userprofile', 'UserProfileController');
});


Auth::routes(['verify' => true]);
