-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 10:50 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `uitm_achievements`
--

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'FK to users table, the submitter',
  `category_id` int(11) NOT NULL COMMENT 'FK to achievement_categories table',
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `achievement_date` date DEFAULT NULL,
  `level` enum('International','National','Institutional') NOT NULL,
  `status` enum('pending','approved','rejected','needs_revision') NOT NULL DEFAULT 'pending',
  `pic_name` varchar(255) NOT NULL COMMENT 'Person in Charge Name',
  `pic_email` varchar(255) NOT NULL COMMENT 'Person in Charge Email',
  `pic_phone` varchar(50) DEFAULT NULL COMMENT 'Person in Charge Phone Number',
  `admin_feedback` text DEFAULT NULL COMMENT 'Feedback from admin if rejected or needs revision',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL COMMENT 'Admin who approved this achievement',
  `rejected_by_user_id` int(11) DEFAULT NULL COMMENT 'Admin who rejected this achievement',
  `rejected_at` timestamp NULL DEFAULT NULL COMMENT 'When it was rejected',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Manually featured by admin',
  `featured_order` int(11) DEFAULT NULL COMMENT 'Order for featured items (lower numbers first)',
  `featured_until` timestamp NULL DEFAULT NULL COMMENT 'Featured until this date (for rotation)',
  `feature_reason` varchar(255) DEFAULT NULL COMMENT 'Why this was featured',
  `view_count` int(11) DEFAULT 0 COMMENT 'Number of times this achievement was viewed',
  `last_viewed` timestamp NULL DEFAULT NULL COMMENT 'Last time this achievement was viewed',
  `share_count` int(11) DEFAULT 0 COMMENT 'Number of times this was shared (if you add sharing)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `achievement_categories`
--

CREATE TABLE `achievement_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'e.g., Academic, Research, Student Development',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievement_categories`
--

INSERT INTO `achievement_categories` (`id`, `name`, `description`) VALUES
(1, 'Academic', 'Achievements related to academic excellence, curriculum development, teaching awards, etc.'),
(2, 'Research', 'Achievements related to research projects, publications, grants, innovations, etc.'),
(3, 'Student Development', 'Achievements related to student activities, competitions, leadership programs, etc.'),
(4, 'Industrial Linkages', 'Achievements related to collaborations with industry partners, internships, etc.'),
(5, 'Internationalization', 'Achievements related to international collaborations, student/staff exchange programs, global recognition, etc.'),
(6, 'Recognition & Awards', 'Prestigious awards and recognitions received by individuals or the university.'),
(7, 'Corporate Social Responsibility (CSR)', 'Achievements related to community engagement and social responsibility initiatives.');

-- --------------------------------------------------------

--
-- Table structure for table `achievement_media`
--

CREATE TABLE `achievement_media` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `media_type` enum('image','youtube_video') NOT NULL,
  `file_path_or_url` varchar(255) NOT NULL COMMENT 'File path for images, URL for YouTube videos',
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_size` int(11) DEFAULT NULL COMMENT 'File size in bytes',
  `original_filename` varchar(255) DEFAULT NULL COMMENT 'Original filename when uploaded'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `achievement_status_history`
--

CREATE TABLE `achievement_status_history` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `old_status` enum('pending','approved','rejected','needs_revision') DEFAULT NULL,
  `new_status` enum('pending','approved','rejected','needs_revision') NOT NULL,
  `changed_by_user_id` int(11) NOT NULL COMMENT 'Admin who made the change',
  `admin_feedback` text DEFAULT NULL COMMENT 'Feedback provided with status change',
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'NULL for anonymous/system actions',
  `action_type` varchar(50) NOT NULL COMMENT 'e.g., CREATE, UPDATE, DELETE, LOGIN, LOGOUT, APPROVE, REJECT',
  `table_affected` varchar(50) DEFAULT NULL COMMENT 'Which table was affected',
  `record_id` int(11) DEFAULT NULL COMMENT 'ID of the affected record',
  `description` text NOT NULL COMMENT 'Human readable description of the action',
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Previous values for UPDATE actions' CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'New values for CREATE/UPDATE actions' CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IPv4 or IPv6 address',
  `user_agent` text DEFAULT NULL COMMENT 'Browser/client information',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL COMMENT 'To identify unique anonymous users (can use PHP session ID or a generated cookie ID)',
  `liked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL COMMENT 'Associated achievement for context, FK to achievements table',
  `sender_id` int(11) NOT NULL COMMENT 'FK to users table',
  `receiver_id` int(11) NOT NULL COMMENT 'FK to users table',
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('achievement_approved','achievement_rejected','achievement_needs_revision','new_message','new_submission') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL COMMENT 'ID of related record (achievement_id, message_id, etc.)',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_analytics`
--

CREATE TABLE `page_analytics` (
  `id` int(11) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `page_title` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referrer` varchar(255) DEFAULT NULL,
  `time_spent` int(11) DEFAULT NULL COMMENT 'Time spent on page in seconds',
  `visited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_log`
--

