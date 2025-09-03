<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array(session('role'), ['admin', 'developer'])) {
            abort(401);
        }
        return $next($request);
    }
}
