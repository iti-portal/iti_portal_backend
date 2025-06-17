<?php

namespace Database\Factories;

use App\Models\CompanyProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyProfileFactory extends Factory
{
    protected $model = CompanyProfile::class;

    public function definition(): array
    {
        $industries = [
            'Technology', 'Healthcare', 'Finance', 'Education', 'E-commerce', 
            'Manufacturing', 'Consulting', 'Media', 'Real Estate', 'Transportation'
        ];
        
        $companySizes = ['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'];
        
        $locations = [
            'Cairo, Egypt', 'Alexandria, Egypt', 'Giza, Egypt', 'New Cairo, Egypt',
            'Sheikh Zayed, Egypt', 'Maadi, Cairo', 'Heliopolis, Cairo'
        ];

        return [
            'company_name' => $this->faker->company(),
            'description' => $this->faker->paragraph(4),
            'location' => $this->faker->randomElement($locations),
            'established_at' => $this->faker->dateTimeBetween('-20 years', '-1 year'),
            'website' => $this->faker->optional(0.8)->url(),
            'industry' => $this->faker->randomElement($industries),
            'company_size' => $this->faker->randomElement($companySizes),
            'logo' => null,
        ];
    }
}