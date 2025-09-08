<?php

namespace Tests\Feature;

use App\Models\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class ApplicationMultiStudentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('institution_quotas', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('period_id');
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('period_id');
            $table->string('status');
            $table->date('submitted_at');
            $table->date('decision_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('institution_quotas');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('students');
        parent::tearDown();
    }

    public function test_can_create_multiple_applications_for_students(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $student2 = \DB::table('students')->insertGetId(['name' => 'Bob']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        \DB::table('institution_quotas')->insert(['institution_id' => $institution, 'period_id' => 1]);

        $this->withoutMiddleware()->withSession(['role' => 'admin'])->post('/application', [
            'student_ids' => [$student1, $student2],
            'institution_id' => $institution,
            'status' => 'draft',
            'submitted_at' => '2024-01-01',
        ])->assertRedirect('/application');

        $this->assertDatabaseCount('applications', 2);
        $this->assertDatabaseHas('applications', ['student_id' => $student1, 'institution_id' => $institution]);
        $this->assertDatabaseHas('applications', ['student_id' => $student2, 'institution_id' => $institution]);
    }

    public function test_mass_update_applies_to_all_applications_for_institution(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $student2 = \DB::table('students')->insertGetId(['name' => 'Bob']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        \DB::table('institution_quotas')->insert(['institution_id' => $institution, 'period_id' => 1]);

        $app1 = Application::create([
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => 1,
            'status' => 'draft',
            'submitted_at' => '2024-01-01',
        ]);

        $app2 = Application::create([
            'student_id' => $student2,
            'institution_id' => $institution,
            'period_id' => 1,
            'status' => 'draft',
            'submitted_at' => '2024-01-01',
        ]);

        $this->withoutMiddleware()->withSession(['role' => 'admin'])->put('/application/' . $app1->id, [
            'student_id' => $student1,
            'institution_id' => $institution,
            'status' => 'accepted',
            'submitted_at' => '2024-02-01',
            'apply_to_all' => 1,
            'notes' => 'updated',
        ])->assertRedirect('/application');

        $this->assertDatabaseHas('applications', ['id' => $app1->id, 'status' => 'accepted', 'notes' => 'updated']);
        $this->assertDatabaseHas('applications', ['id' => $app2->id, 'status' => 'accepted', 'notes' => 'updated', 'student_id' => $student2]);
    }

    public function test_store_requires_existing_students(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        \DB::table('institution_quotas')->insert(['institution_id' => $institution, 'period_id' => 1]);

        $response = $this->withoutMiddleware()->withSession(['role' => 'admin'])->post('/application', [
            'student_ids' => [$student1, 999],
            'institution_id' => $institution,
            'status' => 'draft',
            'submitted_at' => '2024-01-01',
        ]);

        $response->assertSessionHasErrors('student_ids.1');
        $this->assertDatabaseCount('applications', 0);
    }
}
