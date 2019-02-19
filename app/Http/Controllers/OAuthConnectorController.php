<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class OAuthConnectorController extends Controller
{
    public function redirectToGoodreads()
    {
        $query = http_build_query([
            'client_id' => env('GOODREADS_CLIENT_ID', ''),
            'redirect_uri' => 'http://dev.blinkist.net/callback',
            'response_type' => 'code',
            'scope' => 'get-isbn13 get-title',
        ]);

        return redirect('http://dev.goodreads.net/oauth/authorize?'.$query);
    }

    public function callbackFromGoodreads(Request $request) {
        if (isset($request->error)) {
            return redirect('/home');
        }

        $http = new Client();

        $response = $http->post('http://dev.goodreads.net/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => env('GOODREADS_CLIENT_ID', ''),
                'client_secret' => env('GOODREADS_CLIENT_SECRET', ''),
                'redirect_uri' => 'http://dev.blinkist.net/callback',
                'code' => $request->code,
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true);

        $user = Auth::user();
        $user->goodreads_access_token = $token['access_token'];
        $user->goodreads_refresh_token = $token['refresh_token'];
        $user->save();

        return redirect('/home');
    }

    public function removeTokenGoodreads() {
        $user = Auth::user();
        $user->goodreads_access_token = null;
        $user->goodreads_refresh_token = null;
        $user->save();

        return redirect('/home');
    }

    public function wantToRead() {
        $http = new Client();

        $response = $http->get('http://dev.goodreads.net/api/users/subscriptions', [
            'headers' => [
                'Authorization' => 'Bearer '.Auth::user()->goodreads_access_token
            ]
        ]);

        if (json_decode((string) $response->getBody(), true) == null) {
            return redirect()->route('redirect.to.goodreads');
        }

        $books = json_decode((string) $response->getBody(), true);

        return view('oauth.wanttoread', ['books' => $books]);
    }
}
