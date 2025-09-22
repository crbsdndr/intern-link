<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW supervisor_details_view AS
            SELECT s.id,
                   u.id AS user_id,
                   u.name,
                   u.email,
                   u.phone,
                   u.role,
                   s.supervisor_number,
                   s.department,
                   s.notes,
                   s.photo,
                   s.created_at,
                   s.updated_at,
                   u.email_verified_at
            FROM app.supervisors s
            JOIN core.users u ON s.user_id = u.id;
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW supervisor_details_view AS
            SELECT s.id,
                   u.id AS user_id,
                   u.name,
                   u.email,
                   u.phone,
                   u.role,
                   s.supervisor_number,
                   s.department,
                   s.notes,
                   s.photo,
                   s.created_at,
                   s.updated_at
            FROM app.supervisors s
            JOIN core.users u ON s.user_id = u.id;
        SQL);
    }
};
