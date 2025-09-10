<?php

namespace Tests\Feature;

use App\Models\Internship;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class InternshipMultiApplicationTest extends TestCase
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
            $table->date('submitted_at');
            $table->timestamps();
        });

        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('period_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('internships');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('periods');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('students');
        parent::tearDown();
    }

    public function test_can_create_multiple_internships_for_applications(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $student2 = \DB::table('students')->insertGetId(['name' => 'Bob']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        $period = \DB::table('periods')->insertGetId(['year' => 2024, 'term' => '1']);

        $app1 = \DB::table('applications')->insertGetId([
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $app2 = \DB::table('applications')->insertGetId([
            'student_id' => $student2,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withoutMiddleware()->withSession(['role' => 'admin'])->post('/internship', [
            'application_ids' => [$app1, $app2],
            'start_date' => '2024-02-01',
            'end_date' => '2024-03-01',
            'status' => 'planned',
        ])->assertRedirect('/internship');

        $this->assertDatabaseCount('internships', 2);
        $this->assertDatabaseHas('internships', ['application_id' => $app1, 'student_id' => $student1]);
        $this->assertDatabaseHas('internships', ['application_id' => $app2, 'student_id' => $student2]);
    }

    public function test_cannot_create_internship_if_application_already_has_one(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $student2 = \DB::table('students')->insertGetId(['name' => 'Bob']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        $period = \DB::table('periods')->insertGetId(['year' => 2024, 'term' => '1']);

        $app1 = \DB::table('applications')->insertGetId([
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $app2 = \DB::table('applications')->insertGetId([
            'student_id' => $student2,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Internship::create([
            'application_id' => $app1,
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => $period,
            'start_date' => '2024-02-01',
            'end_date' => '2024-03-01',
            'status' => 'planned',
        ]);

        $this->withoutMiddleware()->withSession(['role' => 'admin'])->post('/internship', [
            'application_ids' => [$app1, $app2],
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-01',
            'status' => 'planned',
        ])->assertSessionHasErrors('application_ids');

        $this->assertDatabaseCount('internships', 1);
    }

    public function test_mass_update_creates_and_updates_internships(): void
    {
        $student1 = \DB::table('students')->insertGetId(['name' => 'Alice']);
        $student2 = \DB::table('students')->insertGetId(['name' => 'Bob']);
        $institution = \DB::table('institutions')->insertGetId(['name' => 'Inst']);
        $period = \DB::table('periods')->insertGetId(['year' => 2024, 'term' => '1']);

        $app1 = \DB::table('applications')->insertGetId([
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $app2 = \DB::table('applications')->insertGetId([
            'student_id' => $student2,
            'institution_id' => $institution,
            'period_id' => $period,
            'status' => 'accepted',
            'submitted_at' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $internship = Internship::create([
            'application_id' => $app1,
            'student_id' => $student1,
            'institution_id' => $institution,
            'period_id' => $period,
            'start_date' => '2024-02-01',
            'end_date' => '2024-03-01',
            'status' => 'planned',
        ]);

        $this->withoutMiddleware()->withSession(['role' => 'admin'])->put('/internship/' . $internship->id, [
            'application_ids' => [$app1, $app2],
            'start_date' => '2024-04-01',
            'end_date' => '2024-05-01',
            'status' => 'ongoing',
        ])->assertRedirect('/internship');

        $this->assertDatabaseHas('internships', [
            'application_id' => $app1,
            'start_date' => '2024-04-01 00:00:00',
            'status' => 'ongoing',
        ]);
        $this->assertDatabaseHas('internships', [
            'application_id' => $app2,
            'start_date' => '2024-04-01 00:00:00',
            'status' => 'ongoing',
        ]);
    }
}
