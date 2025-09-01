<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        Student::create([
            'user_id' => 1,
            'student_number' => 'S-0001',
            'national_sn' => 'NSN-0001',
            'major' => 'Computer Science',
            'batch' => '2024',
        ]);
    }
}
