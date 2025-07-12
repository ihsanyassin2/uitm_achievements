<?php
// admin/index.php
// This file will typically redirect to the admin dashboard.

$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found from admin/index.php.");
}

// Require login and admin role
require_login('admin'); // This will redirect to login if not logged in or not an admin.

// Redirect to the admin dashboard
redirect(SITE_URL . 'admin/dashboard.php');

// No further content needed as this page just redirects.
exit;
?>
