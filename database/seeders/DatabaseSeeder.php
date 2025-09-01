<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PeriodSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            SupervisorSeeder::class,
            InstitutionSeeder::class,
            InstitutionContactSeeder::class,
            InstitutionQuotaSeeder::class,
            ApplicationSeeder::class,
            ApplicationStatusHistorySeeder::class,
            InternshipSeeder::class,
            InternshipSupervisorSeeder::class,
            MonitoringLogSeeder::class,
        ]);
    }
}
