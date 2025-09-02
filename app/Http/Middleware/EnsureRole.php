<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!session()->has('role') || !in_array(session('role'), $roles)) {
            abort(403);
        }

        return $next($request);
    }
}
