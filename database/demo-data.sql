-- Optional demo data for a richer first run.
-- Run database/schema.sql, then database/seed.sql, then this file.
USE `ironcore_gym`;

UPDATE `subscriptions` SET `start_date`=DATE_SUB(CURDATE(),INTERVAL 5 DAY),`end_date`=DATE_ADD(CURDATE(),INTERVAL 25 DAY),`status`='active' WHERE `id`=1;

INSERT INTO `workout_plans` (`id`,`member_id`,`trainer_id`,`title`,`goal`,`start_date`,`end_date`) VALUES
(1,1,1,'Upper Body Strength','Build upper-body strength and hypertrophy',DATE_SUB(CURDATE(),INTERVAL 7 DAY),DATE_ADD(CURDATE(),INTERVAL 23 DAY));

INSERT INTO `workout_plan_exercises` (`plan_id`,`exercise_id`,`sets`,`reps`,`rest_seconds`,`day_of_week`) VALUES
(1,2,4,'8-10',90,'Mon'),(1,4,4,'6-10',90,'Wed'),(1,5,3,'12-15',60,'Fri');

INSERT INTO `attendance` (`member_id`,`date`,`check_in_time`,`check_out_time`,`status`) VALUES
(1,DATE_SUB(CURDATE(),INTERVAL 4 DAY),'18:02:00','19:12:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 3 DAY),'18:10:00','19:18:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 2 DAY),'18:05:00','19:20:00','present'),
(1,DATE_SUB(CURDATE(),INTERVAL 1 DAY),'18:01:00','19:10:00','present');

INSERT INTO `progress_logs` (`member_id`,`log_date`,`weight_kg`,`body_fat_pct`,`chest_cm`,`waist_cm`,`biceps_cm`,`notes`) VALUES
(1,DATE_SUB(CURDATE(),INTERVAL 30 DAY),80.00,19.50,101.00,84.00,35.00,'Baseline measurement'),
(1,DATE_SUB(CURDATE(),INTERVAL 14 DAY),79.10,18.90,102.00,83.00,35.40,'Consistent training'),
(1,CURDATE(),78.50,18.20,103.00,82.00,36.00,'Good progress this month');
