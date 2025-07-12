CREATE TABLE `achievement_media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `media_type` ENUM('image', 'youtube_video') NOT NULL,
  `file_path_or_url` VARCHAR(255) NOT NULL COMMENT 'File path for images, URL for YouTube videos',
  `caption` VARCHAR(255),
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
);
