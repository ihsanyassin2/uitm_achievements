-- Table structure for table `achievement_media`
CREATE TABLE `achievement_media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `achievement_id` INT NOT NULL,
  `media_type` ENUM('image', 'video_youtube') NOT NULL,
  `media_url` VARCHAR(255) NOT NULL COMMENT 'File path for images, YouTube URL for videos',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
