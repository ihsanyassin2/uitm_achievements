CREATE TABLE `likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `session_id` VARCHAR(255) NOT NULL COMMENT 'To identify unique anonymous users (can use PHP session ID or a generated cookie ID)',
  `liked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_like` (`achievement_id`, `session_id`) COMMENT 'Ensures a user can only like an achievement once'
);
