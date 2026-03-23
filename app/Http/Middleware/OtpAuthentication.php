<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OtpAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('login_status') === true) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
