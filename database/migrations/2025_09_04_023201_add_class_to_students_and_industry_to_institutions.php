<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('class', 100)->default('');
        });

        DB::table('students')->where('class', '')->update(['class' => 'Unknown']);
        DB::statement("ALTER TABLE students ALTER COLUMN class SET NOT NULL");
        DB::statement("ALTER TABLE students ALTER COLUMN class DROP DEFAULT");
        Schema::table('students', function (Blueprint $table) {
            $table->index('class');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->string('industry', 100)->default('');
        });

        DB::table('institutions')->where('industry', '')->update(['industry' => 'Unknown']);
        DB::statement("ALTER TABLE institutions ALTER COLUMN industry SET NOT NULL");
        DB::statement("ALTER TABLE institutions ALTER COLUMN industry DROP DEFAULT");
        Schema::table('institutions', function (Blueprint $table) {
            $table->index('industry');
        });

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
                   s.class,
                   s.batch,
                   s.notes,
                   s.photo,
                   s.created_at,
                   s.updated_at
            FROM students s
            JOIN users u ON s.user_id = u.id;
        SQL);

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
                   i.industry,
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
                   s.photo,
                   s.created_at,
                   s.updated_at
            FROM students s
            JOIN users u ON s.user_id = u.id;
        SQL);

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

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['class']);
            $table->dropColumn('class');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex(['industry']);
            $table->dropColumn('industry');
        });
    }
};
