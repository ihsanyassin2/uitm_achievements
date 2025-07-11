-- Table structure for table `achievements`
CREATE TABLE `achievements` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `category` ENUM('Academic', 'Research', 'Student Development', 'Industrial Linkages', 'Internationalization', 'Recognition & Awards', 'Corporate Social Responsibility (CSR)') NOT NULL,
  `level` ENUM('International', 'National', 'Institutional') NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected', 'needs_revision') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
