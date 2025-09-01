<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonitoringLog;
use Carbon\Carbon;

class MonitoringLogSeeder extends Seeder
{
    public function run(): void
    {
        MonitoringLog::create([
            'internship_id' => 1,
            'supervisor_id' => 1,
            'log_date' => Carbon::now()->toDateString(),
            'score' => 90,
            'title' => 'Weekly Report',
            'content' => 'All tasks completed.',
            'type' => 'weekly',
        ]);
    }
}
