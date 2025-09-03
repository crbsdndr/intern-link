<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisorSelf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') === 'supervisor') {
            $supervisorId = DB::table('supervisors')->where('user_id', session('user_id'))->value('id');
            $routeId = (int) $request->route('id');
            if ($routeId !== $supervisorId) {
                abort(401);
            }
        }
        return $next($request);
    }
}
