<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

class EducationFactory extends Factory
{
    protected $model = Education::class;

    public function definition(): array
    {
        $degrees = ['Bachelor', 'Master', 'PhD', 'Diploma', 'Certificate'];
        $fields = [
            'Computer Science', 'Information Technology', 'Software Engineering',
            'Business Administration', 'Engineering', 'Marketing', 'Design',
            'Data Science', 'Cybersecurity', 'Network Administration'
        ];
        
        $institutions = [
            'Cairo University', 'American University in Cairo', 'Ain Shams University',
            'Alexandria University', 'Helwan University', 'ITI - Information Technology Institute',
            'German University in Cairo', 'British University in Egypt'
        ];

        $startDate = $this->faker->dateTimeBetween('-6 years', '-2 years');
        $endDate = $this->faker->optional(0.8)->dateTimeBetween($startDate, 'now');

        return [
            'degree' => $this->faker->randomElement($degrees),
            'field_of_study' => $this->faker->randomElement($fields),
            'institution' => $this->faker->randomElement($institutions),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'description' => $this->faker->optional(0.6)->paragraph(2),
        ];
    }
}