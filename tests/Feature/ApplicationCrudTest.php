<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class ApplicationCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite driver not available.');
        }

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
            ],
        ]);

        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect();

        DB::statement('DROP VIEW IF EXISTS application_details_view');
        DB::statement('DROP VIEW IF EXISTS student_details_view');
        DB::statement('DROP VIEW IF EXISTS institution_details_view');

        Schema::dropIfExists('applications');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('students');

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('student_number')->nullable();
            $table->string('national_sn')->nullable();
            $table->string('major')->nullable();
            $table->string('class')->nullable();
            $table->string('batch')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('website')->nullable();
            $table->string('industry')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_position')->nullable();
            $table->boolean('contact_primary')->default(false);
            $table->integer('quota')->nullable();
            $table->integer('quota_used')->nullable();
            $table->integer('quota_period_year')->nullable();
            $table->string('quota_period_term')->nullable();
            $table->timestamps();
        });

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->string('term');
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('period_id');
            $table->string('status');
            $table->boolean('student_access')->default(false);
            $table->date('submitted_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            CREATE VIEW student_details_view AS
            SELECT id,
                   user_id,
                   name,
                   email,
                   NULL AS email_verified_at,
                   phone,
                   'student' AS role,
                   student_number,
                   national_sn,
                   major,
                   class,
                   batch,
                   notes,
                   photo,
                   created_at,
                   updated_at
            FROM students
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW institution_details_view AS
            SELECT id,
                   name,
                   address,
                   city,
                   province,
                   website,
                   industry,
                   notes,
                   photo,
                   contact_name,
                   contact_email,
                   contact_phone,
                   contact_position,
                   contact_primary,
                   quota,
                   quota_used AS used,
                   quota_period_year AS period_year,
                   quota_period_term AS period_term
            FROM institutions
        SQL);

        DB::statement(<<<'SQL'
            CREATE VIEW application_details_view AS
            SELECT a.id,
                   a.student_id,
                   s.user_id               AS student_user_id,
                   s.name                  AS student_name,
                   s.email                 AS student_email,
                   s.phone                 AS student_phone,
                   s.student_number,
                   s.national_sn,
                   s.major                 AS student_major,
                   s.class                 AS student_class,
                   s.batch                 AS student_batch,
                   s.notes                 AS student_notes,
                   s.photo                 AS student_photo,
                   a.institution_id,
                   i.name                  AS institution_name,
                   i.address               AS institution_address,
                   i.city                  AS institution_city,
                   i.province              AS institution_province,
                   i.website               AS institution_website,
                   i.industry              AS institution_industry,
                   i.notes                 AS institution_notes,
                   i.photo                 AS institution_photo,
                   i.contact_name          AS institution_contact_name,
                   i.contact_email         AS institution_contact_email,
                   i.contact_phone         AS institution_contact_phone,
                   i.contact_position      AS institution_contact_position,
                   i.contact_primary       AS institution_contact_primary,
                   i.quota                 AS institution_quota,
                   i.quota_used            AS institution_quota_used,
                   i.quota_period_year     AS institution_quota_period_year,
                   i.quota_period_term     AS institution_quota_period_term,
                   a.period_id,
                   p.year                  AS period_year,
                   p.term                  AS period_term,
                   a.status,
                   a.student_access,
                   a.submitted_at,
                   a.notes                 AS application_notes,
                   a.created_at,
                   a.updated_at
            FROM applications a
            JOIN students s ON s.id = a.student_id
            JOIN institutions i ON i.id = a.institution_id
            JOIN periods p ON p.id = a.period_id
        SQL);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    private function seedStudent(int $userId = 1): int
    {
        return DB::table('students')->insertGetId([
            'user_id' => $userId,
            'name' => 'Student ' . $userId,
            'email' => 'student' . $userId . '@example.com',
            'phone' => '0800-000' . $userId,
            'student_number' => 'SN' . $userId,
            'national_sn' => 'NSN' . $userId,
            'major' => 'Major',
            'class' => 'Class A',
            'batch' => '2024',
            'notes' => 'Notes',
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedInstitution(): int
    {
        return DB::table('institutions')->insertGetId([
            'name' => 'Institution',
            'address' => 'Address',
            'city' => 'City',
            'province' => 'Province',
            'website' => 'https://example.com',
            'industry' => 'Industry',
            'notes' => 'Notes',
            'photo' => null,
            'contact_name' => 'Contact',
            'contact_email' => 'contact@example.com',
            'contact_phone' => '12345',
            'contact_position' => 'Manager',
            'contact_primary' => true,
            'quota' => 10,
            'quota_used' => 2,
            'quota_period_year' => 2024,
            'quota_period_term' => 'Term 1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedPeriod(): int
    {
        return DB::table('periods')->insertGetId([
            'year' => 2024,
            'term' => 'Term 1',
        ]);
    }

    public function test_admin_can_create_application(): void
    {
        $studentId = $this->seedStudent();
        $institutionId = $this->seedInstitution();
        $periodId = $this->seedPeriod();

        $response = $this->withoutMiddleware()
            ->withSession(['role' => 'admin'])
            ->post('/applications', [
                'student_id' => $studentId,
                'institution_id' => $institutionId,
                'period_id' => $periodId,
                'status' => 'submitted',
                'student_access' => 'true',
                'submitted_at' => '2024-01-01',
                'notes' => 'Initial notes',
            ]);

        $response->assertRedirect('/applications');
        $this->assertDatabaseHas('applications', [
            'student_id' => $studentId,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'student_access' => true,
            'notes' => 'Initial notes',
        ]);
    }

    public function test_student_cannot_create_duplicate_application(): void
    {
        $studentId = $this->seedStudent();
        $institutionId = $this->seedInstitution();
        $periodId = $this->seedPeriod();

        Application::create([
            'student_id' => $studentId,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'status' => 'submitted',
            'submitted_at' => '2024-01-01',
        ]);

        $response = $this->withoutMiddleware()
            ->withSession(['role' => 'student', 'user_id' => 1])
            ->post('/applications', [
                'institution_id' => $institutionId,
                'period_id' => $periodId,
                'status' => 'submitted',
                'submitted_at' => '2024-02-01',
            ]);

        $response->assertRedirect('/applications');
        $this->assertDatabaseCount('applications', 1);
    }

    public function test_student_cannot_update_without_access(): void
    {
        $studentId = $this->seedStudent();
        $institutionId = $this->seedInstitution();
        $periodId = $this->seedPeriod();

        $application = Application::create([
            'student_id' => $studentId,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'status' => 'submitted',
            'student_access' => false,
            'submitted_at' => '2024-01-01',
            'notes' => null,
        ]);

        $response = $this->withoutMiddleware()
            ->withSession(['role' => 'student', 'user_id' => 1])
            ->put('/applications/' . $application->id, [
                'student_ids' => [$studentId],
                'institution_id' => $institutionId,
                'period_id' => $periodId,
                'status' => 'submitted',
                'submitted_at' => '2024-02-01',
            ]);

        $response->assertStatus(401);
    }

    public function test_student_can_update_with_access(): void
    {
        $studentId = $this->seedStudent();
        $institutionId = $this->seedInstitution();
        $periodId = $this->seedPeriod();

        $application = Application::create([
            'student_id' => $studentId,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'status' => 'submitted',
            'student_access' => true,
            'submitted_at' => '2024-01-01',
            'notes' => 'Old',
        ]);

        $response = $this->withoutMiddleware()
            ->withSession(['role' => 'student', 'user_id' => 1])
            ->put('/applications/' . $application->id, [
                'student_ids' => [$studentId],
                'institution_id' => $institutionId,
                'period_id' => $periodId,
                'status' => 'submitted',
                'submitted_at' => '2024-02-01',
                'notes' => 'Updated by student',
            ]);

        $response->assertRedirect('/applications/' . $application->id . '/read/');
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'notes' => 'Updated by student',
            'submitted_at' => '2024-02-01',
        ]);
    }

    public function test_admin_apply_all_updates_all_students(): void
    {
        $studentA = $this->seedStudent(1);
        $studentB = $this->seedStudent(2);
        $institutionId = $this->seedInstitution();
        $periodId = $this->seedPeriod();

        $appA = Application::create([
            'student_id' => $studentA,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'status' => 'submitted',
            'student_access' => true,
            'submitted_at' => '2024-01-01',
        ]);

        $appB = Application::create([
            'student_id' => $studentB,
            'institution_id' => $institutionId,
            'period_id' => $periodId,
            'status' => 'submitted',
            'student_access' => false,
            'submitted_at' => '2024-01-01',
        ]);

        $response = $this->withoutMiddleware()
            ->withSession(['role' => 'admin'])
            ->put('/applications/' . $appA->id, [
                'student_ids' => [$studentA],
                'institution_id' => $institutionId,
                'period_id' => $periodId,
                'status' => 'accepted',
                'student_access' => 'true',
                'submitted_at' => '2024-03-01',
                'notes' => 'Batch update',
                'apply_all' => 1,
            ]);

        $response->assertRedirect('/applications/' . $appA->id . '/read/');

        $this->assertDatabaseHas('applications', [
            'id' => $appA->id,
            'status' => 'accepted',
            'notes' => 'Batch update',
            'student_access' => true,
        ]);

        $this->assertDatabaseHas('applications', [
            'id' => $appB->id,
            'status' => 'accepted',
            'notes' => 'Batch update',
            'student_access' => true,
        ]);
    }
}
