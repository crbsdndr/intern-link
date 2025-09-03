<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloperSelf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') === 'developer') {
            $routeId = (int) $request->route('id');
            if ($routeId !== (int) session('user_id')) {
                abort(401);
            }
        }
        return $next($request);
    }
}
