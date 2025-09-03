<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSelf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') === 'admin') {
            $routeId = (int) $request->route('id');
            if ($routeId !== (int) session('user_id')) {
                abort(401);
            }
        } elseif (session('role') === 'developer') {
            // Developers can manage any admin account
        } else {
            abort(401);
        }
        return $next($request);
    }
}
