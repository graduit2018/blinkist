<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class OAuthConnectorController extends Controller
{
    public function redirectToGoodreads()
    {
        $query = http_build_query([
            'client_id' => env('GOODREADS_CLIENT_ID', ''),
            'redirect_uri' => 'http://blinkist.net/callback',
            'response_type' => 'code',
            'scope' => 'get-isbn13 get-title',
        ]);

        return redirect('http://oauth.goodreads.net/oauth/authorize?'.$query);
    }

    public function callbackFromGoodreads(Request $request) {
        if (isset($request->error)) {
            return redirect('/home');
        }

        $http = new Client();

        $response = $http->post('http://oauth.goodreads.net/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => env('GOODREADS_CLIENT_ID', ''),
                'client_secret' => env('GOODREADS_CLIENT_SECRET', ''),
                'redirect_uri' => 'http://blinkist.net/callback',
                'code' => $request->code,
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true);

        $user = Auth::user();
        $user->goodreads_access_token = $token['access_token'];
        $user->goodreads_refresh_token = $token['refresh_token'];
        $user->expires_at = Carbon::now()->subMinute()->addSeconds($token['expires_in'])->timestamp;
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

        if (Auth::user()->goodreads_access_token == null) {
            return view('oauth.wanttoread');
        }

        if (Auth::user()->tokenExpired()) {

            $response = $http->post('http://oauth.goodreads.net/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => Auth::user()->goodreads_refresh_token,
                    'client_id' => env('GOODREADS_CLIENT_ID', ''),
                    'client_secret' => env('GOODREADS_CLIENT_SECRET', ''),
                    'scope' => '',
                ],
            ]);

            $token = json_decode((string) $response->getBody(), true);

            $user = Auth::user();
            $user->goodreads_access_token = $token['access_token'];
            $user->goodreads_refresh_token = $token['refresh_token'];
            $user->expires_at = Carbon::now()->subMinute()->addSeconds($token['expires_in'])->timestamp;
            $user->save();
        }

        $response = $http->get('http://goodreads.net/api/users/subscriptions', [
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
