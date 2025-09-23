<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW developer_details_view AS
            SELECT u.id,
                   u.name,
                   u.email,
                   u.phone,
                   u.email_verified_at,
                   u.created_at,
                   u.updated_at
            FROM core.users u
            WHERE u.role = 'developer';
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS developer_details_view;');
    }
};
