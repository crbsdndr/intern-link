<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = [
        'name',
        'email',
        'phone',
        'student_number',
        'national_sn',
        'major',
        'class',
        'batch',
    ];

    private const SELECT_COLUMNS = [
        'id',
        'user_id',
        'name',
        'email',
        'email_verified_at',
        'phone',
        'student_number',
        'national_sn',
        'major',
        'class',
        'batch',
        'notes',
        'photo',
        'created_at',
        'updated_at',
    ];

    public function index(Request $request)
    {
        $query = DB::table('student_details_view')
            ->select(self::SELECT_COLUMNS);
        if (session('role') === 'student') {
            $query->where('id', $this->currentStudentId());
        }

        $filters = [];

        if ($name = trim((string) $request->query('name', ''))) {
            $query->where('name', 'like', '%' . $name . '%');
            $filters['name'] = 'Name: ' . $name;
        }

        if ($email = trim((string) $request->query('email', ''))) {
            $query->where('email', 'like', '%' . $email . '%');
            $filters['email'] = 'Email: ' . $email;
        }

        if ($phone = trim((string) $request->query('phone', ''))) {
            $query->where('phone', 'like', '%' . $phone . '%');
            $filters['phone'] = 'Phone: ' . $phone;
        }

        if (($isEmailVerified = $request->query('is_email_verified')) !== null) {
            if ($isEmailVerified === 'true') {
                $query->whereNotNull('email_verified_at');
                $filters['is_email_verified'] = 'Email Verified: True';
            } elseif ($isEmailVerified === 'false') {
                $query->whereNull('email_verified_at');
                $filters['is_email_verified'] = 'Email Verified: False';
            }
        }

        if ($emailVerifiedAt = $request->query('email_verified_at')) {
            $query->whereDate('email_verified_at', $emailVerifiedAt);
            $filters['email_verified_at'] = 'Email Verified At: ' . $emailVerifiedAt;
        }

        if ($studentNumber = trim((string) $request->query('student_number', ''))) {
            $query->where('student_number', 'like', '%' . $studentNumber . '%');
            $filters['student_number'] = 'Student Number: ' . $studentNumber;
        }

        if ($nationalStudentNumber = trim((string) $request->query('national_sn', ''))) {
            $query->where('national_sn', 'like', '%' . $nationalStudentNumber . '%');
            $filters['national_sn'] = 'National Student Number: ' . $nationalStudentNumber;
        }

        if ($major = trim((string) $request->query('major', ''))) {
            $query->where('major', 'like', '%' . $major . '%');
            $filters['major'] = 'Major: ' . $major;
        }

        if ($class = trim((string) $request->query('class', ''))) {
            $query->where('class', 'like', '%' . $class . '%');
            $filters['class'] = 'Class: ' . $class;
        }

        if ($batch = trim((string) $request->query('batch', ''))) {
            $query->where('batch', 'like', '%' . $batch . '%');
            $filters['batch'] = 'Batch: ' . $batch;
        }

        if (($hasNotes = $request->query('has_notes')) !== null) {
            if ($hasNotes === 'true') {
                $query->whereNotNull('notes')->where('notes', '!=', '');
                $filters['has_notes'] = 'Has Notes: True';
            } elseif ($hasNotes === 'false') {
                $query->where(function ($sub) {
                    $sub->whereNull('notes')->orWhere('notes', '=', '');
                });
                $filters['has_notes'] = 'Has Notes: False';
            }
        }

        if (($hasPhoto = $request->query('has_photo')) !== null) {
            if ($hasPhoto === 'true') {
                $query->whereNotNull('photo')->where('photo', '!=', '');
                $filters['has_photo'] = 'Has Photo: True';
            } elseif ($hasPhoto === 'false') {
                $query->where(function ($sub) {
                    $sub->whereNull('photo')->orWhere('photo', '=', '');
                });
                $filters['has_photo'] = 'Has Photo: False';
            }
        }

        if ($q = trim($request->query('q', ''))) {
            $qLower = strtolower($q);
            $query->where(function ($sub) use ($qLower) {
                foreach (self::SEARCH_COLUMNS as $col) {
                    $sub->orWhereRaw('LOWER(' . $col . ') LIKE ?', ['%' . $qLower . '%']);
                }
            });
        }

        $sort = $request->query('sort', 'created_at:desc');
        [$sortField, $sortDir] = array_pad(explode(':', $sort), 2, 'desc');
        $allowedSorts = array_merge(self::SEARCH_COLUMNS, ['created_at', 'updated_at']);
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortField, $sortDir);

        $students = $query->paginate(10)->withQueryString();

        return view('student.index', [
            'students' => $students,
            'filters' => $filters,
        ]);
    }

    public function show($id)
    {
        $student = DB::table('student_details_view')->where('id', $id)->first();
        abort_if(!$student, 404);
        if (session('role') === 'student' && $student->id !== $this->currentStudentId()) {
            abort(401);
        }
        return view('student.show', compact('student'));
    }

    public function create()
    {
        if (session('role') === 'student') {
            abort(401);
        }
        return view('student.create');
    }

    public function store(Request $request)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|digits_between:6,15',
            'password' => 'required|string',
            'student_number' => 'required|string|unique:students,student_number',
            'national_sn' => 'required|string|unique:students,national_sn',
            'major' => 'required|string|max:100',
            'class' => 'required|string|max:100',
            'batch' => 'required|digits:4',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => 'student',
        ]);

        Student::create([
            'user_id' => $user->id,
            'student_number' => $data['student_number'],
            'national_sn' => $data['national_sn'],
            'major' => $data['major'],
            'class' => $data['class'],
            'batch' => (string) $data['batch'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        return redirect()->route('students.index');
    }

    public function edit($id)
    {
        if (session('role') === 'student' && (int) $id !== $this->currentStudentId()) {
            abort(401);
        }
        $student = DB::table('student_details_view')->where('id', $id)->first();
        abort_if(!$student, 404);
        return view('student.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        if (session('role') === 'student' && $student->user_id !== session('user_id')) {
            abort(401);
        }
        $user = $student->user;

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|digits_between:6,15',
            'password' => 'nullable|string',
            'student_number' => 'required|string|unique:students,student_number,' . $student->id,
            'national_sn' => 'required|string|unique:students,national_sn,' . $student->id,
            'major' => 'required|string|max:100',
            'class' => 'required|string|max:100',
            'batch' => 'required|digits:4',
            'notes' => 'nullable|string',
            'photo' => 'nullable|string',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        $student->update([
            'student_number' => $data['student_number'],
            'national_sn' => $data['national_sn'],
            'major' => $data['major'],
            'class' => $data['class'],
            'batch' => (string) $data['batch'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        $message = session('role') === 'student'
            ? 'Profile updated.'
            : 'Student updated.';

        return redirect()->route('students.index')->with('status', $message);
    }

    public function destroy(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        if (session('role') === 'student' && $student->user_id !== session('user_id')) {
            abort(401);
        }

        $student->user()->delete();

        if (session('user_id') === $student->user_id) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login')->with('status', 'Your account has been deleted. You have been logged out.');
        }

        return redirect()->route('students.index')->with('status', 'Student deleted.');
    }
}
