<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsNotTemporary
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            if ($request->routeIs('password.first-change', 'password.first-change.update', 'logout')) {
                return $next($request);
            }

            return redirect()->route('password.first-change');
        }

        return $next($request);
    }
}
