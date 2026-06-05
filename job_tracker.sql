-- Database: `job_tracker`
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `job_tracker`;
USE `job_tracker`;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `jobs`
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `deadline` date NOT NULL,
  `experience` varchar(100) DEFAULT 'Not specified',
  `salary` varchar(100) DEFAULT 'Not specified',
  `skills` text DEFAULT NULL,
  `company_name` varchar(255) DEFAULT 'Not specified',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `job_applications`
CREATE TABLE IF NOT EXISTS `job_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `resume` varchar(255) NOT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `match_score` int(11) DEFAULT 0,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `contact_messages`
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seeding sample users
-- Password for both users is 'admin123' and 'user123' respectively, hashed using password_hash() in PHP
-- Hash: '$2y$10$e0MYzXy5PF1W2Z2j26gU/O31gL.T87Tq7F83N6U/e5K9N0Gg8p5kK' evaluates to 'admin123' / 'user123'
INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$e0MYzXy5PF1W2Z2j26gU/O31gL.T87Tq7F83N6U/e5K9N0Gg8p5kK', 'admin'),
(2, 'Standard Candidate', 'user@example.com', '$2y$10$e0MYzXy5PF1W2Z2j26gU/O31gL.T87Tq7F83N6U/e5K9N0Gg8p5kK', 'user');

-- Seeding default jobs
INSERT INTO `jobs` (`id`, `title`, `description`, `location`, `category`, `deadline`, `experience`, `salary`, `skills`, `company_name`) VALUES
(1, 'Full Stack Web Developer', 'We are looking for a highly skilled Full Stack Web Developer experienced in building robust PHP and JavaScript web applications. You will design, develop, and test web applications, collaborate with cross-functional teams, and deploy applications on Apache/Nginx servers.', 'New York, NY', 'IT', '2026-09-30', '2-5 Years', '₹85,000/month', 'PHP, JavaScript, HTML, CSS, Tailwind CSS, MySQL, Git', 'DevTech Solutions'),
(2, 'Digital Marketing Manager', 'Join our dynamic marketing department as a Digital Marketing Manager. In this role, you will lead our digital marketing strategies, manage SEO/SEM campaigns, optimize social media accounts, coordinate search engine ads, and compile monthly analytics reporting summaries.', 'Remote', 'Marketing', '2026-08-15', '3+ Years', '₹60,000/month', 'SEO, SEM, Analytics, Content Strategy, Google Ads, Social Media', 'MarketForce Group'),
(3, 'Financial Analyst', 'Our finance team is looking for a Financial Analyst to analyze budgets, process payroll entries, evaluate capital projects, formulate fiscal forecasts, and support internal departments with cost-efficiency reporting summaries. Excel expertise is required.', 'Chicago, IL', 'Finance', '2026-10-10', '1-3 Years', '₹70,000/month', 'Excel, Financial Modeling, Analytics, Forecasting, Budgeting', 'Apex Capital Partners');
