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
DELETE FROM `system_settings`;

-- 1. Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('gym_name', 'IRONCORE Fitness'),
('contact_email', 'contact@ironcore.com'),
('phone', '+91 98765 43210'),
('currency', 'INR'),
('timezone', 'Asia/Kolkata'),
('address', 'Plot 42, Cyber City, High-Tech Zone, Hyderabad, 500081');

-- 2. Insert Initial Admin, Trainer, Active Member, Inactive Member, and Suspended Member Users
-- Credentials for Testing Authentication:
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

-- 3. Insert Profiles
INSERT INTO `trainers` (`id`, `user_id`, `specialization`, `experience_years`, `bio`, `hourly_rate`) VALUES
(1, 2, 'Strength & Hypertrophy', 8, 'Senior strength coach specializing in Olympic lifting and hypertrophy programming.', 1500.00),
(2, 3, 'Functional Conditioning & Rehab', 6, 'Certified athletic trainer focused on mobility, fat loss, and core stability.', 1200.00);

INSERT INTO `members` (`id`, `user_id`, `phone`, `emergency_contact`, `gender`, `dob`, `address`, `join_date`) VALUES
(1, 4, '+91 9876543210', 'Maria Rivera (+91 9876543211)', 'male', '1998-05-14', 'B-402 Horizon Towers, Downtown', DATE_SUB(CURDATE(), INTERVAL 60 DAY)),
(2, 5, '+91 9876543216', 'Self', 'male', '1995-11-20', 'Suite 108, Lake View Apartments', DATE_SUB(CURDATE(), INTERVAL 120 DAY)),
(3, 6, '+91 9876543215', 'John Connor', 'female', '1992-07-08', '404 Resistance Lane', DATE_SUB(CURDATE(), INTERVAL 30 DAY));

-- 4. Membership Plans
INSERT INTO `membership_plans` (`id`, `title`, `tag`, `price`, `billing_cycle`, `duration_days`, `description`, `features`, `is_recommended`, `status`) VALUES
(1, 'STARTER', 'Essential Access', 999.00, 'monthly', 30, 'Perfect for self-motivated fitness enthusiasts needing basic facility access.', '["Full Gym Floor Access", "Digital Attendance Tracking", "Member Dashboard", "Locker Room Access"]', 0, 'active'),
(2, 'PRO', 'Most Popular', 1999.00, 'monthly', 30, 'Comprehensive package with guided workout programs and trainer support.', '["Everything in Starter", "Personalized Workout Plans", "Trainer Assistance", "Progress & Metrics Tracking", "Group Fitness Classes"]', 1, 'active'),
(3, 'ELITE', 'VIP Experience', 2999.00, 'monthly', 30, 'All-inclusive premium experience with dedicated 1-on-1 personal training.', '["Everything in Pro", "1-on-1 Personal Trainer", "Priority Session Booking", "Nutritional Consultation", "Complimentary Recovery Drinks"]', 0, 'active');

-- 5. Active and Expired Subscriptions (Dynamic Dates)
INSERT INTO `subscriptions` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `auto_renew`, `status`) VALUES
(1, 1, 2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 1, 'active'),
(2, 2, 1, DATE_SUB(CURDATE(), INTERVAL 90 DAY), DATE_SUB(CURDATE(), INTERVAL 60 DAY), 0, 'expired'),
(3, 3, 1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), CURDATE(), 0, 'expired');

-- 6. Exercise Catalog Seed Data
INSERT INTO `exercise_catalog` (`id`, `name`, `category`, `muscle_group`, `equipment`, `instructions`) VALUES
(1, 'Barbell Back Squat', 'Strength', 'Quadriceps, Glutes', 'Barbell, Rack', 'Keep chest elevated, break at hips and knees, squat below parallel.'),
(2, 'Incline Dumbbell Bench Press', 'Strength', 'Chest, Anterior Deltoids', 'Dumbbells, Bench', 'Set bench at 30 degrees, press dumbbells overhead in controlled arc.'),
(3, 'Conventional Deadlift', 'Strength', 'Posterior Chain, Back', 'Barbell, Plates', 'Hinge at hips, maintain flat spine, pull bar tight to shins.'),
(4, 'Pull-Ups', 'Bodyweight', 'Lats, Biceps', 'Pull-Up Bar', 'Full extension at bottom, pull chin over bar engaging latissimus dorsi.'),
(5, 'Dumbbell Walking Lunges', 'Conditioning', 'Quads, Glutes', 'Dumbbells', 'Step forward with controlled gait, knee lightly touching ground.'),
(6, 'Seated Cable Row', 'Strength', 'Upper Back, Rhomboids', 'Cable Machine', 'Keep torso upright, pull handle towards abdomen, squeeze shoulder blades.'),
(7, 'Overhead Shoulder Press', 'Strength', 'Shoulders, Triceps', 'Barbell / Dumbbells', 'Press weights directly overhead from shoulder height, lockout elbows under control.');

-- 7. Initial Payment Logs (Dynamic Dates)
INSERT INTO `payments` (`id`, `subscription_id`, `member_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`) VALUES
(1, 1, 1, 1999.00, 'UPI', 'TXN_IRON_99841', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 2, 2, 999.00, 'Card', 'TXN_IRON_88120', 'paid', DATE_SUB(NOW(), INTERVAL 90 DAY));

-- 8. Workout Plans & Assigned Exercises
INSERT INTO `workout_plans` (`id`, `member_id`, `trainer_id`, `title`, `goal`, `start_date`, `end_date`) VALUES
(1, 1, 1, 'Hypertrophy & Strength Foundation', 'Build upper-body strength and lean muscle mass', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY));

INSERT INTO `workout_plan_exercises` (`plan_id`, `exercise_id`, `sets`, `reps`, `rest_seconds`, `day_of_week`) VALUES
(1, 1, 4, '8-10', 90, 'Mon'),
(1, 2, 4, '10-12', 75, 'Mon'),
(1, 4, 3, '8-10', 60, 'Wed'),
(1, 6, 3, '12-15', 60, 'Wed'),
(1, 3, 4, '6-8', 120, 'Fri'),
(1, 5, 3, '12-15', 60, 'Fri');

-- 9. Attendance Logs
INSERT INTO `attendance` (`member_id`, `date`, `check_in_time`, `check_out_time`, `status`) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), '18:02:00', '19:15:00', 'present'),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), '18:10:00', '19:20:00', 'present'),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '18:05:00', '19:25:00', 'present'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '18:01:00', '19:10:00', 'present'),
(1, CURDATE(), '09:15:00', NULL, 'present');

-- 10. Progress Logs
INSERT INTO `progress_logs` (`member_id`, `log_date`, `weight_kg`, `body_fat_pct`, `chest_cm`, `waist_cm`, `biceps_cm`, `notes`) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 30 DAY), 80.00, 19.50, 101.00, 84.00, 35.00, 'Baseline measurement'),
(1, DATE_SUB(CURDATE(), INTERVAL 14 DAY), 79.10, 18.90, 102.00, 83.00, 35.40, 'Consistent hypertrophy progress'),
(1, CURDATE(), 78.50, 18.20, 103.00, 82.00, 36.00, 'Body fat decreased, lean mass increased');
