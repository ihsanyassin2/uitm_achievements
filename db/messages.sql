CREATE TABLE `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL COMMENT 'Associated achievement for context, FK to achievements table',
  `sender_id` INT NOT NULL COMMENT 'FK to users table',
  `receiver_id` INT NOT NULL COMMENT 'FK to users table',
  `message` TEXT NOT NULL,
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);
