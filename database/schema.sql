-- IRONCORE Fitness & Gym Management System Schema
-- Compatibility: MySQL 5.7+ / MySQL 8.0 / MariaDB 10.2+

CREATE DATABASE IF NOT EXISTS `ironcore_gym` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ironcore_gym`;

-- 1. Users Table (Core Auth Table for Admin, Trainer, Member)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'trainer', 'member') NOT NULL DEFAULT 'member',
    `avatar` VARCHAR(255) NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Members Table (Member profiles linked to users)
CREATE TABLE IF NOT EXISTS `members` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `phone` VARCHAR(20) NULL,
    `emergency_contact` VARCHAR(100) NULL,
    `gender` ENUM('male', 'female', 'other') NULL,
    `dob` DATE NULL,
    `address` TEXT NULL,
    `join_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Trainers Table (Trainer profiles linked to users)
CREATE TABLE IF NOT EXISTS `trainers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `specialization` VARCHAR(150) NOT NULL,
    `experience_years` INT NOT NULL DEFAULT 0,
    `bio` TEXT NULL,
    `hourly_rate` DECIMAL(10,2) DEFAULT 0.00,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Membership Plans
CREATE TABLE IF NOT EXISTS `membership_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(100) NOT NULL,
    `tag` VARCHAR(50) NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `billing_cycle` ENUM('monthly', 'annual') NOT NULL DEFAULT 'monthly',
    `duration_days` INT NOT NULL DEFAULT 30,
    `description` TEXT NULL,
    `features` JSON NULL,
    `is_recommended` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Subscriptions
CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `plan_id` INT NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `auto_renew` TINYINT(1) DEFAULT 1,
    `status` ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`plan_id`) REFERENCES `membership_plans`(`id`) ON DELETE RESTRICT,
    INDEX `idx_sub_member` (`member_id`),
    INDEX `idx_sub_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Attendance Table
CREATE TABLE IF NOT EXISTS `attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `check_in_time` TIME NOT NULL,
    `check_out_time` TIME NULL,
    `status` ENUM('present', 'late', 'excused') DEFAULT 'present',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    INDEX `idx_attendance_date` (`date`),
    INDEX `idx_attendance_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Exercise Catalog
CREATE TABLE IF NOT EXISTS `exercise_catalog` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `muscle_group` VARCHAR(100) NOT NULL,
    `equipment` VARCHAR(100) NULL,
    `instructions` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Workout Plans
CREATE TABLE IF NOT EXISTS `workout_plans` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `trainer_id` INT NULL,
    `title` VARCHAR(150) NOT NULL,
    `goal` VARCHAR(255) NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Workout Plan Exercises
CREATE TABLE IF NOT EXISTS `workout_plan_exercises` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `plan_id` INT NOT NULL,
    `exercise_id` INT NOT NULL,
    `sets` INT NOT NULL DEFAULT 3,
    `reps` VARCHAR(50) NOT NULL DEFAULT '10-12',
    `rest_seconds` INT DEFAULT 60,
    `day_of_week` ENUM('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
    FOREIGN KEY (`plan_id`) REFERENCES `workout_plans`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`exercise_id`) REFERENCES `exercise_catalog`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Payments
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subscription_id` INT NULL,
    `member_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL DEFAULT 'UPI',
    `transaction_id` VARCHAR(100) NOT NULL UNIQUE,
    `status` ENUM('paid', 'pending', 'failed', 'refunded') DEFAULT 'paid',
    `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE,
    INDEX `idx_payment_trans` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Progress Logs
CREATE TABLE IF NOT EXISTS `progress_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `member_id` INT NOT NULL,
    `log_date` DATE NOT NULL,
    `weight_kg` DECIMAL(5,2) NULL,
    `body_fat_pct` DECIMAL(4,2) NULL,
    `chest_cm` DECIMAL(5,2) NULL,
    `waist_cm` DECIMAL(5,2) NULL,
    `biceps_cm` DECIMAL(5,2) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`member_id`) REFERENCES `members`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
