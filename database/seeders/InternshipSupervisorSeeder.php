<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            DB::table('internship_supervisors')->insert([
                'internship_id' => $i,
                'supervisor_id' => $i,
                'is_primary' => true,
            ]);
        }
    }
}
