<?php
// user/index.php
// This file will typically redirect to the user dashboard or another main page for the user section.

$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    // Fallback if config.php is not found.
    die("Critical error: Main configuration file not found from user/index.php.");
}

// Ensure user is logged in and is a 'user' or 'admin' (admin can access user area)
require_login(); // This will redirect to login if not logged in.

// Admins can access user features, so check for 'user' or 'admin' role.
// If require_login() doesn't handle role redirection sufficiently for this specific case,
// you can add more checks here.
// For example, if a specific page in /user/ is ONLY for 'user' role and not 'admin' viewing as user.
// However, the README states: "Allow admins full access to user features".
// So, require_login() without a specific role parameter should be fine here,
// as it just ensures the user is logged in. The sidebars and navbar will adjust based on actual role.

// Redirect to the user dashboard
redirect(SITE_URL . 'user/dashboard.php');

// No further content needed as this page just redirects.
exit;
?>
