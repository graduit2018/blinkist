<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class OAuthConnectorController extends Controller
{
    public function redirectToGoodreads()
    {
        $query = http_build_query([
            'client_id' => env('GOODREADS_CLIENT_ID', ''),
            'redirect_uri' => 'http://dev.blinkist.net/callback',
            'response_type' => 'code',
            'scope' => '',
        ]);

        return redirect('http://dev.goodreads.net/oauth/authorize?'.$query);
    }

    public function callbackFromGoodreads(Request $request) {
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

        dd(json_decode((string) $response->getBody(), true));
        // session()->put('token', json_decode((string) $response->getBody(), true));

        return redirect('/home');
    }
}
