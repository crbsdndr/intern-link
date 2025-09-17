<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Application;
use Carbon\Carbon;

class ApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        for ($i = 1; $i <= 50; $i++) {
            Application::create([
                'student_id' => $i,
                'institution_id' => $i,
                'period_id' => $i,
                'status' => 'accepted',
                'submitted_at' => $now,
            ]);
        }
    }
}
