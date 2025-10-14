-- Create student_tasks table
CREATE TABLE IF NOT EXISTS `student_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `student_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date NOT NULL,
  `task_type` enum('assignment','test') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_student_tasks_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`sid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data for testing (student_id = 1)
INSERT INTO `student_tasks` (`student_id`, `title`, `due_date`, `task_type`) VALUES
(1, 'Practical theory', '2025-10-29', 'assignment'),
(1, 'Practical theory I', '2025-10-29', 'test'),
(1, 'Web Design Project', '2025-10-25', 'assignment'),
(1, 'HTML Quiz', '2025-10-20', 'test'),
(1, 'Python Data Analysis', '2025-11-05', 'assignment'),
(1, 'Final Assessment', '2025-11-15', 'test');
