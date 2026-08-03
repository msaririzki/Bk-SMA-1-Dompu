<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.edit')->with('warning', 'Ganti kata sandi/PIN awal sebelum melanjutkan.');
        }

        return $next($request);
    }
}
