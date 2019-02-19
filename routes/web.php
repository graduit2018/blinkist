<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/redirect-to-goodreads', 'OAuthConnectorController@redirectToGoodreads')->name('redirect.to.goodreads');
    Route::get('/callback', 'OAuthConnectorController@callbackFromGoodreads');

    Route::get('/wanttoread', function () {
        $http = new GuzzleHttp\Client;

        $response = $http->get('http://dev.goodreads.net/api/users/subscriptions', [
            'headers' => [
                'Authorization' => 'Bearer '.session()->get('token.access_token')
            ]
        ]);

        return json_decode((string) $response->getBody(), true);
    });
});
