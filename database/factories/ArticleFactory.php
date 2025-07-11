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
            'Cloud Computing Essentials',
            'Advanced JavaScript Concepts',
            'DevOps for Developers',
            'Mastering PostgreSQL',
            'Frontend Performance Optimization',
            'Backend Security Best Practices',
            'GraphQL vs REST: A Deep Dive',
            'Container Orchestration with Kubernetes',
            'Serverless Architectures Explained',
            'Data Structures and Algorithms in Python',
            'Progressive Web Apps (PWAs) in Action'
        ];

        $articleContents = [
            'This article provides a comprehensive guide to understanding and implementing the latest features in Laravel 11, including new routing, testing, and performance enhancements. Dive deep into practical examples and best practices for building robust web applications.' .
            "\n\n" . 'Laravel 11 introduces a streamlined application structure, making it even easier for developers to get started and maintain their projects. Key improvements include a simplified `bootstrap/app.php` file, a new `config/` directory structure, and enhanced testing utilities that promote a more efficient development workflow.',
            'Explore the essential best practices for developing high-performance and maintainable React applications in 2024. This includes state management, component architecture, hooks usage, and optimization techniques for a smooth user experience.' .
            "\n\n" . 'In 2024, React development emphasizes performance and scalability. This guide delves into optimizing component rendering with `React.memo` and `useCallback`, efficient state management using Context API or Redux Toolkit, and structuring large applications for maintainability and collaboration. We also cover modern styling solutions and accessibility considerations.',
            'Learn how to design and build scalable, secure, and efficient APIs using Node.js and Express.js. This content covers authentication, database integration, error handling, and deployment strategies for production-ready services.' .
            "\n\n" . 'Building robust APIs is crucial for modern applications. This section explores best practices for API design, including RESTful principles, versioning, and proper error handling. We also discuss implementing secure authentication mechanisms like JWT, integrating with various databases, and deploying Node.js applications to cloud platforms for high availability and performance.',
            'An introductory journey into the world of Machine Learning, covering fundamental concepts, popular algorithms like linear regression and decision trees, and practical applications using Python and scikit-learn. Perfect for beginners.' .
            "\n\n" . 'Machine Learning is transforming industries, and this article serves as your entry point. We break down complex concepts into digestible insights, covering supervised and unsupervised learning, model evaluation metrics, and data preprocessing techniques. Practical examples using Python and the scikit-learn library will help you build your first ML models.',
            'A step-by-step tutorial for beginners to get started with Docker. Understand containerization, learn to create and manage Docker images and containers, and orchestrate multi-container applications with Docker Compose.' .
            "\n\n" . 'Docker has revolutionized software deployment. This tutorial guides you from basic Docker commands to building custom images, managing containers, and linking services with Docker Compose. You will learn how to containerize your applications for consistent environments across development, testing, and production, simplifying deployment pipelines.',
            'Discover the latest and most effective techniques for writing modern CSS, including utility-first CSS with Tailwind CSS, CSS-in-JS, and advanced layout techniques like CSS Grid and Flexbox for responsive designs.' .
            "\n\n" . 'Modern CSS offers powerful tools for creating beautiful and responsive user interfaces. This article explores the benefits of utility-first frameworks like Tailwind CSS for rapid development, the flexibility of CSS-in-JS solutions, and advanced layout techniques using CSS Grid and Flexbox to build complex and adaptive designs efficiently.',
            'Optimize your database performance with these expert tips and tricks. This article covers indexing strategies, query optimization, proper schema design, and caching mechanisms for both SQL and NoSQL databases.' .
            "\n\n" . 'Database performance is critical for application responsiveness. This section provides actionable advice on creating effective indexes, optimizing complex SQL queries, and denormalizing data when appropriate. We also discuss the role of caching layers like Redis and memcached in reducing database load and improving retrieval times for frequently accessed data.',
            'Stay updated with the newest trends in mobile app development for iOS and Android. Explore topics like cross-platform frameworks (Flutter, React Native), AI integration, and the future of mobile user interfaces.' .
            "\n\n" . 'The mobile landscape is constantly evolving. This article highlights the rising popularity of cross-platform development with Flutter and React Native, enabling wider reach with a single codebase. We also touch upon the increasing integration of AI for personalized user experiences and the emerging trends in mobile UI/UX design.',
            'Understand the core principles of cybersecurity and how to protect your applications and data from common threats. Learn about secure coding practices, network security, and incident response fundamentals.' .
            "\n\n" . 'Cybersecurity is a paramount concern in the digital age. This content educates you on common vulnerabilities such as SQL injection and XSS, and how to mitigate them through secure coding practices. We also cover network security fundamentals, including firewalls and intrusion detection systems, and outline basic steps for incident response planning.',
            'A guide to the essential concepts of cloud computing, including IaaS, PaaS, and SaaS models. Explore major cloud providers like AWS, Azure, and Google Cloud, and understand their services for scalable infrastructure.' .
            "\n\n" . 'Cloud computing provides unparalleled flexibility and scalability. This guide demystifies key cloud service models (IaaS, PaaS, SaaS) and their respective use cases. We provide an overview of the core services offered by leading cloud providers like AWS EC2, Azure App Service, and Google Cloud Functions, helping you choose the right architecture for your needs.'
        ];

        $image = $this->faker->randomNumber(1,15);

        return [
            'title' => $this->faker->randomElement($techTopics),
            'content' => $this->faker->randomElement($articleContents) . "\n\n" . $this->faker->paragraphs(4, true),
            'featured_image' => 'test/articles/a' . $image . '.png',
            'external_link' => $this->faker->optional(0.3)->url(),
            'status' => $this->faker->randomElement(['published', 'draft']),
            'published_at' => $this->faker->optional(0.8)->dateTimeBetween('-6 months', 'now'),
            'like_count' => $this->faker->numberBetween(0, 100),
        ];
    }
}
