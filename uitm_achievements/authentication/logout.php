<?php
// Start session to access session variables
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Load configuration for SITE_URL (needed for redirect)
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php'; // For set_flash_message & redirect

// Unset all session variables
$_SESSION = array();

// If session cookies are used, delete the session cookie.
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

// Set a flash message for the user (optional)
// Since the session is destroyed, we need to start a new one to store the flash message.
// This is a common pattern: destroy old, start new for flash, then redirect.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
set_flash_message("You have been successfully logged out.", "success");

// Redirect to the login page or homepage
redirect(SITE_URL . 'authentication/login.php');
exit;
?>
