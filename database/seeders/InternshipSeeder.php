<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Internship;
use Carbon\Carbon;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        Internship::create([
            'application_id' => 1,
            'student_id' => 1,
            'institution_id' => 1,
            'period_id' => 1,
            'start_date' => Carbon::now()->toDateString(),
            'end_date' => Carbon::now()->addMonths(3)->toDateString(),
            'status' => 'ongoing',
        ]);
    }
}
