-- IRONCORE Minimal Clean Seed Data
-- 1 Record per Entity Architecture for Real-Data Testing
USE `ironcore_gym`;

-- Clear existing data in dependency order
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
DELETE FROM `system_settings`;

-- 1. Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('gym_name', 'IRONCORE Fitness'),
('contact_email', 'contact@ironcore.com'),
('phone', '+91 98765 43210'),
('currency', 'INR'),
('timezone', 'Asia/Kolkata'),
('address', 'Plot 42, Cyber City, High-Tech Zone, Hyderabad, 500081');

-- 2. Insert Exactly 1 Admin, 1 Trainer, 1 Member
-- Credentials:
-- Admin:   admin@ironcore.com   / admin         / Admin@123
-- Trainer: marcus@ironcore.com  / marcus.vance  / Trainer@123
-- Member:  alex@gmail.com       / alex.rivera   / Member@123
INSERT INTO `users` (`id`, `full_name`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin', 'admin@ironcore.com', '$2y$10$PSDVDniqt5886aTezjeli.6VGpJ2oJBEFhpcfCFXhEtzq/4B4CR3y', 'admin', 'active'),
(2, 'Marcus Vance', 'marcus.vance', 'marcus@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(3, 'Alex Rivera', 'alex.rivera', 'alex@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'active');

-- 3. Profiles (1 Trainer, 1 Member)
INSERT INTO `trainers` (`id`, `user_id`, `phone`, `specialization`, `experience_years`, `bio`, `hourly_rate`) VALUES
(1, 2, '+91 98765 11111', 'Strength & Hypertrophy', 8, 'Senior strength coach specializing in Olympic lifting, hypertrophy, and athlete conditioning.', 1500.00);

INSERT INTO `members` (`id`, `user_id`, `assigned_trainer_id`, `phone`, `emergency_contact`, `gender`, `dob`, `address`, `join_date`) VALUES
(1, 3, 1, '+91 9876543210', 'Maria Rivera (+91 9876543211)', 'male', '1998-05-14', 'B-402 Horizon Towers, Downtown', DATE_SUB(CURDATE(), INTERVAL 30 DAY));

-- 4. Exactly 1 Initial Membership Plan
INSERT INTO `membership_plans` (`id`, `title`, `tag`, `price`, `billing_cycle`, `duration_days`, `description`, `features`, `is_recommended`, `status`) VALUES
(1, 'PRO', 'Most Popular', 1999.00, 'monthly', 30, 'Comprehensive package with guided workout programs, trainer support, and full facility access.', '["Full Gym Floor Access", "Personalized Workout Plans", "Trainer Assistance", "Progress Tracking", "Locker Room Privileges"]', 1, 'active');

-- 5. Exactly 1 Active Subscription
INSERT INTO `subscriptions` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `auto_renew`, `status`) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 1, 'active');

-- 6. Core Exercise Catalog
INSERT INTO `exercise_catalog` (`id`, `name`, `category`, `muscle_group`, `equipment`, `instructions`) VALUES
(1, 'Barbell Back Squat', 'Strength', 'Quadriceps, Glutes', 'Barbell, Rack', 'Keep chest elevated, break at hips and knees, squat below parallel.'),
(2, 'Incline Dumbbell Bench Press', 'Strength', 'Chest, Anterior Deltoids', 'Dumbbells, Bench', 'Set bench at 30 degrees, press dumbbells overhead in controlled arc.'),
(3, 'Conventional Deadlift', 'Strength', 'Posterior Chain, Back', 'Barbell, Plates', 'Hinge at hips, maintain flat spine, pull bar tight to shins.'),
(4, 'Pull-Ups', 'Bodyweight', 'Lats, Biceps', 'Pull-Up Bar', 'Full extension at bottom, pull chin over bar engaging latissimus dorsi.'),
(5, 'Dumbbell Walking Lunges', 'Conditioning', 'Quads, Glutes', 'Dumbbells', 'Step forward with controlled gait, knee lightly touching ground.'),
(6, 'Seated Cable Row', 'Strength', 'Upper Back, Rhomboids', 'Cable Machine', 'Keep torso upright, pull handle towards abdomen, squeeze shoulder blades.'),
(7, 'Overhead Shoulder Press', 'Strength', 'Shoulders, Triceps', 'Barbell / Dumbbells', 'Press weights directly overhead from shoulder height, lockout elbows under control.');

-- 7. Exactly 1 Paid Payment Record
INSERT INTO `payments` (`id`, `subscription_id`, `member_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`) VALUES
(1, 1, 1, 1999.00, 'UPI', 'TXN_IRON_99841', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 8. Exactly 1 Workout Plan with Exercises
INSERT INTO `workout_plans` (`id`, `member_id`, `trainer_id`, `title`, `difficulty`, `goal`, `description`, `start_date`, `end_date`) VALUES
(1, 1, 1, 'Hypertrophy & Strength Foundation', 'Intermediate', 'Build upper-body strength, compound power, and lean muscle mass.', 'A comprehensive periodized protocol emphasizing barbell compound lifts and accessory hypertrophy movements.', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY));

INSERT INTO `workout_plan_exercises` (`plan_id`, `exercise_id`, `sets`, `reps`, `rest_seconds`, `day_of_week`) VALUES
(1, 1, 4, '8-10', 90, 'Mon'),
(1, 2, 4, '10-12', 75, 'Wed'),
(1, 3, 3, '6-8', 120, 'Fri');

-- 9. Exactly 1 Active Attendance Check-In (Currently in gym)
INSERT INTO `attendance` (`id`, `member_id`, `date`, `check_in_time`, `check_out_time`, `status`) VALUES
(1, 1, CURDATE(), '09:15:00', NULL, 'present');

-- 10. Exactly 1 Progress Measurement Log
INSERT INTO `progress_logs` (`id`, `member_id`, `log_date`, `weight_kg`, `body_fat_pct`, `chest_cm`, `waist_cm`, `biceps_cm`, `notes`) VALUES
(1, 1, CURDATE(), 78.50, 18.20, 103.00, 82.00, 36.00, 'Baseline measurement and body composition logged.');
