<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Replace with your database username
define('DB_PASS', '');     // Replace with your database password
define('DB_NAME', 'uitm_achievements_db'); // Replace with your database name

// Site Configuration
define('SITE_URL', 'http://localhost/uitm_achievements/'); // Adjust if your XAMPP/MAMPP setup is different
define('ROOT_PATH', dirname(__FILE__) . '/../'); // Defines the root path of the project

// Email Configuration (for @uitm.edu.my validation, password reset, notifications)
define('UITM_EMAIL_DOMAIN', 'uitm.edu.my');
define('ADMIN_EMAIL', 'admin@uitm.edu.my'); // For system notifications

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Error Reporting (for development)
//error_reporting(E_ALL);
//ini_set('display_errors', 1); // Comment out for production

// Timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

?>
