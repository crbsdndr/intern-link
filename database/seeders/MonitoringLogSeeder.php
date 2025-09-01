<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonitoringLog;
use Carbon\Carbon;
use Faker\Factory as Faker;

class MonitoringLogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        for ($i = 1; $i <= 10; $i++) {
            MonitoringLog::create([
                'internship_id' => $i,
                'supervisor_id' => $i,
                'log_date' => Carbon::now()->toDateString(),
                'score' => $faker->numberBetween(70, 100),
                'title' => 'Weekly Report ' . $i,
                'content' => $faker->sentence,
                'type' => 'weekly',
            ]);
        }
    }
}
