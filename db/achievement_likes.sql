-- Table structure for table `achievement_likes`
CREATE TABLE `achievement_likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `user_id` INT DEFAULT NULL COMMENT 'Can be NULL if liked by a public, non-logged-in user, or store session/IP hash',
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'Store IP address for non-logged-in user likes to prevent multiple likes',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL, -- Allow user to be deleted without losing like count
  UNIQUE KEY `unique_like` (`achievement_id`, `user_id`, `ip_address`) -- Prevent duplicate likes from same user/IP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
