-- Step 1: Add 'completed' column to track completion status
ALTER TABLE `student_tasks` 
ADD COLUMN `completed` TINYINT(1) NOT NULL DEFAULT 0 AFTER `task_type`;

-- Step 2: Modify task_type enum to include 'lesson'
ALTER TABLE `student_tasks` 
MODIFY COLUMN `task_type` ENUM('lesson', 'assignment', 'test') COLLATE utf8mb4_unicode_ci NOT NULL;

-- Step 3: Insert sample lesson tasks
INSERT INTO `student_tasks` (`student_id`, `title`, `due_date`, `task_type`, `completed`) VALUES
(1, 'Introduction to Web Design', '2025-10-16', 'lesson', 0),
(1, 'CSS Fundamentals', '2025-10-18', 'lesson', 1),
(1, 'JavaScript Basics', '2025-10-22', 'lesson', 0),
(1, 'Responsive Design', '2025-10-28', 'lesson', 0),
(1, 'Python Variables and Data Types', '2025-11-01', 'lesson', 0),
(1, 'Python Functions', '2025-11-08', 'lesson', 0);

-- Step 4: Mark some existing tasks as completed for testing
UPDATE `student_tasks` 
SET `completed` = 1 
WHERE `title` IN ('HTML Quiz', 'Web Design Project');
