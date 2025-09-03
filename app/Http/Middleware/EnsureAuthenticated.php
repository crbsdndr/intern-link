<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('user_id')) {
            return redirect('/login');
        }

        $exists = \Illuminate\Support\Facades\DB::table('users')->where('id', session('user_id'))->exists();
        if (!$exists) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('status', 'Akun Anda tidak ditemukan. Kemungkinan telah dihapus.');
        }

        return $next($request);
    }
}
