-- Optional demo data for a richer first run.
-- Run database/schema.sql, then database/seed.sql, then this file.
USE `ironcore_gym`;

-- Additional Members
INSERT IGNORE INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`) VALUES
(7, 'Daniel Carter', 'daniel@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'active'),
(8, 'Sophia Miller', 'sophia@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'active'),
(9, 'Ryan Brooks', 'ryan@gmail.com', '$2y$10$5BS7zio7lT.L4hLK/bhfEunJ7iIaup8g/gTPBoKMnedgLztKUmWIK', 'member', 'active');

INSERT IGNORE INTO `members` (`id`, `user_id`, `phone`, `emergency_contact`, `gender`, `dob`, `address`, `join_date`) VALUES
(4, 7, '+91 9876543211', 'Robert Carter', 'male', '1996-03-12', '74 Sunrise Avenue, West End', DATE_SUB(CURDATE(), INTERVAL 45 DAY)),
(5, 8, '+91 9876543212', 'Emma Miller', 'female', '1999-09-24', '120 Silicon Heights, North Zone', DATE_SUB(CURDATE(), INTERVAL 20 DAY)),
(6, 9, '+91 9876543213', 'Jessica Brooks', 'male', '1994-12-05', '55 Park Street, Central', DATE_SUB(CURDATE(), INTERVAL 15 DAY));

INSERT IGNORE INTO `subscriptions` (`id`, `member_id`, `plan_id`, `start_date`, `end_date`, `auto_renew`, `status`) VALUES
(4, 4, 1, DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 1, 'active'),
(5, 5, 3, DATE_SUB(CURDATE(), INTERVAL 20 DAY), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1, 'active'),
(6, 6, 2, DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 1, 'active');

INSERT IGNORE INTO `payments` (`id`, `subscription_id`, `member_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`) VALUES
(3, 4, 4, 999.00, 'UPI', 'TXN_IRON_77192', 'paid', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(4, 5, 5, 2999.00, 'Card', 'TXN_IRON_66281', 'paid', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(5, 6, 6, 1999.00, 'UPI', 'TXN_IRON_55390', 'paid', DATE_SUB(NOW(), INTERVAL 10 DAY));

INSERT IGNORE INTO `workout_plans` (`id`, `member_id`, `trainer_id`, `title`, `goal`, `start_date`, `end_date`) VALUES
(2, 4, 1, 'Strength & Muscle Building', 'Focus on compound strength progression', DATE_SUB(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 20 DAY)),
(3, 5, 2, 'VIP Conditioning & Mobility', 'Olympic lifting form & endurance', DATE_SUB(CURDATE(), INTERVAL 15 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY));

INSERT IGNORE INTO `attendance` (`member_id`, `date`, `check_in_time`, `check_out_time`, `status`) VALUES
(4, CURDATE(), '08:30:00', '09:45:00', 'present'),
(5, CURDATE(), '10:00:00', NULL, 'present'),
(6, CURDATE(), '11:15:00', '12:30:00', 'present');
