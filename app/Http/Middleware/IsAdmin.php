<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('user')['is_admin'] ?? false) {
            return $next($request);
        }

        return redirect()->route('home');
    }
}
