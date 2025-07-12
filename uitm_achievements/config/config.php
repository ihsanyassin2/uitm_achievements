<?php
// Configuration file for UiTM Achievements
// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user'); // Replace with your database username
define('DB_PASS', 'your_db_password'); // Replace with your database password
define('DB_NAME', 'uitm_achievements_db'); // Replace with your database name

// Site settings
define('SITE_URL', 'http://localhost/uitm_achievements/'); // Change this to your actual site URL
define('SITE_ROOT', __DIR__ . '/../'); // Defines the root directory of the project

// Email settings (for registration, notifications, etc.)
define('ADMIN_EMAIL', 'admin@example.com'); // Admin email for system notifications

// Security settings
define('SESSION_NAME', 'uitmAcheivementsSession');

// Other settings
// ... add as needed

// Error reporting
// FOR DEVELOPMENT:
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// FOR PRODUCTION:
// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 0);
// error_reporting(E_ALL); // Report all errors
// ini_set('log_errors', 1); // Log errors to a file
// ini_set('error_log', SITE_ROOT . 'logs/php_error.log'); // Ensure 'logs' directory exists and is writable by the server

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// Autoload function for classes (if you plan to use OOP and classes)
/*
spl_autoload_register(function ($className) {
    $classPath = SITE_ROOT . 'classes/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($classPath)) {
        require_once $classPath;
    }
});
*/

// Include essential functions
require_once SITE_ROOT . 'functions/functions.php';

?>
