<?php
// uitm_achievements/index.php
// Main entry point for the application.

// Attempt to include the configuration file.
// __DIR__ ensures the path is correct regardless of how index.php is accessed.
$config_path = __DIR__ . '/config/config.php';

if (file_exists($config_path)) {
    require_once $config_path;
} else {
    // If config.php is missing, the application cannot run.
    // Display a user-friendly error message.
    // Logging this error to a server log would also be good practice.
    die("Critical Error: The website configuration is missing. Please contact the site administrator.");
}

// functions.php should be included by config.php.
// Check if essential functions/constants are available as a safeguard.
if (!function_exists('is_logged_in') || !defined('SITE_URL')) {
    die("Critical Error: Essential functions or settings are not loaded. Configuration might be incomplete.");
}

// Determine where to redirect the user.
if (is_logged_in()) {
    // User is logged in. Redirect to their respective dashboard.
    if (has_role('admin')) {
        // Logged-in user is an administrator.
        redirect(SITE_URL . 'admin/dashboard.php');
    } elseif (has_role('user')) {
        // Logged-in user is a regular user.
        redirect(SITE_URL . 'user/dashboard.php');
    } else {
        // Logged-in user has an unrecognized role.
        // This case should ideally not happen with proper role management.
        // For safety, log them out or redirect to a generic error page or public index.
        // Logging out is a safe default.
        $_SESSION['error_message'] = "Your user role is not recognized. Please contact support.";
        redirect(SITE_URL . 'authentication/logout.php'); // Redirect to logout to clear session
    }
} else {
    // User is not logged in. Redirect to the public-facing homepage.
    redirect(SITE_URL . 'public/index.php');
}

// Fallback exit, though redirect() should have already exited.
exit;
?>
