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
        $branches = ['Smart Village', 'Nasr City', 'Alexandria', 'Mansoura', 'Ismailia', 'Assiut', 'Sohag'];
        $programs = ['ptp', 'itp'];
        $intakes = ['31', '32', '33', '34', '35', '36', '37', '38', '39', '40', '41', '42', '43', '44', '45'];

        $arabicFirstNames = [
            'Ahmed', 'Mohamed', 'Ali', 'Omar', 'Khaled', 'Youssef', 'Mostafa', 'Ibrahim', 'Abdullah', 'Zainab',
            'Fatima', 'Aisha', 'Nour', 'Sara', 'Leila', 'Mariam', 'Hana', 'Jana', 'Salma', 'Yasmin',
            'Sami', 'Tarek', 'Hassan', 'Hussein', 'Amir', 'Rami', 'Fadi', 'Karim', 'Majed', 'Adel',
            'Mona', 'Lina', 'Dina', 'Rania', 'Samira', 'Nadia', 'Lamia', 'Huda', 'Souad', 'Farah',
            'Layla', 'Joud', 'Basma', 'Maya', 'Sama', 'Lara', 'Tala', 'Rima', 'Dana', 'Aya',
            'Yara', 'Zahra', 'Ola', 'Reem', 'Rawan', 'Dalia', 'Shatha', 'Hajar', 'Malak', 'Noor',
            'Hamza', 'Anas', 'Badr', 'Bilal', 'Jaber', 'Khalil', 'Luay', 'Mazen', 'Nabil', 'Osama',
            'Qais', 'Rakan', 'Sufyan', 'Talal', 'Wael', 'Yahya', 'Ziad', 'Amal', 'Batoul', 'Dalal',
            'Eman', 'Ghada', 'Hanan', 'Israa', 'Khadija', 'Lama', 'Marwa', 'Nada', 'Ola', 'Riham'
        ];

        $arabicLastNames = [
            'Mansour', 'El-Sayed', 'Hassan', 'Ali', 'Mohamed', 'Khalifa', 'Ahmed', 'Ibrahim', 'Abdullah', 'Zaki',
            'Fawzy', 'Salem', 'Amin', 'Gaber', 'Radwan', 'El-Deen', 'El-Gharib', 'El-Masry', 'El-Sharkawy', 'El-Maghraby',
            'Hamdan', 'Farah', 'Issa', 'Jaber', 'Kamal', 'Mahmoud', 'Nassar', 'Othman', 'Qasem', 'Ramadan',
            'Saad', 'Taha', 'Wahab', 'Youssef', 'Zahran', 'Bakri', 'Daoud', 'Fahmy', 'Ghazal', 'Haddad',
            'Jomaa', 'Kazem', 'Lotfy', 'Moussa', 'Naguib', 'Omar', 'Qandil', 'Rizk', 'Salah', 'Tawfik',
            'Wassef', 'Yassin', 'Zayed', 'Abdelaziz', 'Badawi', 'Darwish', 'Eid', 'Fadel', 'Ghali', 'Habib',
            'Jamal', 'Khoury', 'Labib', 'Maalouf', 'Naggar', 'Okasha', 'Qureshi', 'Rashid', 'Salloum', 'Tabet'
        ];

        return [
            'first_name' => $this->faker->randomElement($arabicFirstNames),
            'last_name' => $this->faker->randomElement($arabicLastNames),
            'username' => $this->faker->unique()->userName(),
            'summary' => $this->faker->randomElement([
                'Passionate software engineer with a strong background in web development and a keen eye for detail. Dedicated to building scalable and efficient applications.',
                'Experienced data scientist with a proven track record of extracting valuable insights from complex datasets. Skilled in machine learning and statistical analysis.',
                'Creative UI/UX designer focused on crafting intuitive and engaging user experiences. Specializes in user-centered design principles and modern design tools.',
                'Results-driven mobile developer proficient in both Android and iOS platforms. Committed to delivering high-quality, performant mobile applications.',
                'DevOps enthusiast with expertise in automating deployment pipelines and managing cloud infrastructure. A strong advocate for continuous integration and delivery.',
                'Cybersecurity professional with a deep understanding of network security and vulnerability assessment. Dedicated to protecting digital assets and ensuring data privacy.',
                'A highly motivated individual with a passion for continuous learning and problem-solving. Always eager to take on new challenges and contribute to innovative projects.',
                'Detail-oriented and organized professional with excellent communication and teamwork skills. Committed to achieving project goals and exceeding expectations.',
                'An innovative thinker who thrives in dynamic environments. Possesses a strong ability to adapt to new technologies and drive impactful solutions.',
                'Proactive and dedicated professional with a solid foundation in various technical domains. Eager to apply knowledge and skills to real-world problems.',
                'Highly analytical professional with a strong aptitude for problem-solving and critical thinking. Adept at transforming complex data into actionable insights.',
                'Dedicated and collaborative team player with a passion for innovation and continuous improvement. Proven ability to deliver high-quality results in fast-paced environments.',
                'Dynamic and versatile professional with a broad range of technical skills and a commitment to excellence. Excels at tackling diverse challenges and driving successful outcomes.',
                'Resourceful and adaptable individual with a strong commitment to learning and professional growth. Capable of quickly mastering new technologies and methodologies.',
                'Strategic and visionary leader with a proven track record of inspiring teams and achieving ambitious goals. Adept at fostering a culture of innovation and high performance.'
            ]),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'linkedin' => 'https://linkedin.com/in/' . $this->faker->userName(),
            'github' => 'https://github.com/' . $this->faker->userName(),
            'portfolio_url' => $this->faker->optional(0.7)->url(),
            'profile_picture' => null,
            'cover_photo' => null,
            'branch' => $this->faker->randomElement($branches),
            'program' => $this->faker->randomElement($programs),
            'available_for_freelance' => $this->faker->boolean(30),
            'track' => $this->faker->randomElement($tracks),
            'intake' => $intake = $this->faker->randomElement($intakes),
            'student_status' => ($intake == '45') ? 'current' : 'graduate',
            'nid_front_image' => null,
            'nid_back_image' => null,
        ];
    }

    /**
     * Indicate that the user profile is for an alumni.
     */
    public function alumni(): static
    {
        return $this->state(fn (array $attributes) => [
            'student_status' => 'graduate',
        ]);
    }
}
