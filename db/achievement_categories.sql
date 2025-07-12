CREATE TABLE `achievement_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) UNIQUE NOT NULL COMMENT 'e.g., Academic, Research, Student Development',
  `description` TEXT
);

-- Insert predefined categories
INSERT INTO `achievement_categories` (`name`, `description`) VALUES
('Academic', 'Achievements related to academic excellence, curriculum development, teaching awards, etc.'),
('Research', 'Achievements related to research projects, publications, grants, innovations, etc.'),
('Student Development', 'Achievements related to student activities, competitions, leadership programs, etc.'),
('Industrial Linkages', 'Achievements related to collaborations with industry partners, internships, etc.'),
('Internationalization', 'Achievements related to international collaborations, student/staff exchange programs, global recognition, etc.'),
('Recognition & Awards', 'Prestigious awards and recognitions received by individuals or the university.'),
('Corporate Social Responsibility (CSR)', 'Achievements related to community engagement and social responsibility initiatives.');
