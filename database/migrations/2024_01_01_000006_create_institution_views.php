<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            CREATE OR REPLACE VIEW institution_details_view AS
            WITH primary_contact AS (
                SELECT DISTINCT ON (institution_id) id, institution_id, name, email, phone, position, is_primary
                FROM institution_contacts
                ORDER BY institution_id, is_primary DESC, id
            ),
            latest_quota AS (
                SELECT DISTINCT ON (institution_id) iq.id, iq.institution_id, iq.quota, iq.used, iq.notes, p.year, p.term
                FROM institution_quotas iq
                JOIN periods p ON p.id = iq.period_id
                ORDER BY iq.institution_id, p.year DESC, p.term DESC, iq.id DESC
            )
            SELECT i.id,
                   i.name,
                   i.address,
                   i.city,
                   i.province,
                   i.website,
                   i.notes,
                   i.photo,
                   pc.name AS contact_name,
                   pc.email AS contact_email,
                   pc.phone AS contact_phone,
                   pc.position AS contact_position,
                   pc.is_primary AS contact_primary,
                   lq.year AS period_year,
                   lq.term AS period_term,
                   lq.quota,
                   lq.used,
                   lq.notes AS quota_notes
            FROM institutions i
            LEFT JOIN primary_contact pc ON pc.institution_id = i.id
            LEFT JOIN latest_quota lq ON lq.institution_id = i.id;
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS institution_details_view;');
    }
};
