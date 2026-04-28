<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function index()
    {
        if (Session::get('login_status') === true) {
            return redirect()->route('home');
        }

        return view('admin.login');
    }

    public function redirectToProvider()
    {
        if (Session::get('login_status') === true) {
            return redirect()->route('home');
        }

        $state = Str::random(40);
        Session::put('secureivs_state', $state);

        $clientId    = config('settings.secureivs_client_id');
        $redirectUri = urlencode(route('auth.callback'));
        $authUrl     = "https://customer-api.secureivs.eu/?client_id={$clientId}&login_method=sms&redirect_uri={$redirectUri}&return_code={$state}";

        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        $referer = $request->headers->get('referer', '');
        if (!app()->environment('local') && !str_contains($referer, 'secureivs.eu')) {
            return redirect()->route('admin.login')->with('error', 'Invalid request origin.');
        }

        $returnCode    = $request->query('return_code');
        $expectedState = Session::pull('secureivs_state');

        if (!$returnCode || !$expectedState || !hash_equals($expectedState, $returnCode)) {
            return redirect()->route('admin.login')->with('error', 'Invalid session. Please try again.');
        }

        $status = $request->query('status');
        $mobile = $request->query('user');

        if ($status !== 'true' || !$mobile) {
            return redirect()->route('admin.login')->with('error', 'Authentication failed. Please try again.');
        }

        $users = config('settings.users');

        if (!isset($users[$mobile])) {
            return redirect()->route('admin.login')->with('error', 'User not authorized.');
        }

        Session::put('login_status', true);
        Session::put('user', $users[$mobile]);

        return redirect()->route('home');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('admin.login');
    }
}
