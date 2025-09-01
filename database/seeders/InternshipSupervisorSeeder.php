<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InternshipSupervisorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('internship_supervisors')->insert([
            'internship_id' => 1,
            'supervisor_id' => 1,
            'is_primary' => true,
        ]);
    }
}
