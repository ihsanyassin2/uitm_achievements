<?php
// This file will contain reusable PHP functions.
// Ensure config is loaded if not already, especially for DB and SITE_URL
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$configPath = dirname(__FILE__) . '/../config/config.php';
if (file_exists($configPath) && !defined('DB_HOST')) { // Check if config constants are loaded
    require_once $configPath;
} elseif (!defined('DB_HOST')) {
    die("Critical error: config.php not found and DB constants are not defined in functions.php.");
}


/**
 * Database Connection Function (PDO)
 * @return PDO|null Returns a PDO connection object or null on failure.
 */
function get_pdo_connection() {
    // Check if a connection already exists in global scope to reuse it
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }

    $host = DB_HOST;
    $db   = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $GLOBALS['pdo'] = new PDO($dsn, $user, $pass, $options);
        return $GLOBALS['pdo'];
    } catch (\PDOException $e) {
        // Log error or handle more gracefully in production
        // For development, it's fine to throw the exception or echo error.
        error_log("PDO Connection Error: " . $e->getMessage());
        // In a real app, you might redirect to an error page or show a generic message.
        // For now, let's set a session message if possible, or just die.
        if (session_status() !== PHP_SESSION_NONE) {
             $_SESSION['message'] = "Database connection error. Please try again later or contact support.";
             $_SESSION['message_type'] = "danger";
        }
        // Depending on where this is called, redirect might be problematic.
        // Consider throwing an exception to be caught by the caller.
        // For now, returning null will force calling code to check.
        return null;
        // throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

/**
 * Redirects to a given URL.
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Sets a session flash message.
 * @param string $message The message content.
 * @param string $type The message type (e.g., 'success', 'danger', 'warning', 'info').
 */
function set_flash_message($message, $type) {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Checks if a user is logged in.
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Checks if the logged-in user has a specific role.
 * @param string|array $role The role(s) to check against.
 * @return bool True if the user has the role, false otherwise.
 */
function has_role($role) {
    if (!is_logged_in()) {
        return false;
    }
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    return $_SESSION['user_role'] === $role;
}

/**
 * Restricts page access based on login status and optionally role.
 * If access is denied, redirects to login page or a specified redirect URL.
 * @param string|array|null $roleRequired Role(s) required to access. If null, just checks login.
 * @param string $redirect_url URL to redirect to if access is denied. Defaults to login page.
 */
function protect_page($roleRequired = null, $redirect_url = null) {
    if (!is_logged_in()) {
        set_flash_message("You must be logged in to view this page.", "warning");
        redirect($redirect_url ?? SITE_URL . 'authentication/login.php');
    }
    if ($roleRequired !== null && !has_role($roleRequired)) {
        set_flash_message("You do not have permission to access this page.", "danger");
        // Redirect to a generic 'access denied' page or user dashboard
        redirect(SITE_URL . (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'user' ? 'user/dashboard.php' : 'public/index.php'));
    }
}


/**
 * Validates if an email is from the @uitm.edu.my domain.
 * @param string $email The email to validate.
 * @return bool True if valid UiTM email, false otherwise.
 */
function is_uitm_email($email) {
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return strtolower(substr($email, -strlen('@' . UITM_EMAIL_DOMAIN))) === strtolower('@' . UITM_EMAIL_DOMAIN);
}

/**
 * Sanitize input data.
 * @param string $data The data to sanitize.
 * @return string Sanitized data.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Helper function to determine active page for sidebar/navbar styling.
 * Compares the current script's path with the provided page name.
 * @param string $page_name The page name/path to check (e.g., 'user/dashboard.php').
 * @return string 'active' if current page matches, otherwise empty string.
 */
if (!function_exists('isActivePage')) { // Ensure it's not declared elsewhere (e.g. sidebars)
    function isActivePage($page_name) {
        $current_page_full_path = $_SERVER['PHP_SELF'];
        // Check if the provided page_name is part of the current script's path
        // This is a basic check; for more complex routing, a router would be better.
        if (strpos($current_page_full_path, $page_name) !== false) {
             return 'active';
        }
        return '';
    }
}

// Add more reusable functions here as the project develops, e.g.:
// - CRUD helper functions (find, save, delete records)
// - File upload handlers
// - Pagination helpers
// - Date formatting functions
// - etc.

?>
