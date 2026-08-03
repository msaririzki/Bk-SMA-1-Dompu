<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::Student && $request->user()?->must_change_password && ! $request->routeIs('password.*', 'logout')) {
            return redirect()->route('password.edit')->with('warning', 'Ganti kata sandi awal sebelum melanjutkan.');
        }

        return $next($request);
    }
}
