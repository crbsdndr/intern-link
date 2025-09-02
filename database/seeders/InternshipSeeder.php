<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Internship;
use Carbon\Carbon;

class InternshipSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            $start = Carbon::now();
            Internship::create([
                'application_id' => $i,
                'student_id' => $i,
                'institution_id' => $i,
                'period_id' => $i,
                'start_date' => $start->toDateString(),
                'end_date' => $start->copy()->addMonths(3)->toDateString(),
                'status' => 'ongoing',
            ]);
        }
    }
}
