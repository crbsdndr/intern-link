<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Columns searched and displayed. Adjust here if needed.
     */
    private const SEARCH_COLUMNS = ['name', 'major'];
    private const DISPLAY_COLUMNS = ['id', 'name', 'major'];

    public function index(Request $request)
    {
        $query = DB::table('student_details_view')
            ->select(self::DISPLAY_COLUMNS);
        if (session('role') === 'student') {
            $query->where('id', $this->currentStudentId());
        }

        $filters = [];

        if ($major = $request->query('major~')) {
            $query->where('major', 'like', '%' . $major . '%');
            $filters['major~'] = 'Major: ' . $major;
        }

        if ($batchParam = $request->query('batch')) {
            if (Str::startsWith($batchParam, 'in:')) {
                $batches = array_filter(explode(',', Str::after($batchParam, 'in:')));
                if ($batches) {
                    $query->whereIn('batch', $batches);
                    $filters['batch'] = 'Batch: ' . implode(', ', $batches);
                }
            }
        }

        if ($created = $request->query('created_at')) {
            if (Str::startsWith($created, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($created, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('created_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('created_at', '<=', $end);
                }
                $filters['created_at'] = 'Created: ' . $start . ' - ' . $end;
            }
        }

        if ($updated = $request->query('updated_at')) {
            if (Str::startsWith($updated, 'range:')) {
                [$start, $end] = array_pad(explode(',', Str::after($updated, 'range:')), 2, null);
                if ($start) {
                    $query->whereDate('updated_at', '>=', $start);
                }
                if ($end) {
                    $query->whereDate('updated_at', '<=', $end);
                }
                $filters['updated_at'] = 'Updated: ' . $start . ' - ' . $end;
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
        $allowedSorts = array_merge(self::DISPLAY_COLUMNS, ['created_at', 'updated_at']);
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
            'phone' => 'nullable|string',
            'password' => 'required|string',
            'student_number' => 'required|string|unique:students,student_number',
            'national_sn' => 'required|string|unique:students,national_sn',
            'major' => 'required|string',
            'batch' => 'required|string',
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
            'batch' => $data['batch'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        return redirect('/student');
    }

    public function edit($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $student = DB::table('student_details_view')->where('id', $id)->first();
        abort_if(!$student, 404);
        return view('student.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $student = Student::findOrFail($id);
        $user = $student->user;

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'password' => 'nullable|string',
            'student_number' => 'required|string|unique:students,student_number,' . $student->id,
            'national_sn' => 'required|string|unique:students,national_sn,' . $student->id,
            'major' => 'required|string',
            'batch' => 'required|string',
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
            'batch' => $data['batch'],
            'notes' => $data['notes'] ?? null,
            'photo' => $data['photo'] ?? null,
        ]);

        return redirect('/student');
    }

    public function destroy($id)
    {
        if (session('role') === 'student') {
            abort(401);
        }
        $student = Student::findOrFail($id);
        $student->user()->delete();
        return redirect('/student');
    }
}
