<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Handle user registration.
     */
    public function signup(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'role' => 'required|string',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        return response()->json(['message' => 'Signup successful']);
    }

    /**
     * Handle user login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return response()->json(['message' => 'Login successful']);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out']);
    }
}
