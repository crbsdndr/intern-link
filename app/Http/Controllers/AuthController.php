<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show step 1 of the registration form.
     */
    public function showStep1(Request $request)
    {
        $data = $request->session()->get('register.step1', []);
        return view('auth.register-step1', ['data' => $data]);
    }

    /**
     * Handle step 1 submission and store basic data in the session.
     */
    public function handleStep1(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
        ]);

        $data['password'] = Hash::make($data['password']);

        $request->session()->put('register.step1', $data);

        return redirect()->route('register.step2');
    }

    /**
     * Show step 2 of the registration form.
     */
    public function showStep2(Request $request)
    {
        if (! $request->session()->has('register.step1')) {
            return redirect()->route('register');
        }

        $data = $request->session()->get('register.step2', []);
        return view('auth.register-step2', ['data' => $data]);
    }

    /**
     * Handle step 2 submission and store role-specific data in the session.
     */
    public function handleStep2(Request $request)
    {
        if (! $request->session()->has('register.step1')) {
            return redirect()->route('register');
        }

        $role = $request->input('role');
        $rules = ['role' => 'required|in:student,supervisor'];

        if ($role === 'student') {
            $rules += [
                'national_number' => 'required|string',
                'national_student_number' => 'required|string',
                'major' => 'required|string',
                'batch' => 'required|numeric',
                'photo' => 'required|image',
            ];
        } else {
            $rules += [
                'supervisor_number' => 'required|string',
                'department' => 'required|string',
                'photo' => 'required|image',
            ];
        }

        $data = $request->validate($rules);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos');
        }

        $request->session()->put('register.step2', $data);

        return redirect()->route('register.step3');
    }

    /**
     * Show confirmation step.
     */
    public function showStep3(Request $request)
    {
        if (! $request->session()->has('register.step1') || ! $request->session()->has('register.step2')) {
            return redirect()->route('register');
        }

        return view('auth.register-step3');
    }

    /**
     * Finalize registration using data from the session.
     */
    public function handleStep3(Request $request)
    {
        $step1 = $request->session()->get('register.step1');
        $step2 = $request->session()->get('register.step2');

        if (! $step1 || ! $step2) {
            return redirect()->route('register');
        }

        $user = User::create([
            'name' => $step1['name'],
            'email' => $step1['email'],
            'password' => $step1['password'],
            'phone' => $step1['phone'] ?? null,
            'role' => $step2['role'],
        ]);

        if ($step2['role'] === 'student') {
            Student::create([
                'user_id' => $user->id,
                'student_number' => $step2['national_student_number'],
                'national_sn' => $step2['national_number'],
                'major' => $step2['major'],
                'batch' => $step2['batch'],
                'photo' => $step2['photo'],
            ]);
        } else {
            Supervisor::create([
                'user_id' => $user->id,
                'supervisor_number' => $step2['supervisor_number'],
                'department' => $step2['department'],
                'photo' => $step2['photo'],
            ]);
        }

        $request->session()->forget('register.step1');
        $request->session()->forget('register.step2');

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
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
            return back()->withErrors(['email' => 'Invalid credentials'])->withInput();
        }

        session([
            'user_id' => $user->id,
            'role'    => $user->role,
        ]);

        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Logged out');
    }
}
