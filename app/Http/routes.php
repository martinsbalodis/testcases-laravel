<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', ['as' => 'index', function () {
    Auth::check();
    return view('welcome');
}]);

Route::get('/login', ['as' => 'login', function () {

    $user = factory(\App\SeleniumTestUser::class)->create();
    Auth::login($user);

    return redirect('/');
}]);

Route::post('/', function () {
    return view('welcome');
});