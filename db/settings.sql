CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_name` VARCHAR(255) UNIQUE NOT NULL,
  `setting_value` TEXT,
  `description` TEXT,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Example settings (can be added/modified by admin later)
INSERT INTO `settings` (`setting_name`, `setting_value`, `description`) VALUES
('site_title', 'UiTM Achievements Portal', 'The main title of the website.'),
('admin_email', 'admin@uitm.edu.my', 'Default administrator email for notifications.'),
('records_per_page', '10', 'Number of records to display per page in listings.');
