<?php
// logout.php
// No need for full page title or HTML structure, this script just performs an action and redirects.

// Start session if not already started, to access session variables
if (session_status() == PHP_SESSION_NONE) {
    // It's important that SESSION_NAME is defined if you use custom session names.
    // config.php should handle this.
    $session_name_defined = defined('SESSION_NAME');
    session_name($session_name_defined ? SESSION_NAME : 'uitmAcheivementsSession');
    session_start();
}

// Define SITE_URL if not already defined (e.g. if config.php wasn't loaded)
// This is a fallback, ensure config.php is robustly included in your main flow.
if (!defined('SITE_URL')) {
    // Attempt to determine SITE_URL if possible, or use a hardcoded default.
    // This logic can be complex if the script is in a subdirectory.
    // For simplicity, assuming a fixed relative path to config or a default.
    $config_path = __DIR__ . '/../config/config.php';
    if (file_exists($config_path)) {
        require_once $config_path; // This will define SITE_URL
    } else {
        // Fallback if config.php is not found - this is problematic.
        // Define a sensible default or handle the error.
        // Adjust this to your actual base URL if needed.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script_path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        // Assuming logout.php is in uitm_achievements/authentication/, so go up one level for project root.
        define('SITE_URL', $protocol . $host . rtrim(dirname($script_path, 2), '/') . '/');
    }
}


// Unset all session variables
$_SESSION = array();

// If you want to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Set a success message for the login page (optional)
// Since session is destroyed, this needs to be passed via GET or handled differently if needed.
// Or, more simply, the login page can just have a generic "You have been logged out." message if it detects a logout parameter.

// Redirect to the login page
// Using the SITE_URL constant from config.php is preferred.
header("Location: " . SITE_URL . "authentication/login.php?logged_out=true");
exit;
?>
