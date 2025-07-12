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
            'description' => $this->faker->randomElement([
                'A global leader in innovative software solutions, dedicated to transforming digital experiences and empowering businesses with cutting-edge technology.',
                'Pioneering sustainable energy solutions, committed to a greener future through advanced renewable energy technologies and responsible practices.',
                'A dynamic e-commerce platform specializing in handcrafted goods, connecting artisans with a global marketplace and fostering unique creativity.',
                'Leading the way in healthcare innovation, providing advanced medical devices and compassionate care to improve patient outcomes worldwide.',
                'A premier financial institution offering comprehensive banking, investment, and wealth management services, focused on client success and financial security.',
                'Revolutionizing education through interactive online learning platforms, making quality knowledge accessible to students of all ages and backgrounds.',
                'A cutting-edge biotechnology firm focused on groundbreaking research and development in genetic engineering and pharmaceutical advancements.',
            ]),
            'location' => $this->faker->randomElement($locations),
            'established_at' => $this->faker->dateTimeBetween('-20 years', '-1 year'),
            'website' => $this->faker->optional(0.8)->url(),
            'industry' => $this->faker->randomElement($industries),
            'company_size' => $this->faker->randomElement($companySizes),
            'logo' => 'test/company_logos/' . $this->faker->numberBetween(1, 8) . '.png',
        ];
    }
}
