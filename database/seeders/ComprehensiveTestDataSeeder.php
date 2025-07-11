<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\CompanyProfile;
use App\Models\StaffProfile;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\Education;
use App\Models\WorkExperience;
use App\Models\Project;
use App\Models\Certificate;
use App\Models\Award;
use App\Models\Achievement;
use App\Models\AchievementLike;
use App\Models\AchievementComment;
use App\Models\AlumniService;
use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\AvailableJob;
use App\Models\JobSkill;
use App\Models\JobApplication;
use App\Models\Connection;
use App\Models\Notification;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ComprehensiveTestDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $this->command->info('🚀 Starting comprehensive test data seeding...');
        
        // Step 1: Create Skills (independent)
        $this->command->info('📚 Creating skills...');
        $this->createSkills();
        
        // Step 2: Create Users with different roles
        $this->command->info('👥 Creating users...');
        $users = $this->createUsers();
        
        // Step 3: Create User Profiles
        $this->command->info('📝 Creating user profiles...');
        $this->createUserProfiles($users);
        
        // Step 4: Create Company Profiles
        $this->command->info('🏢 Creating company profiles...');
        $this->createCompanyProfiles($users);
        
        // Step 5: Create Staff Profiles
        $this->command->info('👨‍💼 Creating staff profiles...');
        $this->createStaffProfiles($users);
        
        // Step 6: Create Education records
        $this->command->info('🎓 Creating education records...');
        $this->createEducation($users);
        
        // Step 7: Create Work Experience
        $this->command->info('💼 Creating work experience...');
        $this->createWorkExperience($users);
        
        // Step 8: Create User Skills
        $this->command->info('🛠️ Creating user skills...');
        $this->createUserSkills($users);
        
        // Step 9: Create Projects
        $this->command->info('🚀 Creating projects...');
        $this->createProjects($users);
        
        // Step 10: Create Certificates
        $this->command->info('📜 Creating certificates...');
        $this->createCertificates($users);
        
        // Step 11: Create Awards
        $this->command->info('🏆 Creating awards...');
        $this->createAwards($users);
        
        // Step 12: Create Achievements
        $this->command->info('🎯 Creating achievements...');
        $achievements = $this->createAchievements($users);
        
        // Step 13: Create Achievement Interactions
        $this->command->info('❤️ Creating achievement interactions...');
        $this->createAchievementInteractions($achievements, $users);
        
        // Step 14: Create Alumni Services
        $this->command->info('🎓 Creating alumni services...');
        $this->createAlumniServices($users);
        
        // Step 15: Create Articles
        $this->command->info('📰 Creating articles...');
        $articles = $this->createArticles($users);
        
        // Step 16: Create Article Likes
        $this->command->info('👍 Creating article likes...');
        $this->createArticleLikes($articles, $users);
        
        // Step 17: Create Available Jobs
        $this->command->info('💼 Creating available jobs...');
        $jobs = $this->createAvailableJobs($users);
        
        // Step 18: Create Job Skills
        $this->command->info('🔧 Creating job skills...');
        $this->createJobSkills($jobs);
        
        // Step 19: Create Job Applications
        $this->command->info('📋 Creating job applications...');
        $this->createJobApplications($jobs, $users);
        
        // Step 20: Create Connections
        $this->command->info('🤝 Creating connections...');
        $this->createConnections($users);
        
        // Step 21: Create Notifications
        $this->command->info('🔔 Creating notifications...');
        $this->createNotifications($users);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('✅ Comprehensive test data seeding completed successfully!');
        $this->printSummary();
    }
    
    private function createSkills()
    {
        $skills = [
            'PHP', 'JavaScript', 'Python', 'Java', 'React', 'Vue.js', 'Laravel', 
            'Node.js', 'MySQL', 'MongoDB', 'Docker', 'AWS', 'Git', 'React Native', 
            'Flutter', 'UI/UX Design', 'TypeScript', 'Angular', 'Express.js', 
            'PostgreSQL', 'Redis', 'Linux', 'Kubernetes', 'Jenkins', 'Figma', 
            'Adobe Photoshop', 'Machine Learning', 'C', 'C++', 'C#', 'Go', 'Rust', 
            'Ruby', 'Swift', 'Kotlin', 'Scala', 'Haskell', 'Erlang', 'Elixir', 
            'Clojure', 'Lisp', 'Dart', 'Assembly', 'R', 'Julia', 'Groovy', 
            'CoffeeScript', 'Visual Basic','Perl', 'Svelte', 'jQuery', 'Bootstrap',
            'Tailwind CSS', 'Next.js', 'Nuxt.js', 'Gatsby', 'Webpack', 'Babel', 
            'Vite', 'Django', 'Flask', 'Ruby on Rails', 'Spring Boot', 'ASP.NET', 
            'Koa.js', 'SQLite', 'Redis', 'Cassandra', 'Neo4j', 'Solr', 'Kafka', 
            'RabbitMQ', 'GraphQL', 'RESTful APIs', 'SQL', 'DynamoDB', 'BigQuery',
            'Azure', 'Terraform', 'Ansible', 'Chef', 'Puppet', 'Nagios', 
            'Prometheus', 'Grafana', 'Splunk', 'OpenShift', 'SVN', 'Mercurial', 
            'Xamarin', 'Ionic', 'Unity', 'Unreal Engine', 'Godot'
        ];
        
        foreach ($skills as $skillName) {
            Skill::firstOrCreate(['name' => $skillName]);
        }
    }
    
    private function createUsers()
    {
        $users = collect();
        
        // Create students/alumni
        $studentUsers = User::factory(30)->create();
        foreach ($studentUsers as $user) {
            $user->assignRole('student');
        }
        $users = $users->merge($studentUsers);
        

        $alumniUsers = User::factory(30)->create();
        foreach ($alumniUsers as $user) {
            $user->assignRole('alumni');
        }
        $users = $users->merge($alumniUsers);

        // Create companies
        $companyUsers = User::factory(20)->create();
        foreach ($companyUsers as $user) {
            $user->assignRole('company');
        }
        $users = $users->merge($companyUsers);
        
        // Create staff
        $staffUsers = User::factory(8)->create();
        foreach ($staffUsers as $user) {
            $user->assignRole('staff');
        }
        $users = $users->merge($staffUsers);
        
        return $users;
    }
    
    private function createUserProfiles($users)
    {
        // Alumni: status = graduate, intake != 45
        $alumniUsers = $users->filter(function ($user) {
            return $user->hasRole('alumni');
        });
    
        foreach ($alumniUsers as $user) {
            UserProfile::factory()->state([
                'user_id' => $user->id,
                'student_status' => 'graduate',
                'intake' => fake()->randomElement(['31','32','33','34','35','36','37','38','39','40','41','42','43','44']),
            ])->create();
        }
    
        // Students: status = current, intake = 45
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole('student');
        });
    
        foreach ($studentUsers as $user) {
            UserProfile::factory()->state([
                'user_id' => $user->id,
                'student_status' => 'current',
                'intake' => '45',
            ])->create();
        }
    }
    
    
    private function createCompanyProfiles($users)
    {
        $companyUsers = $users->filter(function ($user) {
            return $user->hasRole('company');
        });
        
        foreach ($companyUsers as $user) {
            CompanyProfile::factory()->create(['user_id' => $user->id]);
        }
    }
    
    private function createStaffProfiles($users)
    {
        $staffUsers = $users->filter(function ($user) {
            return $user->hasRole('staff');
        });
        
        foreach ($staffUsers as $user) {
            $user->update(['status' => 'approved']); // Ensure staff are approved
            
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

            StaffProfile::create([
                'user_id' => $user->id,
                'full_name' => fake()->randomElement($arabicFirstNames) . ' ' . fake()->randomElement($arabicLastNames),
                'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
                'position' => fake()->randomElement(['Manager', 'Coordinator', 'Specialist', 'Director']),
            ]);
        }
    }
    
    private function createEducation($users)
    {
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            // Each user gets 1-3 education records
            $count = fake()->numberBetween(1, 3);
            Education::factory($count)->create(['user_id' => $user->id]);
        }
    }
    
    private function createWorkExperience($users)
    {
        $experiencedUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($experiencedUsers as $user) {
            // 70% chance of having work experience, 1-4 records
            if (fake()->boolean(70)) {
                $count = fake()->numberBetween(1, 4);
                WorkExperience::factory($count)->create(['user_id' => $user->id]);
            }
        }
    }
    
    private function createUserSkills($users)
    {
        $skills = Skill::all();
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            // Each user gets 3-8 skills
            $userSkills = $skills->random(fake()->numberBetween(3, 8));
            foreach ($userSkills as $skill) {
                UserSkill::create([
                    'user_id' => $user->id,
                    'skill_id' => $skill->id,
                ]);
            }
        }
    }
    
    private function createProjects($users)
    {
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            // 80% chance of having projects, 1-5 projects
            if (fake()->boolean(80)) {
                $count = fake()->numberBetween(1, 5);
                for ($i = 0; $i < $count; $i++) {
                    $titles = [
                        'E-commerce Platform for Local Artisans',
                        'AI-Powered Chatbot for Customer Support',
                        'Mobile Health Tracking Application',
                        'Decentralized Voting System (Blockchain)',
                        'Real-time Collaborative Whiteboard',
                        'Personal Finance Management Dashboard',
                        'Smart Home Automation System',
                        'Online Learning Management System',
                        'Supply Chain Optimization with IoT',
                        'Augmented Reality Navigation App',
                    ];

                    $descriptions = [
                        'Developed a full-stack e-commerce solution enabling local artisans to sell their products online, featuring secure payment gateways and administrative dashboards.',
                        'Implemented an AI-driven chatbot using natural language processing to provide instant customer support and answer frequently asked questions, reducing response times significantly.',
                        'Designed and built a mobile application for tracking health metrics, including heart rate, sleep patterns, and activity levels, with data visualization and personalized insights.',
                        'Created a secure and transparent decentralized voting system leveraging blockchain technology to ensure integrity and immutability of votes.',
                        'Built a web-based collaborative whiteboard application allowing multiple users to draw and brainstorm in real-time, with features like undo/redo and session management.',
                        'Developed an intuitive personal finance management dashboard to help users track income, expenses, and investments, with budgeting tools and financial reporting.',
                        'Engineered a smart home automation system integrating various IoT devices for lighting, temperature control, and security, accessible via a mobile app.',
                        'Constructed a comprehensive online learning management system (LMS) with course creation, student enrollment, progress tracking, and quiz functionalities.',
                        'Implemented an IoT-based solution for optimizing supply chain logistics, providing real-time tracking of goods and predictive analytics for inventory management.',
                        'Developed an augmented reality (AR) navigation application that overlays directions and points of interest onto the real-world view using device cameras.',
                    ];

                    $technologies = [
                        'Laravel, Vue.js, MySQL, Tailwind CSS, Stripe API',
                        'Python, TensorFlow, Flask, NLTK, Dialogflow',
                        'React Native, Node.js, MongoDB, Firebase, Redux',
                        'Solidity, Ethereum, Web3.js, Truffle, Ganache',
                        'Node.js, Socket.IO, React, Canvas API, Express.js',
                        'Django, PostgreSQL, React, D3.js, RESTful APIs',
                        'Raspberry Pi, Python, MQTT, Node-RED, AWS IoT',
                        'PHP, Moodle, MySQL, SCORM, JavaScript',
                        'Java, Spring Boot, Kafka, Apache Cassandra, Azure IoT Hub',
                        'Unity, C#, ARCore, ARKit, GPS',
                    ];

                    $project = Project::create([
                        'user_id' => $user->id,
                        'title' => fake()->randomElement($titles),
                        'description' => fake()->randomElement($descriptions),
                        'technologies_used' => fake()->randomElement($technologies),
                        'project_url' => fake()->optional(0.7)->url(),
                        'github_url' => fake()->optional(0.8)->url(),
                        'start_date' => fake()->dateTimeBetween('-2 years', '-6 months'),
                        'end_date' => fake()->optional(0.6)->dateTimeBetween('-6 months', 'now'),
                        'is_featured' => fake()->boolean(20),
                    ]);

                    // Create 1-2 images for each project
                    $imageCount = fake()->numberBetween(1, 2);
                    $availableImageNumbers = range(1, 5); // Assuming images 1.png to 5.png exist
                    shuffle($availableImageNumbers); // Shuffle to pick unique numbers for this project

                    for ($j = 0; $j < $imageCount; $j++) {
                        $imageNumber = $availableImageNumbers[$j]; // Get a unique number for this image
                        \App\Models\ProjectImage::create([
                            'project_id' => $project->id,
                            'image_path' => 'test/project_images/' . $imageNumber . '.png',
                            'alt_text' => fake()->sentence(3),
                            'order' => $j + 1,
                        ]);
                    }
                }
            }
        }
    }
    
    private function createCertificates($users)
    {
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            $image = fake()->numberBetween(1,6);
            // 60% chance of having certificates, 1-3 certificates
            if (fake()->boolean(60)) {
                $count = fake()->numberBetween(1, 3);
                for ($i = 0; $i < $count; $i++) {
                    Certificate::create([
                        'user_id' => $user->id,
                        'title' => fake()->randomElement([
                            'AWS Certified Solutions Architect - Associate',
                            'Google Cloud Professional Data Engineer',
                            'Microsoft Certified: Azure Developer Associate',
                            'Certified ScrumMaster (CSM)',
                            'Cisco Certified Network Associate (CCNA)',
                            'Project Management Professional (PMP)',
                            'Certified Information Systems Security Professional (CISSP)',
                            'CompTIA Security+',
                            'Certified Kubernetes Administrator (CKA)',
                            'Certified Ethical Hacker (CEH)'
                        ]),
                        'description' => fake()->randomElement([
                            'Validated expertise in designing distributed systems and applications on the AWS platform, covering architectural principles and best practices.',
                            'Demonstrated proficiency in designing and building data processing systems on Google Cloud Platform, including data pipelines, machine learning models, and data warehousing solutions.',
                            'Proven ability to design, build, test, and maintain cloud applications and services on Microsoft Azure, with a focus on scalable and resilient solutions.',
                            'Recognized for understanding Scrum framework, including roles, events, and artifacts, enabling effective team facilitation and project delivery in agile environments.',
                            'Certified in foundational networking skills, including network access, IP connectivity, IP services, security fundamentals, and automation and programmability.',
                            'Globally recognized certification for project managers demonstrating experience, education, and competence in leading and directing projects.',
                            'Validated advanced knowledge and hands-on experience in information security, covering areas such as security architecture, risk management, and software development security.',
                            'Certified in core cybersecurity skills, including network security, threats, vulnerabilities, and data and application security.',
                            'Demonstrated proficiency in deploying, configuring, and managing Kubernetes clusters, essential for orchestrating containerized applications.',
                            'Certified in ethical hacking methodologies, including penetration testing and vulnerability assessment, to identify and mitigate security risks.'
                        ]),
                        'organization' => fake()->company(),
                        'achieved_at' => fake()->dateTimeBetween('-2 years', 'now'),
                        'certificate_url' => fake()->optional(0.6)->url(),
                        'image_path' => 'test/certificates/' . $image . '.png',
                    ]);
                }
            }
        }
    }
    
    private function createAwards($users)
    {
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            $image = fake()->numberBetween(1,3);
            // 40% chance of having awards, 1-2 awards
            if (fake()->boolean(70)) {
                $count = fake()->numberBetween(1, 2);
                for ($i = 0; $i < $count; $i++) {
                    Award::create([
                        'user_id' => $user->id,
                        'title' => fake()->randomElement([
                            'Employee of the Year Award',
                            'Innovation Excellence Award',
                            'Outstanding Contributor Award',
                            'Team Leadership Award',
                            'Customer Service Champion',
                            'Rising Star Award',
                            'President\'s Award for Excellence',
                            'Patent Achievement Award',
                            'Community Impact Award',
                            'Sales Achievement Award'
                        ]),
                        'description' => fake()->randomElement([
                            'Awarded for exceptional performance and dedication throughout the year, consistently exceeding expectations and contributing significantly to company goals.',
                            'Recognized for pioneering a groundbreaking solution or process that significantly improved efficiency, reduced costs, or created new opportunities.',
                            'Acknowledged for consistent high-quality work, proactive problem-solving, and positive influence on team morale and productivity.',
                            'Presented to an individual who demonstrated exemplary leadership, fostered a collaborative environment, and guided their team to achieve remarkable results.',
                            'Honors an individual who consistently provided outstanding service, built strong customer relationships, and went above and beyond to ensure customer satisfaction.',
                            'Celebrates a new or junior employee who quickly demonstrated significant potential, made substantial contributions, and showed exceptional growth within the organization.',
                            'A prestigious award given for sustained superior performance, innovation, and leadership that had a profound and lasting positive impact on the organization.',
                            'Awarded to an inventor or team for securing a new patent, recognizing their significant contribution to intellectual property and technological advancement.',
                            'Recognizes an individual or team for their exceptional efforts in community service, social responsibility, or making a positive difference beyond their professional duties.',
                            'Presented to an individual who achieved exceptional sales results, surpassed targets, and demonstrated outstanding client acquisition and retention skills.'
                        ]),
                        'organization' => fake()->company(),
                        'achieved_at' => fake()->dateTimeBetween('-2 years', 'now'),
                        'image_path' => 'test/awards/' . $image . '.png',
                        'certificate_url' => fake()->optional(0.6)->url(),
                    ]);
                }
            }
        }
    }
    
    private function createAchievements($users)
    {
        $achievements = collect();
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            // Each user gets 2-6 achievements
            $count = fake()->numberBetween(2, 6);
            for ($i = 0; $i < $count; $i++) {
                $achievement = Achievement::factory()->create(['user_id' => $user->id]);
                $achievements->push($achievement);
            }
        }
        
        return $achievements;
    }
    
    private function createAchievementInteractions($achievements, $users)
    {
        $studentAlumniUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        foreach ($achievements as $achievement) {
            // Random users like achievements (20-80% of users)
            $likers = $studentAlumniUsers->random(fake()->numberBetween(
                (int)($studentAlumniUsers->count() * 0.1), 
                (int)($studentAlumniUsers->count() * 0.4)
            ));
            
            foreach ($likers as $liker) {
                AchievementLike::create([
                    'achievement_id' => $achievement->id,
                    'user_id' => $liker->id,
                ]);
            }
            
            // Random users comment on achievements (5-20% of users)
            $commenters = $studentAlumniUsers->random(fake()->numberBetween(1, (int)($studentAlumniUsers->count() * 0.2)));
            
            foreach ($commenters as $commenter) {
                AchievementComment::create([
                    'achievement_id' => $achievement->id,
                    'user_id' => $commenter->id,
                    'content' => fake()->randomElement([
                        'Congratulations on this amazing achievement!',
                        'This is truly inspiring, well done!',
                        'Fantastic work! Keep up the great effort.',
                        'So proud to see your progress and success!',
                        'This achievement is a testament to your hard work.',
                        'Absolutely brilliant! What an accomplishment.',
                        'You\'ve set a new bar with this, incredible!',
                        'Remarkable dedication, truly deserved!',
                        'This motivates me to push harder, thank you!',
                        'A huge milestone! Celebrate your success!',
                    ]),
                ]);
            }
            
            // Update counts
            $achievement->update([
                'like_count' => $achievement->likes()->count(),
                'comment_count' => $achievement->comments()->count(),
            ]);
        }
    }
    
    private function createAlumniServices($users)
    {
        $alumniUsers = $users->filter(function ($user) {
            return $user->hasRole(['alumni']) && !$user->isRejected();
        });
        
        foreach ($alumniUsers as $user) {
            // 60% chance of providing alumni services, 1-2 services
            if (fake()->boolean(60)) {
                $count = fake()->numberBetween(1, 2);
                for ($i = 0; $i < $count; $i++) {
                    $evaluation = fake()->optional(0.4)->randomElement(['positive', 'neutral', 'negative']);
                    $feedback = null;
                    
                    if ($evaluation !== null) {
                        $feedback = fake()->randomElement([
                            'The service provided was excellent and highly beneficial. I gained valuable insights.',
                            'Very helpful session. The mentor was knowledgeable and supportive.',
                            'I found the career guidance workshop incredibly useful for my job search.',
                            'The web development course was well-structured and easy to follow. Highly recommend!',
                            'Good advice, but I would have preferred more specific examples related to my field.',
                            'The consultation session was informative, though it ran a bit short.',
                            'Neutral experience. Some parts were helpful, others less relevant to my needs.',
                            'The technical mentoring was great, very hands-on and practical.',
                            'I appreciate the effort, but the content of the course was too basic for my level.',
                            'The service did not meet my expectations. I hoped for more in-depth support.',
                        ]);
                    }

                    AlumniService::create([
                        'alumni_id' => $user->id,
                        'title' => fake()->randomElement([
                            'Business Strategy',
                            'Advanced Python for Data Science',
                            'Mobile App Monetization Strategies',
                            'PHP',
                            'SQL',
                            'Node.js',
                            'React Native',
                            'Digital Marketing',
                            'Cybersecurity for Startups',
                            'Cloud Computing Fundamentals',
                            'AI in Healthcare Seminar',
                            'Blockchain Development Course',
                            'UI/UX Design Principles',
                            'Project Management Certification Prep',
                            'Leadership Skills',
                            'Financial Literacy for Professionals',
                            'Public Speaking & Presentation Skills',
                            'Networking for Career Growth',
                            'Sustainable Business Practices'
                        ]),
                        'description' => fake()->randomElement([
                            'A comprehensive workshop providing alumni with tools and frameworks for effective business strategy development and implementation.',
                            'An intensive course designed to deepen understanding and practical application of Python in data science, including advanced libraries and techniques.',
                            'Explore various strategies for monetizing mobile applications, including in-app purchases, subscriptions, and advertising models.',
                            'A masterclass covering essential digital marketing techniques, including SEO, social media marketing, and content strategy.',
                            'Learn critical cybersecurity measures and best practices specifically tailored for new and growing startup businesses.',
                            'Understand the core concepts of cloud computing, including major service models and practical applications across leading cloud platforms.',
                            'A seminar focusing on the transformative role of Artificial Intelligence in the healthcare industry, from diagnostics to patient care.',
                            'Dive into the world of blockchain, learning how to develop decentralized applications and smart contracts on various blockchain platforms.',
                            'Master the fundamental principles of User Interface (UI) and User Experience (UX) design to create intuitive and engaging digital products.',
                            'Prepare for industry-recognized project management certifications with this course covering methodologies, tools, and best practices.',
                            'Develop essential leadership qualities and effective team management skills through interactive sessions and practical exercises.',
                            'Gain crucial knowledge in personal and business financial planning, investment basics, and wealth management strategies.',
                            'Enhance your communication abilities with practical techniques for confident public speaking and impactful presentations.',
                            'Build and leverage professional networks effectively for career advancement, mentorship opportunities, and business collaborations.',
                            'Discover and implement environmentally and socially responsible business practices for long-term sustainability and positive impact.'
                        ]),
                        'service_type' => fake()->randomElement(['business_session', 'course_teaching']),
                        'feedback' => $feedback,
                        'evaluation' => $evaluation,
                    ]);
                }
            }
        }
    }
    
    private function createArticles($users)
    {
        $articles = collect();
        $authors = $users->filter(function ($user) {
            return $user->hasRole(['admin', 'staff']);
        });
        
        foreach ($authors as $author) {
            // 60% chance of writing articles, 1-6 articles
            if (fake()->boolean(60)) {
                $count = fake()->numberBetween(1, 6);
                for ($i = 0; $i < $count; $i++) {
                    $article = Article::factory()->create(['author_id' => $author->id]);
                    $articles->push($article);
                }
            }
        }
        
        return $articles;
    }
    
    private function createArticleLikes($articles, $users)
    {
        $studentAlumniUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']);
        });
        foreach ($articles as $article) {
            // Random users like articles
            $likers = $studentAlumniUsers->random(fake()->numberBetween(0, (int)($studentAlumniUsers->count() * 0.3)));
            
            foreach ($likers as $liker) {
                ArticleLike::create([
                    'article_id' => $article->id,
                    'user_id' => $liker->id,
                ]);
            }
            
            // Update likes count
            $article->update(['like_count' => $article->likes()->count()]);
        }
    }
    
    private function createAvailableJobs($users)
    {
        $jobs = collect();
        $companyUsers = $users->filter(function ($user) {
            return $user->hasRole('company') && !$user->isRejected();
        });
        
        foreach ($companyUsers as $company) {
            // Each company posts 2-8 jobs
            $count = fake()->numberBetween(2, 12);
            for ($i = 0; $i < $count; $i++) {
                $job = AvailableJob::factory()->create(['company_id' => $company->id]);
                $jobs->push($job);
            }
        }
        
        return $jobs;
    }
    
    private function createJobSkills($jobs)
    {
        $skills = Skill::all();
        
        foreach ($jobs as $job) {
            // Each job requires 3-6 skills
            $jobSkills = $skills->random(fake()->numberBetween(3, 6));
            foreach ($jobSkills as $skill) {
                JobSkill::create([
                    'job_id' => $job->id,
                    'skill_id' => $skill->id,
                    'is_required' => fake()->boolean(70),
                ]);
            }
        }
    }
    
    private function createJobApplications($jobs, $users)
    {
        $applicants = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($jobs as $job) {
            // Each job gets 0-15 applications
            $applicationCount = fake()->numberBetween(0, 15);
            $jobApplicants = $applicants->random(min($applicationCount, $applicants->count()));
            
            foreach ($jobApplicants as $applicant) {
                JobApplication::create([
                    'job_id' => $job->id,
                    'user_id' => $applicant->id,
                    'cover_letter' => fake()->randomElement([
                        'I am writing to express my keen interest in the advertised position. With my proven experience in software development and project management, I am confident I can contribute significantly to your team. My skills in full-stack development and agile methodologies are well-suited for this role.',
                        'My passion for innovative technology and my strong background in data analysis make me an ideal candidate for this opportunity. I am particularly drawn to companies that prioritize cutting-edge solutions and continuous learning. I bring a strong analytical mindset and a commitment to excellence.',
                        'Having closely followed this company\'s impactful work in the tech industry, I am excited by the opportunity to join your engineering team. My skills in cloud infrastructure and cybersecurity align perfectly with your requirements. I am eager to apply my expertise to challenging projects.',
                        'As a highly motivated and results-oriented professional with several years of experience, I am eager to apply my expertise to this position. My ability to drive successful outcomes and optimize workflows will be a valuable asset. I am a proactive problem-solver with a strong work ethic.',
                        'I was excited to discover this opening. My academic background combined with practical experience has prepared me to excel in this challenging role. I possess a strong foundation in core computer science principles and a dedication to lifelong learning.',
                        'With a strong foundation in technical architecture and a knack for problem-solving, I am well-suited for this role. I am particularly impressed by the innovative solutions your organization provides. I am a creative thinker who enjoys tackling complex challenges.',
                        'My experience in leading development projects and my proficiency in modern programming languages make me a strong contender. I am passionate about creating impactful software and fostering team collaboration. I excel in dynamic and fast-paced environments.',
                        'I am writing to apply for this position. My diverse skill set, including front-end design, backend development, and database management, enables me to approach complex challenges with innovative solutions, contributing to measurable success. I am a versatile developer ready for new challenges.',
                        'The opportunity to join this company deeply resonates with my career aspirations. My dedication to high-quality code and my track record in delivering robust applications align perfectly with your company culture. I am committed to building exceptional products.',
                        'My proactive approach to system optimization and my ability to quickly adapt to new technologies are qualities I believe would greatly benefit your team. I am eager to learn and grow within your dynamic environment. I am a quick learner and thrive on new challenges.',
                        'I am confident that my analytical skills and problem-solving capabilities, honed over years in the software industry, make me an excellent fit for this vacancy. I thrive in environments that encourage continuous learning and innovation. I am always looking for ways to improve processes and deliver value.',
                        'This position stands out as a perfect match for my career goals and technical proficiencies. I am enthusiastic about contributing to your mission through my dedication and skills. I am a dedicated professional eager to make a significant impact.',
                        'My commitment to delivering high-quality results and my experience in scalable system design align perfectly with the demands of this role. I am keen to bring my analytical skills and creative problem-solving to your team. I am passionate about building efficient and reliable systems.',
                        'I am impressed by this company\'s reputation for innovation and am eager to contribute to your continued success. My background in software engineering has equipped me with the necessary tools to make an immediate impact. I am excited to be part of a forward-thinking organization.',
                        'This application is to express my strong interest in the role. My unique blend of technical expertise and interpersonal skills allows me to contribute effectively to both technical and collaborative aspects of a project. I am a team player who can also work independently.'
                    ]) . "\n\n" . fake()->paragraphs(3, true),
                    'status' => fake()->randomElement(['applied', 'reviewed', 'interviewed', 'hired', 'rejected']),
                    'cv_path' => fake()->optional(0.8)->filePath(),
                ]);
            }
            
            // Update applications count
            $job->update(['applications_count' => $job->applications()->count()]);
        }
    }
    
    private function createConnections($users)
    {
        $studentUsers = $users->filter(function ($user) {
            return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
        });
        
        foreach ($studentUsers as $user) {
            // Each user sends 2-8 connection requests
            $connectionCount = fake()->numberBetween(2, 8);
            $potentialConnections = $users->filter(function ($user) {
                    return $user->hasRole(['student', 'alumni']) && !$user->isRejected();
                })->where('id', '!=', $user->id)->random(
                min($connectionCount, $users->count() - 1)
            );
            
            foreach ($potentialConnections as $connection) {
                Connection::create([
                    'requester_id' => $user->id,
                    'addressee_id' => $connection->id,
                    'status' => fake()->randomElement(['pending', 'accepted', 'declined']),
                ]);
            }
        }
    }
    
    private function createNotifications($users)
    {
        foreach ($users as $user) {
            // Each user gets 3-10 notifications
            $count = fake()->numberBetween(3, 10);
            for ($i = 0; $i < $count; $i++) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => fake()->randomElement([
                        'job_application',
                        'connection_request',
                        'achievement_liked',
                        'article_published',
                        'job_posted',
                        'profile_viewed'
                    ]),
                    'title' => fake()->sentence(4),
                                    'message' => fake()->sentence(8),
                    'data' => [
                        'action_url' => fake()->optional(0.7)->url(),
                        'related_id' => fake()->optional(0.5)->numberBetween(1, 100),
                    ],
                    'is_read' => fake()->boolean(40),
                    'read_at' => fake()->optional(0.4)->dateTimeBetween('-1 month', 'now'),
                    'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function printSummary()
    {
        $this->command->info("\n📊 SEEDING SUMMARY:");
        $this->command->info("👥 Users: " . User::count());
        $this->command->info("📝 User Profiles: " . UserProfile::count());
        $this->command->info("🏢 Company Profiles: " . CompanyProfile::count());
        $this->command->info("👨‍💼 Staff Profiles: " . StaffProfile::count());
        $this->command->info("📚 Skills: " . Skill::count());
        $this->command->info("🛠️ User Skills: " . UserSkill::count());
        $this->command->info("🎓 Education Records: " . Education::count());
        $this->command->info("💼 Work Experiences: " . WorkExperience::count());
        $this->command->info("🚀 Projects: " . Project::count());
        $this->command->info("📜 Certificates: " . Certificate::count());
        $this->command->info("🏆 Awards: " . Award::count());
        $this->command->info("🎯 Achievements: " . Achievement::count());
        $this->command->info("❤️ Achievement Likes: " . AchievementLike::count());
        $this->command->info("💬 Achievement Comments: " . AchievementComment::count());
        $this->command->info("🎓 Alumni Services: " . AlumniService::count());
        $this->command->info("📰 Articles: " . Article::count());
        $this->command->info("👍 Article Likes: " . ArticleLike::count());
        $this->command->info("💼 Available Jobs: " . AvailableJob::count());
        $this->command->info("🔧 Job Skills: " . JobSkill::count());
        $this->command->info("📋 Job Applications: " . JobApplication::count());
        $this->command->info("🤝 Connections: " . Connection::count());
        $this->command->info("🔔 Notifications: " . Notification::count());
        $this->command->info("\n✨ All test data has been created successfully!");
    }
}
