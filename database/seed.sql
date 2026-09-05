-- IRONCORE Seed Data
USE `ironcore_gym`;

-- Clear existing data in dependency order.
-- DELETE is used instead of TRUNCATE because MariaDB rejects TRUNCATE
-- on a table referenced by a foreign key, even when FK checks are disabled.
DELETE FROM `progress_logs`;
DELETE FROM `payments`;
DELETE FROM `workout_plan_exercises`;
DELETE FROM `workout_plans`;
DELETE FROM `exercise_catalog`;
DELETE FROM `attendance`;
DELETE FROM `subscriptions`;
DELETE FROM `membership_plans`;
DELETE FROM `trainers`;
DELETE FROM `members`;
DELETE FROM `users`;

-- 1. Insert Initial Admin, Trainer, Active Member, Inactive Member, and Suspended Member Users
-- Credentials for Testing Phase 2.1 Authentication:
-- Admin:     admin@ironcore.com   / Admin@123
-- Trainer:   marcus@ironcore.com  / Trainer@123
-- Member:    alex@gmail.com       / Member@123
-- Suspended: suspended@gmail.com  / Member@123
-- Inactive:  inactive@gmail.com   / Member@123

INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@ironcore.com', '$2y$10$PSDVDniqt5886aTezjeli.6VGpJ2oJBEFhpcfCFXhEtzq/4B4CR3y', 'admin', 'active'),
(2, 'Marcus Vance', 'marcus@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(3, 'Elena Rostova', 'elena@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(4, 'Alex Rivera', 'alex@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'active'),
(5, 'David Black', 'suspended@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'suspended'),
(6, 'Sarah Connor', 'inactive@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'inactive');

-- 2. Insert Profiles
INSERT INTO `trainers` (`id`, `user_id`, `specialization`, `experience_years`, `bio`, `hourly_rate`) VALUES
(1, 2, 'Strength & Hypertrophy', 8, 'Senior strength coach specializing in Olympic lifting and hypertrophy programming.', 1500.00),
(2, 3, 'Functional Conditioning & Rehab', 6, 'Certified athletic trainer focused on mobility, fat loss, and core stability.', 1200.00);

INSERT INTO `members` (`id`, `user_id`, `phone`, `emergency_contact`, `gender`, `dob`, `address`, `join_date`) VALUES
(1, 4, '+91 9876543210', 'Maria Rivera (+91 9876543211)', 'male', '1998-05-14', 'B-402 Horizon Towers, Downtown', '2026-01-15');

-- 3. Membership Plans
INSERT INTO `membership_plans` (`id`, `title`, `tag`, `price`, `billing_cycle`, `duration_days`, `description`, `features`, `is_recommended`, `status`) VALUES
(1, 'STARTER', 'Essential Access', 999.00, 'monthly', 30, 'Perfect for self-motivated fitness enthusiasts needing basic facility access.', '["Full Gym Floor Access", "Digital Attendance Tracking", "Member Dashboard", "Locker Room Access"]', 0, 'active'),
(2, 'PRO', 'Most Popular', 1999.00, 'monthly', 30, 'Comprehensive package with guided workout programs and trainer support.', '["Everything in Starter", "Personalized Workout Plans", "Trainer Assistance", "Progress & Metrics Tracking", "Group Fitness Classes"]', 1, 'active'),
(3, 'ELITE', 'VIP Experience', 2999.00, 'monthly', 30, 'All-inclusive premium experience with dedicated 1-on-1 personal training.', '["Everything in Pro", "1-on-1 Personal Trainer", "Priority Session Booking", "Nutritional Consultation", "Complimentary Recovery Drinks"]', 0, 'active');

-- 4. Initial Active Subscription
INSERT INTO `subscriptions` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `auto_renew`, `status`) VALUES
(1, 1, 2, '2026-08-01', '2026-08-31', 1, 'active');

-- 5. Exercise Catalog Seed Data
INSERT INTO `exercise_catalog` (`id`, `name`, `category`, `muscle_group`, `equipment`, `instructions`) VALUES
(1, 'Barbell Back Squat', 'Strength', 'Quadriceps, Glutes', 'Barbell, Rack', 'Keep chest elevated, break at hips and knees, squat below parallel.'),
(2, 'Incline Dumbbell Bench Press', 'Strength', 'Chest, Anterior Deltoids', 'Dumbbells, Bench', 'Set bench at 30 degrees, press dumbbells overhead in controlled arc.'),
(3, 'Conventional Deadlift', 'Strength', 'Posterior Chain, Back', 'Barbell, Plates', 'Hinge at hips, maintain flat spine, pull bar tight to shins.'),
(4, 'Pull-Ups', 'Bodyweight', 'Lats, Biceps', 'Pull-Up Bar', 'Full extension at bottom, pull chin over bar engaging latissimus dorsi.'),
(5, 'Dumbbell Walking Lunges', 'Conditioning', 'Quads, Glutes', 'Dumbbells', 'Step forward with controlled gait, knee lightly touching ground.');

-- 6. Initial Payment Log
INSERT INTO `payments` (`id`, `subscription_id`, `member_id`, `amount`, `payment_method`, `transaction_id`, `status`) VALUES
(1, 1, 1, 1999.00, 'UPI', 'TXN_20260801_99841', 'paid');
