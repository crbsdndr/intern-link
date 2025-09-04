<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'student']),
            'student_number' => $this->faker->unique()->bothify('S-####'),
            'national_sn' => $this->faker->unique()->bothify('NSN-####'),
            'major' => $this->faker->randomElement(['Computer Science', 'Information Systems', 'Engineering']),
            'class' => $this->faker->bothify('XI-?? #'),
            'batch' => (string) $this->faker->year(),
            'notes' => null,
            'photo' => null,
        ];
    }
}
