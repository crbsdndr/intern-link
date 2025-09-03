<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

abstract class Controller
{
    protected function currentStudentId(): ?int
    {
        if (session('role') !== 'student') {
            return null;
        }
        return DB::table('students')->where('user_id', session('user_id'))->value('id');
    }

    protected function currentSupervisorId(): ?int
    {
        if (session('role') !== 'supervisor') {
            return null;
        }
        return DB::table('supervisors')->where('user_id', session('user_id'))->value('id');
    }
}

