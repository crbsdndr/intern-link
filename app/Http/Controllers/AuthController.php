<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display the second registration step for role selection.
     */
    public function showStep2(Request $request)
    {
        $data = $request->session()->get('register.step2', []);

        return view('auth.register-step2', ['data' => $data]);
    }

    /**
     * Store the role selection and related fields in session.
     */
    public function storeStep2(Request $request)
    {
        $rules = ['role' => 'required|in:student,supervisor'];

        if ($request->input('role') === 'student') {
            $rules = array_merge($rules, [
                'national_number' => 'required|string',
                'national_student_number' => 'required|string',
                'major' => 'required|string',
                'batch' => 'required|numeric',
                'photo' => 'required|image',
            ]);
        } elseif ($request->input('role') === 'supervisor') {
            $rules = array_merge($rules, [
                'supervisor_number' => 'required|string',
                'department' => 'required|string',
                'photo' => 'required|image',
            ]);
        }

        $data = $request->validate($rules);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('tmp');
        }

        $request->session()->put('register.step2', $data);

        return redirect()->route('register.step3');
    }
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
