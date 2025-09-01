<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supervisor;

class SupervisorSeeder extends Seeder
{
    public function run(): void
    {
        Supervisor::create([
            'user_id' => 2,
            'supervisor_number' => 'SP-0001',
            'department' => 'Engineering',
        ]);
    }
}
