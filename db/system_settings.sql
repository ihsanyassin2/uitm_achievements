-- Table structure for table `system_settings`
CREATE TABLE `system_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_name` VARCHAR(255) NOT NULL UNIQUE,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial settings (optional, can be inserted via admin panel later)
-- INSERT INTO `system_settings` (`setting_name`, `setting_value`) VALUES
-- ('site_title', 'UiTM Achievements Portal'),
-- ('admin_email', 'admin@example.com'),
-- ('records_per_page', '10');
