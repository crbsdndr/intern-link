<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use Carbon\Carbon;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        Application::create([
            'student_id' => 1,
            'institution_id' => 1,
            'period_id' => 1,
            'status' => 'accepted',
            'submitted_at' => Carbon::now(),
            'decision_at' => Carbon::now(),
        ]);
    }
}
