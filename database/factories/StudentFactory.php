<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Student;
use App\Services\StudentIdentityService;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $name = fake()->name();

        return ['temporary_id' => 'TMP-TEST-'.fake()->unique()->numerify('####'), 'nis' => null, 'nisn' => null, 'name' => $name, 'normalized_name' => app(StudentIdentityService::class)->normalizeName($name), 'gender' => fake()->randomElement(['L', 'P']), 'status' => StudentStatus::Active];
    }
}
