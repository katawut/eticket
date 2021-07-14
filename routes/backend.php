<?php

//use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Backend Routes
|--------------------------------------------------------------------------
|
|
|
*/

Route::get('/', function () {
  return redirect()->route("backend.profile.index");
});


// Backend Authen
Route::name('backend.auth.')
  ->namespace('Backend\Auth')
  ->group(function () {
    Route::get('/login', 'LoginController@showLoginForm')->name('login.form');
    Route::post('/login', 'LoginController@login')->name('login');
    Route::post('/logout', 'LoginController@logout')->name('logout');
  });


Route::name('backend.')->namespace('Backend')->middleware(['backend.auth', 'user.active'])->group(function () {
  Route::get('/home', function () {
    return redirect()->route("backend.profile.index");
  });


  // Profile
  Route::resource('/profile', 'ProfileController');


  // Role
  Route::name('role.')
    ->prefix('/role')
    ->group(function () {
      Route::get('/search', 'RoleController@search')->name('search');
    });
  Route::resource('/role', 'RoleController');


  // User
  Route::name('user.')->prefix('/user')->group(function () {
    Route::get('/search', 'UserController@search')->name('search');
  });
  Route::resource('/user', 'UserController');


  // Customer
  Route::resource('/customer', 'CustomerController');


  // Order
  Route::get('/order/export', 'OrderController@export')->name('order.export');
  Route::get('/order/search', 'OrderController@search')->name('order.search');
  Route::resource('/order', 'OrderController');


  // Ticket
  Route::resource('/ticket', 'TicketController');


  // Stock
  Route::resource('/stock', 'StocksController');


  // PLugin : CKEditor
  Route::name('ckeditor.')->prefix('/ckeditor')->group(function () {
    Route::post('/upload', 'CkeditorController@upload')->name('upload');
  });
  Route::get('ckeditor', 'CkeditorController@index');

  // PLugin : Dropzone
  Route::name('dropzone.')->prefix('/dropzone')->group(function () {
    Route::post('/upload', 'DropzoneController@upload')->name('upload');
  });
});
