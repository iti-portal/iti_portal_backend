<?php

namespace Database\Factories;

use App\Models\AvailableJob;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailableJobFactory extends Factory
{
    protected $model = AvailableJob::class;

    public function definition(): array
    {
        $jobTitles = [
            'Senior PHP Developer', 'React Developer', 'Full Stack Developer',
            'Mobile App Developer', 'UI/UX Designer', 'Data Scientist',
            'DevOps Engineer', 'Product Manager', 'Quality Assurance Engineer',
            'Backend Developer', 'Frontend Developer', 'System Administrator'
        ];

        $jobTypes = ['full_time', 'part_time', 'contract', 'internship'];
        $experienceLevels = ['entry', 'junior', 'mid', 'senior'];
        $statuses = ['active', 'closed', 'paused'];

        return [
            'title' => $this->faker->randomElement($jobTitles),
            'description' => $this->faker->randomElement([
                'Develop, test, and deploy robust web applications using PHP and Laravel. Collaborate with cross-functional teams to define, design, and ship new features. Ensure the performance, quality, and responsiveness of applications. Maintain code quality, organization, and automatization.',
                'Build and maintain user interfaces for web applications using React.js. Work closely with designers to translate UI/UX designs into high-quality code. Optimize components for maximum performance across a vast array of web-capable devices and browsers.',
                'Design, develop, and maintain both front-end and back-end components of web applications. Implement new features, improve existing ones, and ensure seamless integration between different parts of the system. Participate in code reviews and contribute to architectural decisions.',
                'Develop and maintain mobile applications for iOS and Android platforms. Collaborate with product and design teams to create intuitive and engaging user experiences. Ensure optimal performance and responsiveness across various mobile devices.',
                'Create intuitive and aesthetically pleasing user interfaces and experiences for web and mobile applications. Conduct user research, develop wireframes and prototypes, and work closely with development teams to ensure design implementation accuracy.',
                'Apply advanced statistical and machine learning techniques to analyze complex datasets. Develop predictive models, perform data visualization, and provide actionable insights to drive business decisions. Work with large-scale data processing tools and platforms.',
                'Implement and manage continuous integration and continuous delivery (CI/CD) pipelines. Automate infrastructure provisioning, deployment processes, and system monitoring. Ensure high availability, scalability, and security of production environments.',
                'Define and execute the product roadmap for software products. Conduct market research, gather customer requirements, and prioritize features based on business value. Work closely with engineering, design, and marketing teams to ensure successful product delivery.',
                'Develop and execute test plans, test cases, and test scripts for software applications. Identify, document, and track bugs, and work with development teams to ensure timely resolution. Perform various types of testing, including functional, regression, and performance testing.',
                'Design, develop, and maintain server-side logic, databases, and APIs for web applications. Focus on building robust, scalable, and secure back-end systems. Collaborate with front-end developers to integrate user-facing elements with server-side logic.',
                'Implement responsive and interactive user interfaces using modern web technologies. Collaborate with UI/UX designers to translate mockups into functional web pages. Optimize front-end performance and ensure cross-browser compatibility.',
                'Install, configure, and maintain server hardware and software, including operating systems, databases, and applications. Monitor system performance, troubleshoot issues, and implement security measures to protect data and infrastructure.'
            ]),
            'requirements' => $this->faker->randomElement([
                'Strong proficiency in PHP, Laravel framework, and MySQL. Experience with front-end technologies such as JavaScript, HTML, and CSS. Solid understanding of OOP, design patterns, and RESTful API development.',
                'Proficiency in React.js, Redux, and JavaScript (ES6+). Experience with modern front-end build pipelines and tools. Familiarity with RESTful APIs and asynchronous request handling. Strong understanding of responsive design principles.',
                'Proficiency in at least one front-end framework (e.g., React, Vue, Angular) and one back-end language/framework (e.g., Node.js, Python/Django, PHP/Laravel). Experience with database design and management. Ability to work across the full software development life cycle.',
                'Experience with native mobile development (Swift/Kotlin) or cross-platform frameworks (Flutter/React Native). Strong understanding of mobile UI/UX principles and performance optimization. Familiarity with mobile app deployment processes.',
                'Proven experience in UI/UX design, including wireframing, prototyping, and user testing. Proficiency in design tools like Figma, Sketch, or Adobe XD. Strong understanding of user-centered design principles and responsive design.',
                'Solid background in statistics, mathematics, and machine learning algorithms. Proficiency in Python or R, and libraries like TensorFlow, PyTorch, or scikit-learn. Experience with data preprocessing, feature engineering, and model evaluation.',
                'Experience with Linux systems, scripting (Bash, Python), and cloud platforms (AWS, Azure, GCP). Proficiency in CI/CD tools (Jenkins, GitLab CI) and containerization (Docker, Kubernetes). Understanding of network protocols and system monitoring.',
                'Proven experience in product management, including defining product roadmaps, gathering requirements, and launching products. Strong analytical and problem-solving skills. Excellent communication and interpersonal abilities.',
                'Experience in software quality assurance, including developing test plans, test cases, and executing various types of tests. Familiarity with test automation tools and bug tracking systems. Strong attention to detail and problem-solving skills.',
                'Expertise in a back-end language (e.g., Python, Java, Node.js) and framework (e.g., Django, Spring Boot, Express.js). Strong knowledge of database systems (SQL/NoSQL) and API development. Understanding of security best practices.',
                'Proficiency in HTML5, CSS3, and JavaScript. Experience with front-end frameworks (e.g., React, Vue, Angular). Strong understanding of responsive design, cross-browser compatibility, and web performance optimization.',
                'Experience in managing and maintaining IT infrastructure, including servers, networks, and databases. Proficiency in operating systems (Linux, Windows Server) and scripting. Strong troubleshooting and problem-solving abilities.'
            ]),
            'job_type' => $this->faker->randomElement($jobTypes),
            'experience_level' => $this->faker->randomElement($experienceLevels),
            'salary_min' => $this->faker->numberBetween(5000, 15000),
            'salary_max' => $this->faker->numberBetween(15000, 50000),
            'application_deadline' => $this->faker->dateTimeBetween('now', '+3 months'),
            'status' => $this->faker->randomElement($statuses),
            'is_featured' => $this->faker->boolean(20),
            'is_remote' => $this->faker->boolean(40),
            'applications_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
