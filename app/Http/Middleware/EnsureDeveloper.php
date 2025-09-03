<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeveloper
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') !== 'developer') {
            abort(401, 'Akses ditolak.');
        }
        return $next($request);
    }
}
