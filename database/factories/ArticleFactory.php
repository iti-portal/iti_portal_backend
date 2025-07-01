<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $techTopics = [
            'Getting Started with Laravel 11',
            'React Best Practices in 2024',
            'Building Scalable APIs with Node.js',
            'Introduction to Machine Learning',
            'Docker for Beginners',
            'Modern CSS Techniques',
            'Database Optimization Tips',
            'Mobile App Development Trends',
            'Cybersecurity Fundamentals',
            'Cloud Computing Essentials'
        ];

        return [
            'title' => $this->faker->randomElement($techTopics),
            'content' => $this->faker->paragraphs(5, true),
            'featured_image' => null,
            'external_link' => $this->faker->optional(0.3)->url(),
            'status' => $this->faker->randomElement(['published', 'draft']),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'like_count' => $this->faker->numberBetween(0, 100),
        ];
    }
}