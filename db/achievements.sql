CREATE TABLE `achievements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL COMMENT 'FK to users table, the submitter',
  `category_id` INT NOT NULL COMMENT 'FK to achievement_categories table',
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `achievement_date` DATE,
  `level` ENUM('International', 'National', 'Institutional') NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'needs_revision') NOT NULL DEFAULT 'pending',
  `pic_name` VARCHAR(255) NOT NULL COMMENT 'Person in Charge Name',
  `pic_email` VARCHAR(255) NOT NULL COMMENT 'Person in Charge Email',
  `pic_phone` VARCHAR(50) COMMENT 'Person in Charge Phone Number',
  `admin_feedback` TEXT COMMENT 'Feedback from admin if rejected or needs revision',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `approved_at` TIMESTAMP NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`category_id`) REFERENCES `achievement_categories`(`id`) ON DELETE RESTRICT
);
