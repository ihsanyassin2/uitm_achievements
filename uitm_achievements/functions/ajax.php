<?php
// This file will handle AJAX requests.
// All AJAX requests should be routed through this file if possible,
// using a parameter to determine the action to perform.

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/config.php';
require_once 'functions.php'; // Contains get_pdo_connection() and other helpers

// Basic security: Check if it's an AJAX request (though this can be spoofed)
// if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
//     // http_response_code(403);
//     // die(json_encode(['error' => 'Forbidden - Direct access not allowed.']));
// }


header('Content-Type: application/json'); // All responses from this file will be JSON

$action = $_REQUEST['action'] ?? null; // Use $_REQUEST to handle both GET and POST for 'action'

$pdo = get_pdo_connection();
if (!$pdo) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error.']);
    exit;
}

$response = ['success' => false, 'message' => 'Invalid action.'];

switch ($action) {
    case 'example_action':
        // Example: Check if user is logged in for this action
        // if (!is_logged_in()) {
        //     http_response_code(401); // Unauthorized
        //     echo json_encode(['success' => false, 'message' => 'Authentication required.']);
        //     exit;
        // }

        // $data = $_POST['data'] ?? null;
        // if ($data) {
        //     // Process data, interact with database, etc.
        //     // For example:
        //     // $stmt = $pdo->prepare("INSERT INTO some_table (column) VALUES (?)");
        //     // $stmt->execute([$data]);
        //     $response = ['success' => true, 'message' => 'Data processed successfully.', 'received_data' => $data];
        // } else {
        //     $response = ['success' => false, 'message' => 'No data provided.'];
        // }
        $response = ['success' => true, 'message' => 'Example action executed.', 'data_received' => $_REQUEST];
        break;

    // Add more cases for different AJAX actions:
    // case 'like_achievement':
    //     // Handle liking an achievement
    //     break;
    // case 'submit_feedback_message':
    //     // Handle submitting a feedback message
    //     break;
    // case 'fetch_user_data':
    //     // Handle fetching user data
    //     break;
    // case 'check_email_exists':
    //     // Handle checking if an email exists during registration (real-time validation)
    //     break;

    default:
        http_response_code(400); // Bad Request
        $response = ['success' => false, 'message' => 'Unknown or missing action parameter.'];
        break;
}

echo json_encode($response);
exit;

?>
