<?php

namespace Database\Factories;

use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        $tracks = ['Web Development', 'Mobile Development', 'Data Science', 'UI/UX Design', 'DevOps', 'Cybersecurity'];
        $governorates = ['Cairo', 'Alexandria', 'Giza', 'Luxor', 'Aswan', 'Suez', 'Port Said', 'Ismailia'];
        $intakes = ['40', '41', '42', '43', '44', '45'];
        $statuses = ['current', 'graduate'];

        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'username' => $this->faker->unique()->userName(),
            'summary' => $this->faker->paragraph(3),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'linkedin' => 'https://linkedin.com/in/' . $this->faker->userName(),
            'github' => 'https://github.com/' . $this->faker->userName(),
            'portfolio_url' => $this->faker->optional(0.7)->url(),
            'profile_picture' => null,
            'cover_photo' => null,
            'governorate' => $this->faker->randomElement($governorates),
            'available_for_freelance' => $this->faker->boolean(30),
            'track' => $this->faker->randomElement($tracks),
            'intake' => $this->faker->randomElement($intakes),
            'graduation_date' => $this->faker->optional(0.6)->dateTimeBetween('-2 years', '+1 year'),
            'student_status' => $this->faker->randomElement($statuses),
            'nid_front_image' => null,
            'nid_back_image' => null,
        ];
    }
}