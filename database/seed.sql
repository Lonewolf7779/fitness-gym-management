-- IRONCORE clean development seed
-- Authentication + six athlete-inspired trainer demonstration profiles + reusable exercise catalog only.
-- All member, subscription, payment, attendance, workout and progress records are intentionally empty.
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
DELETE FROM trainers;
DELETE FROM members;
DELETE FROM users;
DELETE FROM system_settings;

INSERT INTO system_settings (setting_key, setting_value) VALUES
('gym_name', 'IRONCORE Fitness'),
('contact_email', 'contact@ironcore.com'),
('phone', '+91 98765 43210'),
('currency', 'INR'),
('timezone', 'Asia/Kolkata'),
('address', 'IRONCORE Fitness Centre, India');

-- Admin password: Admin@123
INSERT INTO users (id, full_name, username, email, password_hash, role, status) VALUES
(1, 'System Administrator', 'admin', 'admin@ironcore.com', '$2y$10$PSDVDniqt5886aTezjeli.6VGpJ2oJBEFhpcfCFXhEtzq/4B4CR3y', 'admin', 'active');

-- Demo trainer password for all six: Trainer@123.
-- Profiles are inspired by Indian sporting disciplines and are not claims of employment or coaching affiliation.
INSERT INTO users (id, full_name, username, email, password_hash, role, status) VALUES
(2, 'Neeraj Chopra', 'neeraj.demo', 'neeraj.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(3, 'Mirabai Chanu', 'mirabai.demo', 'mirabai.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(4, 'P. V. Sindhu', 'pvsindhu.demo', 'pvsindhu.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(5, 'Lovlina Borgohain', 'lovlina.demo', 'lovlina.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(6, 'Manu Bhaker', 'manu.demo', 'manu.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active'),
(7, 'P. R. Sreejesh', 'sreejesh.demo', 'sreejesh.demo@ironcore.com', '$2y$10$6dhZuld0ArcZFHJGWSAfzuh67GpAaRxGU2abaB5LPwoZz.3GLmwZO', 'trainer', 'active');

INSERT INTO trainers (id, user_id, phone, specialization, experience_years, bio, hourly_rate) VALUES
(1, 2, '+91 90000 00001', 'Athletics & Explosive Power', 0, 'Demonstration profile inspired by Indian javelin athletics. Not an official coaching affiliation.', 0.00),
(2, 3, '+91 90000 00002', 'Olympic Weightlifting & Strength', 0, 'Demonstration profile inspired by Indian weightlifting. Not an official coaching affiliation.', 0.00),
(3, 4, '+91 90000 00003', 'Badminton Conditioning & Agility', 0, 'Demonstration profile inspired by Indian badminton. Not an official coaching affiliation.', 0.00),
(4, 5, '+91 90000 00004', 'Boxing Conditioning & Combat Fitness', 0, 'Demonstration profile inspired by Indian boxing. Not an official coaching affiliation.', 0.00),
(5, 6, '+91 90000 00005', 'Precision Training & Athletic Focus', 0, 'Demonstration profile inspired by Indian shooting. Not an official coaching affiliation.', 0.00),
(6, 7, '+91 90000 00006', 'Hockey Conditioning & Goalkeeper Fitness', 0, 'Demonstration profile inspired by Indian field hockey. Not an official coaching affiliation.', 0.00);

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