CREATE TABLE `search_log` (
  `id` int(11) NOT NULL,
  `search_query` varchar(255) NOT NULL,
  `category_filter` varchar(50) DEFAULT NULL COMMENT 'Category filter applied',
  `level_filter` varchar(50) DEFAULT NULL COMMENT 'Level filter applied',
  `results_count` int(11) DEFAULT 0 COMMENT 'Number of results returned',
  `session_id` varchar(255) DEFAULT NULL COMMENT 'User session for anonymous tracking',
  `user_id` int(11) DEFAULT NULL COMMENT 'If user is logged in',
  `ip_address` varchar(45) DEFAULT NULL,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_name`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_title', 'UiTM Achievements Portal', 'The main title of the website.', '2025-07-12 00:40:42'),
(2, 'admin_email', 'admin@uitm.edu.my', 'Default administrator email for notifications.', '2025-07-12 00:40:42'),
(3, 'records_per_page', '10', 'Number of records to display per page in listings.', '2025-07-12 00:40:42'),
(4, 'max_images_per_achievement', '5', 'Maximum number of images allowed per achievement', '2025-07-12 08:35:27'),
(5, 'max_file_size_mb', '10', 'Maximum file size in MB for image uploads', '2025-07-12 08:35:27'),
(6, 'allowed_image_types', 'jpg,jpeg,png,gif', 'Comma-separated list of allowed image file extensions', '2025-07-12 08:35:27'),
(7, 'featured_achievements_per_category', '3', 'Number of featured achievements to show per category', '2025-07-12 08:36:03'),
(8, 'auto_feature_top_liked', '1', 'Auto-feature top liked achievements (1=yes, 0=no)', '2025-07-12 08:36:03'),
(9, 'achievements_per_page_public', '12', 'Number of achievements per page on public site', '2025-07-12 08:36:03'),
(10, 'enable_analytics', '1', 'Enable detailed analytics tracking (1=yes, 0=no)', '2025-07-12 08:36:03'),
(11, 'notification_retention_days', '30', 'How many days to keep notifications', '2025-07-12 08:36:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uitm_id` char(6) NOT NULL COMMENT 'Exactly 6-digit staff/student ID',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `biography` text DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `cv_link` varchar(255) DEFAULT NULL,
  `google_scholar_link` varchar(255) DEFAULT NULL,
  `linkedin_link` varchar(255) DEFAULT NULL,
  `scopus_link` varchar(255) DEFAULT NULL,
  `isi_link` varchar(255) DEFAULT NULL,
  `orcid_link` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL COMMENT 'Path to profile picture',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `security_question` varchar(255) NOT NULL DEFAULT '',
  `security_answer_hash` varchar(255) NOT NULL DEFAULT '',
  `last_checked_achievements` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'For tracking new achievement notifications',
  `last_checked_messages` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'For tracking new message notifications',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Track last login for analytics',
  `profile_views` int(11) DEFAULT 0 COMMENT 'Number of times profile was viewed',
  `total_achievements` int(11) DEFAULT 0 COMMENT 'Cached count of user achievements',
  `total_approved_achievements` int(11) DEFAULT 0 COMMENT 'Cached count of approved achievements'
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `approved_by_user_id` (`approved_by_user_id`),
  ADD KEY `rejected_by_user_id` (`rejected_by_user_id`),
  ADD KEY `idx_achievements_featured` (`is_featured`,`featured_order`),
  ADD KEY `idx_achievements_analytics` (`view_count`,`last_viewed`);

--
-- Indexes for table `achievement_categories`
--
ALTER TABLE `achievement_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `achievement_media`
--
ALTER TABLE `achievement_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievement_id` (`achievement_id`);

--
-- Indexes for table `achievement_status_history`
--
ALTER TABLE `achievement_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by_user_id` (`changed_by_user_id`),
  ADD KEY `idx_status_history_achievement` (`achievement_id`),
  ADD KEY `idx_status_history_date` (`changed_at`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`),
  ADD KEY `idx_activity_type` (`action_type`),
  ADD KEY `idx_activity_table` (`table_affected`),
  ADD KEY `idx_activity_date` (`created_at`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`achievement_id`,`session_id`) COMMENT 'Ensures a user can only like an achievement once';

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `achievement_id` (`achievement_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user` (`user_id`),
  ADD KEY `idx_notifications_unread` (`user_id`,`is_read`),
  ADD KEY `idx_notifications_type` (`type`);

--
-- Indexes for table `page_analytics`
--
ALTER TABLE `page_analytics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_page_analytics_url` (`page_url`),
  ADD KEY `idx_page_analytics_date` (`visited_at`);

--
-- Indexes for table `search_log`
--
ALTER TABLE `search_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_search_query` (`search_query`),
  ADD KEY `idx_search_date` (`searched_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uitm_id` (`uitm_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `uitm_id_2` (`uitm_id`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_uitm_id` (`uitm_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `achievement_categories`
--
ALTER TABLE `achievement_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `achievement_media`
--
ALTER TABLE `achievement_media`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `achievement_status_history`
--
ALTER TABLE `achievement_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_analytics`
--
ALTER TABLE `page_analytics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `search_log`
--
ALTER TABLE `search_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `achievements`
--
ALTER TABLE `achievements`
  ADD CONSTRAINT `achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievements_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `achievement_categories` (`id`),
  ADD CONSTRAINT `achievements_ibfk_3` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achievements_ibfk_4` FOREIGN KEY (`rejected_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `achievement_media`
--
ALTER TABLE `achievement_media`
  ADD CONSTRAINT `achievement_media_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `achievement_status_history`
--
ALTER TABLE `achievement_status_history`
  ADD CONSTRAINT `achievement_status_history_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `achievement_status_history_ibfk_2` FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_analytics`
--
ALTER TABLE `page_analytics`
  ADD CONSTRAINT `page_analytics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `search_log`
--
ALTER TABLE `search_log`
  ADD CONSTRAINT `search_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
