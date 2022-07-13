<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return redirect()->away(env('APP_PANEL_URL'));
});

Route::get('/proxy.html', function () {
	return view('cors_proxy');
});

Route::group(['prefix' => 'maintenance'], function() {
	Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
	Route::get('zenvia_curl', 'Integration\SmsConversationController@debug_zenvia');
});
