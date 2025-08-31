<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW student_details_view AS
            SELECT s.id,
                   u.id AS user_id,
                   u.name,
                   u.email,
                   u.phone,
                   u.role,
                   s.student_number,
                   s.national_sn,
                   s.major,
                   s.batch,
                   s.notes,
                   s.photo
            FROM students s
            JOIN users u ON s.user_id = u.id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS student_details_view;');
    }
};
