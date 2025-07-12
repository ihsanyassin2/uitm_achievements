<?php
// ajax.php
// This file handles AJAX requests.
// All responses should be in JSON format.

header('Content-Type: application/json');

// Include configuration and core functions
// Adjust the path as necessary if this file is moved or config structure changes.
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    echo json_encode(['success' => false, 'message' => 'Critical: Configuration file not found.']);
    exit;
}

// Ensure functions.php is loaded (which should include db_connect and other utilities)
// config.php should ideally require functions.php. If not, require it here.
if (!function_exists('db_connect')) {
     $functions_path = __DIR__ . '/functions.php'; // Assuming it's in the same directory
     if(file_exists($functions_path)){
        require_once $functions_path;
     } else {
        echo json_encode(['success' => false, 'message' => 'Critical: Core functions file not found.']);
        exit;
     }
}


// Basic routing for AJAX actions
// All AJAX requests should include an 'action' parameter.
$action = isset($_REQUEST['action']) ? sanitize_input($_REQUEST['action']) : null;

// Response array
$response = ['success' => false, 'message' => 'Invalid action or request.'];

if (!$action) {
    echo json_encode($response);
    exit;
}

// --- Database Connection ---
$db = db_connect();
if (!$db) {
    $response['message'] = 'Database connection error.';
    echo json_encode($response);
    exit;
}

// --- CSRF Protection for POST requests ---
// For actions that modify data, CSRF token should be validated.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming your CSRF token is passed as 'csrf_token' in POST data
    // And validate_csrf_token() is defined in your functions.php
    if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
         // $response['message'] = 'CSRF token validation failed. Please refresh and try again.';
         // CSRF error message is set by validate_csrf_token() in session, let's use that
         if(isset($_SESSION['error_message'])){
            $response['message'] = $_SESSION['error_message'];
            unset($_SESSION['error_message']);
         } else {
            $response['message'] = 'Security token error. Please try again.';
         }
         echo json_encode($response);
         exit;
    }
}


// --- Action Handling ---
// Use a switch statement or if/else if to handle different actions.
switch ($action) {
    case 'example_ajax_action':
        // Example: require_login('user'); // Protect if needed
        // $data = isset($_POST['data']) ? sanitize_input($_POST['data']) : null;
        // if ($data) {
        //     // Process data...
        //     $response['success'] = true;
        //     $response['message'] = 'Data processed successfully.';
        //     $response['data_received'] = $data;
        // } else {
        //     $response['message'] = 'No data provided.';
        // }
        $response = ['success' => true, 'message' => 'AJAX Handler Works!', 'action_called' => $action];
        break;

    case 'like_achievement':
        // Required: achievement_id
        // Optional: user_id if likes are tied to logged-in users, otherwise session_id for anonymous
        $achievement_id = isset($_POST['achievement_id']) ? intval($_POST['achievement_id']) : 0;

        if ($achievement_id > 0) {
            // For anonymous likes, use session_id
            $session_id = session_id(); // PHP's session ID

            // Check if already liked
            $stmt_check = $db->prepare("SELECT id FROM likes WHERE achievement_id = ? AND session_id = ?");
            $stmt_check->bind_param("is", $achievement_id, $session_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // Already liked, so unlike (remove the like)
                $stmt_delete = $db->prepare("DELETE FROM likes WHERE achievement_id = ? AND session_id = ?");
                $stmt_delete->bind_param("is", $achievement_id, $session_id);
                if ($stmt_delete->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Unliked';
                    $response['liked'] = false;
                } else {
                    $response['message'] = 'Error unliking achievement.';
                }
                $stmt_delete->close();
            } else {
                // Not liked yet, so add like
                $stmt_insert = $db->prepare("INSERT INTO likes (achievement_id, session_id) VALUES (?, ?)");
                $stmt_insert->bind_param("is", $achievement_id, $session_id);
                if ($stmt_insert->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Liked';
                    $response['liked'] = true;
                } else {
                    $response['message'] = 'Error liking achievement.';
                }
                $stmt_insert->close();
            }
            $stmt_check->close();

            // Get updated like count
            if ($response['success']) {
                $stmt_count = $db->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE achievement_id = ?");
                $stmt_count->bind_param("i", $achievement_id);
                $stmt_count->execute();
                $result_count = $stmt_count->get_result()->fetch_assoc();
                $response['like_count'] = $result_count['like_count'];
                $stmt_count->close();
            }

        } else {
            $response['message'] = 'Invalid achievement ID.';
        }
        break;

    // Add more cases for other AJAX actions:
    // case 'submit_comment':
    // case 'load_more_items':
    // case 'check_email_exists':
    // etc.

    default:
        // $response['message'] is already 'Invalid action or request.'
        break;
}

// Close the database connection if it was opened
if ($db) {
    $db->close();
}

// Send the JSON response
echo json_encode($response);
exit;
?>
