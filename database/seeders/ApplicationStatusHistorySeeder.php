<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            DB::table('application_status_history')->insert([
                'application_id' => $i,
                'from_status' => 'submitted',
                'to_status' => 'accepted',
            ]);
        }
    }
}
