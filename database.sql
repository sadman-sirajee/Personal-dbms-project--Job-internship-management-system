-- ============================================================
-- Database: job_internship_db
-- Project: Job/Internship Management System
-- Technology: PHP & MySQL
-- ============================================================

CREATE DATABASE IF NOT EXISTS `job_internship_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `job_internship_db`;

-- ------------------------------------------------------------
-- Table structure for table `users`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) DEFAULT 'User',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Dumping data for table `users`
-- Password for all seed users is: admin123 (for Admin) or user123 (for Users)
-- Admin: admin@gmail.com / admin123
-- ------------------------------------------------------------
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$keWBExC.U2Q/hUnFeHkDi.fC594RJRZRa6FT4O5TFGYg0YqpqZqdi', 'Admin', '2026-09-01 10:00:00'),
(2, 'Rahim Ahmed', 'rahim@gmail.com', '$2y$10$keWBExC.U2Q/hUnFeHkDi.fC594RJRZRa6FT4O5TFGYg0YqpqZqdi', 'User', '2026-09-02 11:30:00'),
(3, 'Karim Ullah', 'karim@gmail.com', '$2y$10$keWBExC.U2Q/hUnFeHkDi.fC594RJRZRa6FT4O5TFGYg0YqpqZqdi', 'User', '2026-09-03 14:15:00'),
(4, 'Fatima Begum', 'fatima@gmail.com', '$2y$10$keWBExC.U2Q/hUnFeHkDi.fC594RJRZRa6FT4O5TFGYg0YqpqZqdi', 'User', '2026-09-04 09:45:00');

-- ------------------------------------------------------------
-- Table structure for table `jobs`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `company` VARCHAR(150) NOT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `type` VARCHAR(20) NOT NULL,
    `description` TEXT,
    `deadline` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Dumping data for table `jobs`
-- ------------------------------------------------------------
INSERT INTO `jobs` (`id`, `title`, `company`, `location`, `type`, `description`, `deadline`, `created_at`) VALUES
(1, 'Software Engineer', 'ABC Ltd', 'Dhaka', 'Internship', 'We are looking for an energetic Software Engineer Intern to assist in building web applications and RESTful APIs using PHP, MySQL, and JavaScript.', '2026-10-30', '2026-09-01 10:30:00'),
(2, 'Data Analyst', 'XYZ Ltd', 'Chittagong', 'Job', 'Responsible for collecting, cleaning, and interpreting data sets, generating business reports, and building analytical dashboards.', '2026-11-15', '2026-09-01 11:00:00'),
(3, 'ML Engineer Intern', 'Tech BD', 'Remote', 'Internship', 'Work with machine learning frameworks, data preprocessing pipelines, computer vision models, and natural language processing tasks.', '2026-11-20', '2026-09-02 09:15:00'),
(4, 'Frontend Web Developer', 'SoftCraft Solutions', 'Sylhet', 'Job', 'Design and implement responsive, high-performance user interfaces using HTML5, CSS3, Bootstrap, and modern JavaScript libraries.', '2026-12-01', '2026-09-02 14:00:00'),
(5, 'Quality Assurance Tester', 'Apex Technologies', 'Dhaka', 'Internship', 'Assist the QA department in automated and manual software testing, documenting bug reports, and validating system quality metrics.', '2026-11-05', '2026-09-03 16:20:00'),
(6, 'Backend PHP Developer', 'InnovateX Global', 'Remote', 'Job', 'Develop and maintain scalable backend web services, integrate MySQL databases, and write secure, efficient, and maintainable PHP code.', '2026-12-15', '2026-09-04 08:30:00');

-- ------------------------------------------------------------
-- Table structure for table `applications`
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `applications`;
CREATE TABLE `applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `cover_note` TEXT DEFAULT NULL,
    `resume_url` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('Pending', 'Reviewed', 'Accepted', 'Rejected') DEFAULT 'Pending',
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_job` (`user_id`, `job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Dumping data for table `applications`
-- ------------------------------------------------------------
INSERT INTO `applications` (`id`, `user_id`, `job_id`, `phone`, `cover_note`, `resume_url`, `status`, `applied_at`) VALUES
(1, 2, 1, '+880 1711-223344', 'Passionate computer science graduate with deep interest in web backend architecture and PHP/MySQL systems.', 'https://github.com/rahim-dev', 'Pending', '2026-09-05 10:15:00'),
(2, 3, 2, '+880 1812-998877', 'Experienced in SQL, statistical modeling and business analysis. Excited to contribute to data workflows.', 'https://linkedin.com/in/karim-analyst', 'Reviewed', '2026-09-05 14:30:00');

