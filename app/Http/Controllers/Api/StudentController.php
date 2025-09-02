<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends ApiController
{
    public function index(Request $request)
    {
        $query = Student::with('user')->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        return $this->paginate($request, $query);
    }
}
