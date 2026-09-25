-- IRONCORE Clean Development & Runtime Baseline Seed
-- Authentication + Six athlete-inspired trainer profiles + Baseline athlete member + Reusable exercise catalog
USE ironcore_gym;

DELETE FROM trainer_exercise_assignments;
DELETE FROM progress_logs;
DELETE FROM payments;
DELETE FROM workout_plan_exercises;
DELETE FROM workout_plans;
DELETE FROM exercise_catalog;
DELETE FROM attendance;
DELETE FROM subscriptions;
DELETE FROM membership_plans;
DELETE FROM members;
DELETE FROM trainers;
DELETE FROM users;
DELETE FROM system_settings;

-- 1. System Settings
INSERT INTO system_settings (setting_key, setting_value) VALUES
('gym_name', 'IRONCORE Fitness'),
('contact_email', 'contact@ironcore.com'),
('phone', '+91 98765 43210'),
('currency', 'INR'),
('timezone', 'Asia/Kolkata'),
('address', 'IRONCORE Fitness Centre, India');

-- 2. User Accounts
-- Admin password: Admin@123
-- Trainer password: Trainer@123
-- Member password: Member@123
INSERT INTO users (id, full_name, username, email, password_hash, role, status) VALUES
(1, 'System Administrator', 'admin', 'admin@ironcore.com', '$2y$10$PSDVDniqt5886aTezjeli.6VGpJ2oJBEFhpcfCFXhEtzq/4B4CR3y', 'admin', 'active'),
(2, 'Neeraj Chopra', 'neeraj.demo', 'neeraj.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(3, 'Mirabai Chanu', 'mirabai.demo', 'mirabai.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(4, 'P. V. Sindhu', 'pvsindhu.demo', 'pvsindhu.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(5, 'Lovlina Borgohain', 'lovlina.demo', 'lovlina.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(6, 'Manu Bhaker', 'manu.demo', 'manu.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(7, 'P. R. Sreejesh', 'sreejesh.demo', 'sreejesh.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(8, 'Alex Rivera', 'alex.rivera', 'alex@gmail.com', '$2y$10$KHllJ7ArZ7pDM6Z4fSwxf.FMWKrFkLt0e0I0EaSg7hD92DX7Ma6bC', 'member', 'active');

-- 3. Trainer Profiles
INSERT INTO trainers (id, user_id, phone, specialization, experience_years, bio, hourly_rate) VALUES
(1, 2, '+91 90000 00001', 'Athletics & Explosive Power', 8, 'Senior strength coach specializing in javelin athletics, explosive power and conditioning.', 1500.00),
(2, 3, '+91 90000 00002', 'Olympic Weightlifting & Strength', 7, 'Specialist in Olympic lifting, posterior chain development, and raw power.', 1400.00),
(3, 4, '+91 90000 00003', 'Badminton Conditioning & Agility', 6, 'High-intensity conditioning, footwork mobility, and cardiovascular endurance.', 1200.00),
(4, 5, '+91 90000 00004', 'Boxing Conditioning & Combat Fitness', 5, 'Rotational power, core durability, and combat conditioning.', 1200.00),
(5, 6, '+91 90000 00005', 'Precision Training & Athletic Focus', 4, 'Stability, precision muscular endurance, and posture alignment.', 1000.00),
(6, 7, '+91 90000 00006', 'Hockey Conditioning & Goalkeeper Fitness', 10, 'Reflex speed, unilateral stability, and total-body durability.', 1600.00);

-- 4. Member Profiles
INSERT INTO members (id, user_id, assigned_trainer_id, phone, emergency_contact, gender, dob, address, join_date) VALUES
(1, 8, 1, '+91 98765 43210', 'Maria Rivera (+91 98765 43211)', 'male', '1998-05-14', 'B-402 Horizon Towers, Downtown', DATE_SUB(CURDATE(), INTERVAL 30 DAY));

-- 5. Membership Plans
INSERT INTO membership_plans (id, title, tag, price, billing_cycle, duration_days, description, features, is_recommended, status) VALUES
(1, 'PRO ATHLETE', 'Most Popular', 4999.00, 'monthly', 30, 'Comprehensive package with guided workout programs, dedicated trainer support, and full facility access.', '["Full Gym Floor Access", "Personalized Workout Plans", "Dedicated Trainer Support", "Progress & Metrics Tracking", "Locker Room & Recovery Privileges"]', 1, 'active');

-- 6. Subscriptions
INSERT INTO subscriptions (id, member_id, plan_id, start_date, end_date, auto_renew, status) VALUES
(1, 1, 1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 1, 'active');

-- 7. Exercise Catalog
INSERT INTO exercise_catalog (id, name, category, muscle_group, equipment, instructions) VALUES
(1, 'Barbell Back Squat', 'Strength', 'Quadriceps, Glutes', 'Barbell, Rack', 'Brace the core, keep the chest controlled, and squat through a stable foot position.'),
(2, 'Incline Dumbbell Bench Press', 'Strength', 'Chest, Shoulders, Triceps', 'Dumbbells, Bench', 'Use a controlled descent and press evenly without losing shoulder position.'),
(3, 'Conventional Deadlift', 'Strength', 'Posterior Chain, Back', 'Barbell, Plates', 'Hinge at the hips, keep the spine neutral, and drive through the floor.'),
(4, 'Pull-Ups', 'Bodyweight', 'Lats, Biceps', 'Pull-Up Bar', 'Start from controlled extension and pull the chest toward the bar.'),
(5, 'Walking Lunges', 'Conditioning', 'Quadriceps, Glutes', 'Dumbbells', 'Step forward under control and keep the front knee aligned with the foot.'),
(6, 'Seated Cable Row', 'Strength', 'Upper Back, Rhomboids', 'Cable Machine', 'Pull toward the torso while maintaining a stable spine and controlled return.'),
(7, 'Overhead Press', 'Strength', 'Shoulders, Triceps', 'Barbell / Dumbbells', 'Press vertically while keeping the ribs controlled and elbows stable.'),
(8, 'Romanian Deadlift', 'Strength', 'Hamstrings, Glutes', 'Barbell / Dumbbells', 'Push the hips back and keep the load close to the body.'),
(9, 'Box Jumps', 'Power', 'Glutes, Quads, Calves', 'Plyometric Box', 'Land softly on the box and step down with control.'),
(10, 'Battle Rope Intervals', 'Conditioning', 'Shoulders, Core', 'Battle Ropes', 'Maintain an athletic stance and produce rhythmic rope waves.'),
(11, 'Plank', 'Core', 'Abdominals, Core', 'Bodyweight', 'Maintain a straight line from shoulders through hips while bracing the core.'),
(12, 'Assault Bike Intervals', 'Conditioning', 'Full Body', 'Assault Bike', 'Alternate controlled hard efforts with recovery intervals.');

-- 8. Trainer Exercise Assignments
INSERT INTO trainer_exercise_assignments (trainer_id, exercise_id, assigned_by_user_id) VALUES
(1, 1, 1),
(1, 3, 1),
(1, 9, 1);

-- 9. Payments
INSERT INTO payments (id, subscription_id, member_id, amount, payment_method, transaction_id, status, payment_date) VALUES
(1, 1, 1, 4999.00, 'UPI', 'TXN_IRON_99841', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 10. Workout Plans & Exercises
INSERT INTO workout_plans (id, member_id, trainer_id, title, difficulty, goal, description, start_date, end_date) VALUES
(1, 1, 1, 'Explosive Power & Athletic Conditioning', 'Intermediate', 'Build explosive power, raw strength, and athletic mobility.', 'Periodized protocol focusing on barbell compound movements, posterior chain resilience, and core stabilization.', DATE_SUB(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 23 DAY));

INSERT INTO workout_plan_exercises (plan_id, exercise_id, sets, reps, rest_seconds, day_of_week) VALUES
(1, 1, 4, '8-10', 90, 'Mon'),
(1, 3, 3, '6-8', 120, 'Wed'),
(1, 9, 4, '10-12', 60, 'Fri');

-- 11. Attendance
INSERT INTO attendance (id, member_id, date, check_in_time, check_out_time, status) VALUES
(1, 1, CURDATE(), '09:15:00', NULL, 'present');

-- 12. Progress Logs
INSERT INTO progress_logs (id, member_id, log_date, weight_kg, body_fat_pct, chest_cm, waist_cm, biceps_cm, notes) VALUES
(1, 1, CURDATE(), 78.50, 18.20, 103.00, 82.00, 36.00, 'Baseline athletic metrics and body composition logged.');
