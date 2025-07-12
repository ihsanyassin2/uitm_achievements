<?php
// forgot_password.php
$page_title = "Forgot Password - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

// If already logged in, redirect
if (is_logged_in()) {
    redirect(SITE_URL . (has_role('admin') ? 'admin/dashboard.php' : 'user/dashboard.php'));
}

$email = '';
$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validate_csrf_token()) {
        // Error message is set by the function
    } else {
        $email = sanitize_input($_POST['email']);

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!is_valid_uitm_email($email)) {
            $errors[] = "Invalid email format or not a uitm.edu.my email address.";
        }

        if (empty($errors)) {
            $db = db_connect();
            if ($db) {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                if (!$stmt) {
                    $errors[] = "Database query preparation error: " . $db->error;
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        // Generate a unique token for password reset
                        $reset_token = bin2hex(random_bytes(32));
                        $token_expiry = date("Y-m-d H:i:s", time() + 3600); // Token valid for 1 hour

                        // Store the token and its expiry in the database (you might need a new table 'password_resets' or add columns to 'users' table)
                        // For simplicity, assuming 'users' table has 'reset_token' and 'reset_token_expiry' columns
                        // ALTER TABLE users ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL, ADD COLUMN reset_token_expiry DATETIME DEFAULT NULL;
                        $stmt_update = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                        if (!$stmt_update) {
                             $errors[] = "Database query preparation error (update token): " . $db->error;
                        } else {
                            $stmt_update->bind_param("ssi", $reset_token, $token_expiry, $user['id']);
                            if ($stmt_update->execute()) {
                                // Send password reset email
                                $reset_link = SITE_URL . "authentication/reset_password.php?token=" . $reset_token;
                                $subject = "Password Reset Request - UiTM Achievements";
                                $message_body = "
                                <p>Hello,</p>
                                <p>You requested a password reset for your UiTM Achievements account.</p>
                                <p>Please click the link below to reset your password. This link is valid for 1 hour:</p>
                                <p><a href='{$reset_link}'>{$reset_link}</a></p>
                                <p>If you did not request this, please ignore this email.</p>
                                <p>Thanks,<br>UiTM Achievements Team</p>";

                                $headers = "MIME-Version: 1.0" . "\r\n";
                                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                $headers .= 'From: <noreply@uitm.edu.my>' . "\r\n"; // Replace with actual sender

                                // The mail() function's success is not guaranteed and depends on server config.
                                // For a production system, a robust email library (PHPMailer, SwiftMailer) is recommended.
                                if (mail($email, $subject, $message_body, $headers)) {
                                    $success_message = "A password reset link has been sent to your email address. Please check your inbox (and spam folder).";
                                } else {
                                    // This error might be sensitive, log it instead of showing to user directly for production
                                    $errors[] = "Could not send password reset email. Please contact support. (Mail server may not be configured)";
                                    error_log("Forgot Password Error: mail() function failed for $email.");
                                }
                            } else {
                                $errors[] = "Failed to store reset token. Please try again.";
                            }
                            $stmt_update->close();
                        }
                    } else {
                        // Email not found, but show a generic message for security (to prevent email enumeration)
                        $success_message = "If an account with that email exists, a password reset link has been sent. Please check your inbox (and spam folder).";
                    }
                    $stmt->close();
                }
                $db->close();
            } else {
                $errors[] = "Database connection error.";
            }
        }
    }
}
$csrf_token = generate_csrf_token();

// A new file `reset_password.php` will be needed to handle the token and allow new password entry.
// For now, this file only handles the request part.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>functions/style.css">
</head>
<body>
    <?php include_once SITE_ROOT . 'includes/navbar.php'; ?>

    <div class="container auth-container">
        <div class="auth-card">
            <img src="<?php echo SITE_URL; ?>assets/uitm_logo.png" alt="UiTM Logo" class="logo">
            <h3 class="text-center mb-4">Forgot Your Password?</h3>
            <p class="text-center text-muted">Enter your @uitm.edu.my email address and we'll send you a link to reset your password.</p>

            <?php
            display_message('error_message'); // For CSRF or other session errors
            if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success" role="alert">
                    <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($success_message)): // Only show form if success message is not displayed ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="yourname@uitm.edu.my" pattern=".+@uitm\.edu\.my$">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send Password Reset Link</button>
            </form>
            <?php endif; ?>
            <hr>
            <p class="text-center">Remember your password? <a href="<?php echo SITE_URL; ?>authentication/login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>functions/functions.js"></script>
</body>
</html>
