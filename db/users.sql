CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `uitm_id` VARCHAR(255) UNIQUE NOT NULL COMMENT 'Can be staff ID or student ID, used for public profile URL',
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user',
  `biography` TEXT,
  `phone_number` VARCHAR(50),
  `cv_link` VARCHAR(255),
  `google_scholar_link` VARCHAR(255),
  `linkedin_link` VARCHAR(255),
  `scopus_link` VARCHAR(255),
  `isi_link` VARCHAR(255),
  `orcid_link` VARCHAR(255),
  `profile_picture` VARCHAR(255) COMMENT 'Path to profile picture',
  `reset_token` VARCHAR(64) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Add constraint to ensure email is a uitm.edu.my email
ALTER TABLE `users` ADD CONSTRAINT chk_uitm_email CHECK (`email` LIKE '%@uitm.edu.my');

-- Add indexes for frequently queried columns
ALTER TABLE `users` ADD INDEX `idx_user_email` (`email`);
ALTER TABLE `users` ADD INDEX `idx_user_uitm_id` (`uitm_id`);
ALTER TABLE `users` ADD INDEX `idx_user_reset_token` (`reset_token`);
