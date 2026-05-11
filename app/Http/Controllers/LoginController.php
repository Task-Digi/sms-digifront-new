<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    public function redirectToProvider(Request $request)
    {
        if (Session::get('login_status') === true) {
            return redirect()->route('home');
        }

        $method = $request->query('method') === 'email' ? 'email' : 'sms';

        $state = Str::random(40);
        Session::put('secureivs_state', $state);
        Session::put('secureivs_method', $method);

        $clientId    = config('settings.secureivs_client_id');
        $redirectUri = urlencode(route('auth.callback'));
        $authUrl     = "https://customer-api.secureivs.eu/?client_id={$clientId}&login_method={$method}&redirect_uri={$redirectUri}&return_code={$state}";

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
        $method        = Session::pull('secureivs_method', 'sms');

        if (!$returnCode || !$expectedState || !hash_equals($expectedState, $returnCode)) {
            return redirect()->route('admin.login')->with('error', 'Invalid session. Please try again.');
        }

        $status     = $request->query('status');
        $identifier = $request->query('user');

        if ($status !== 'true' || !$identifier) {
            return redirect()->route('admin.login')->with('error', 'Authentication failed. Please try again.');
        }

        $column = $method === 'email' ? 'email' : 'mobile';
        $user   = User::where($column, $identifier)->where('is_active', true)->first();

        if (!$user) {
            return redirect()->route('admin.login')->with('error', 'User not authorized.');
        }

        Session::put('login_status', true);
        Session::put('user', [
            'id'        => $user->id,
            'name'      => $user->name,
            'mobile'    => $user->mobile,
            'sender_id' => $user->sender_id,
            'is_admin'  => (bool) $user->is_admin,
        ]);

        return redirect()->route($user->is_admin ? 'statistics' : 'home');
    }

    public function logout()
    {
        Session::flush();
        return redirect()->route('admin.login');
    }
}
