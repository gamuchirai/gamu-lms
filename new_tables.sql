-- Assignment Submissions Table
CREATE TABLE IF NOT EXISTS `assignment_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `assignment_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `file_path` VARCHAR(500) DEFAULT NULL,
  `submission_text` TEXT DEFAULT NULL,
  `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('pending', 'graded', 'late') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_submission` (`assignment_id`, `user_id`),
  KEY `assignment_id` (`assignment_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `assignment_submissions_ibfk_1` FOREIGN KEY (`assignment_id`) REFERENCES `assignments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `assignment_submissions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lesson Progress Table
CREATE TABLE IF NOT EXISTS `lesson_progress` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `lesson_id` INT NOT NULL,
  `completed` TINYINT(1) DEFAULT 0,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_progress` (`user_id`, `lesson_id`),
  KEY `user_id` (`user_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `lesson_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_progress_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
  `read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `read` (`read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add feedback column to grades table if not exists
ALTER TABLE `grades` 
ADD COLUMN IF NOT EXISTS `feedback` TEXT DEFAULT NULL AFTER `grade`;

-- Add order_num column to lessons table if not exists
ALTER TABLE `lessons` 
ADD COLUMN IF NOT EXISTS `order_num` INT DEFAULT 0 AFTER `content`;

-- Update enrollments table to add progress column if not exists
ALTER TABLE `enrollments` 
ADD COLUMN IF NOT EXISTS `progress` DECIMAL(5,2) DEFAULT 0.00 AFTER `enrolled_at`;

-- Add reset token fields to users table if not exists
ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `reset_token` VARCHAR(255) DEFAULT NULL AFTER `token`,
ADD COLUMN IF NOT EXISTS `reset_token_expiry` DATETIME DEFAULT NULL AFTER `reset_token`;
