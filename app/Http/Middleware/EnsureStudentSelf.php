<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentSelf
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('role') === 'student') {
            $studentId = DB::table('students')->where('user_id', session('user_id'))->value('id');
            $routeId = (int) $request->route('id');
            if ($routeId !== (int) $studentId) {
                abort(401);
            }
        }
        return $next($request);
    }
}
