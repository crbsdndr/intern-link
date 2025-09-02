<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 50; $i++) {
            Student::create([
                'user_id' => $i,
                'student_number' => 'S-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'national_sn' => 'NSN-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'major' => $faker->randomElement(['Computer Science', 'Information Systems', 'Engineering']),
                'batch' => (string)(2020 + $i),
            ]);
        }
    }
}
