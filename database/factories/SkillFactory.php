<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        $skills = [
            // Programming Languages
            ['name' => 'PHP', 'description' => 'Server-side scripting language'],
            ['name' => 'JavaScript', 'description' => 'Client-side and server-side programming language'],
            ['name' => 'Python', 'description' => 'High-level programming language'],
            ['name' => 'Java', 'description' => 'Object-oriented programming language'],
            ['name' => 'C#', 'description' => 'Microsoft programming language'],
            ['name' => 'C++', 'description' => 'System programming language'],
            ['name' => 'TypeScript', 'description' => 'Typed superset of JavaScript'],
            
            // Web Technologies
            ['name' => 'HTML5', 'description' => 'Markup language for web pages'],
            ['name' => 'CSS3', 'description' => 'Styling language for web pages'],
            ['name' => 'React', 'description' => 'JavaScript library for building user interfaces'],
            ['name' => 'Vue.js', 'description' => 'Progressive JavaScript framework'],
            ['name' => 'Angular', 'description' => 'TypeScript-based web application framework'],
            ['name' => 'Laravel', 'description' => 'PHP web application framework'],
            ['name' => 'Node.js', 'description' => 'JavaScript runtime environment'],
            
            // Databases
            ['name' => 'MySQL', 'description' => 'Relational database management system'],
            ['name' => 'PostgreSQL', 'description' => 'Advanced relational database'],
            ['name' => 'MongoDB', 'description' => 'NoSQL document database'],
            ['name' => 'Redis', 'description' => 'In-memory data structure store'],
            
            // DevOps & Tools
            ['name' => 'Docker', 'description' => 'Containerization platform'],
            ['name' => 'AWS', 'description' => 'Amazon Web Services cloud platform'],
            ['name' => 'Git', 'description' => 'Version control system'],
            ['name' => 'Linux', 'description' => 'Open-source operating system'],
            
            // Mobile Development
            ['name' => 'React Native', 'description' => 'Mobile app development framework'],
            ['name' => 'Flutter', 'description' => 'Google mobile app development framework'],
            
            // Design & UI/UX
            ['name' => 'UI/UX Design', 'description' => 'User interface and experience design'],
            ['name' => 'Figma', 'description' => 'Design and prototyping tool'],
            
            // Soft Skills
            ['name' => 'Project Management', 'description' => 'Planning and executing projects'],
            ['name' => 'Team Leadership', 'description' => 'Leading and managing teams'],
            ['name' => 'Communication', 'description' => 'Effective verbal and written communication'],
        ];

        $skill = $this->faker->randomElement($skills);
        
        return [
            'name' => $skill['name'],
        ];
    }
}