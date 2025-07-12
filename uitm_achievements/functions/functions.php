<?php
// functions.php
// This file will contain reusable PHP functions for the project.

// Ensure config is loaded if not already (e.g. if a function here needs DB constants)
// Note: This might cause issues if functions.php is included before config.php in some entry scripts.
// It's generally better to ensure config.php is included at the very beginning of your application flow (e.g., in index.php or header.php).
if (!defined('DB_HOST')) {
    $config_path = __DIR__ . '/../config/config.php';
    if (file_exists($config_path)) {
        require_once $config_path;
    } else {
        // Handle error: config not found. This is critical for DB functions.
        // error_log("Critical: functions.php could not load config.php");
        // die("Configuration error. Please contact administrator.");
    }
}


/**
 * Establishes a database connection.
 * @return mysqli|false A mysqli object on success, or false on failure.
 */
function db_connect() {
    static $connection; // Static variable to hold the connection

    if (!isset($connection)) {
        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
            error_log("Database configuration constants are not defined.");
            return false;
        }

        $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($connection->connect_error) {
            error_log("Database connection failed: " . $connection->connect_error);
            return false;
        }
        $connection->set_charset("utf8mb4");
    }
    return $connection;
}

/**
 * Sanitizes input data to prevent XSS.
 * @param string $data The input data.
 * @return string The sanitized data.
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Redirects to a given URL.
 * @param string $url The URL to redirect to.
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
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
 * @param string $role The role to check for (e.g., 'admin', 'user').
 * @return bool True if the user has the role, false otherwise.
 */
function has_role($role) {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Restricts page access to logged-in users.
 * Optionally, can restrict to a specific role.
 * @param string|null $role If provided, user must also have this role.
 * @param string $redirect_url URL to redirect to if access is denied. Defaults to login page.
 */
function require_login($role = null, $redirect_url = null) {
    if (!is_logged_in()) {
        $_SESSION['error_message'] = "You must be logged in to view this page.";
        redirect($redirect_url ?? SITE_URL . 'authentication/login.php');
    }
    if ($role !== null && !has_role($role)) {
        $_SESSION['error_message'] = "You do not have permission to access this page.";
        // Redirect to a generic 'access denied' page or user dashboard
        redirect(SITE_URL . (has_role('user') ? 'user/dashboard.php' : 'public/index.php'));
    }
}


/**
 * Displays a session-based message (e.g., success or error).
 * @param string $type The type of message ('success_message', 'error_message', 'info_message').
 */
function display_message($type = 'error_message') {
    if (isset($_SESSION[$type])) {
        $alert_class = 'alert-danger'; // Default for error
        if ($type === 'success_message') {
            $alert_class = 'alert-success';
        } elseif ($type === 'info_message') {
            $alert_class = 'alert-info';
        }
        echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($_SESSION[$type]);
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        echo '</div>';
        unset($_SESSION[$type]);
    }
}

/**
 * Generates a CSRF token and stores it in the session.
 * @return string The generated CSRF token.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a CSRF token from POST data.
 * @param string $token_name The name of the POST field containing the token. Defaults to 'csrf_token'.
 * @return bool True if valid, false otherwise.
 */
function validate_csrf_token($token_name = 'csrf_token') {
    if (isset($_POST[$token_name]) && isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $_POST[$token_name])) {
        // Token is valid, unset it to prevent reuse (optional, depends on strategy)
        // unset($_SESSION['csrf_token']);
        return true;
    }
    // Log CSRF attempt or set error message
    $_SESSION['error_message'] = "Invalid security token. Please try again.";
    error_log("CSRF token validation failed. Submitted: " . ($_POST[$token_name] ?? 'NOT_SET') . ", Session: " . ($_SESSION['csrf_token'] ?? 'NOT_SET'));
    return false;
}

/**
 * Validates an email address, specifically for @uitm.edu.my domain.
 * @param string $email The email address to validate.
 * @return bool True if valid, false otherwise.
 */
function is_valid_uitm_email($email) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if the domain is uitm.edu.my
        if (preg_match('/@uitm\.edu\.my$/i', $email)) {
            return true;
        }
    }
    return false;
}


/**
 * Fetches a setting value from the database.
 * @param string $setting_name The name of the setting.
 * @return string|null The setting value or null if not found or on error.
 */
function get_setting($setting_name) {
    $db = db_connect();
    if (!$db) return null;

    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_name = ?");
    if (!$stmt) {
        error_log("Prepare failed for get_setting: (" . $db->errno . ") " . $db->error);
        return null;
    }
    $stmt->bind_param("s", $setting_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['setting_value'];
    }
    $stmt->close();
    return null; // Setting not found
}


// Add more functions as needed:
// - File upload handling
// - Pagination generation
// - Date formatting
// - Getting user details
// - Logging functions

?>
