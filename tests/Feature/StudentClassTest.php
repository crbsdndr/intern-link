<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class StudentClassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        \DB::statement('DROP TABLE IF EXISTS students CASCADE');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('student_number');
            $table->string('national_sn');
            $table->string('major');
            $table->string('class');
            $table->string('batch');
            $table->text('notes')->nullable();
            $table->text('photo')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_student_class_is_saved_and_updated(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('secret'),
            'role' => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'S-0001',
            'national_sn' => 'NSN-0001',
            'major' => 'CS',
            'class' => 'XI RPL 1',
            'batch' => '2024',
        ]);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'class' => 'XI RPL 1',
        ]);

        $student->update(['class' => 'XII-A']);

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'class' => 'XII-A',
        ]);
    }
}
