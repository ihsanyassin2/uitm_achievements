<?php
// reset_password.php
$page_title = "Reset Password - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

$token = isset($_GET['token']) ? sanitize_input($_GET['token']) : null;
$errors = [];
$success_message = '';
$show_form = false;

if (empty($token)) {
    $_SESSION['error_message'] = "Invalid or missing password reset token.";
    redirect(SITE_URL . 'authentication/login.php');
}

// Validate token
$db = db_connect();
if (!$db) {
    $errors[] = "Database connection error.";
} else {
    $current_time = date("Y-m-d H:i:s");
    $stmt = $db->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expiry > ?");
    if (!$stmt) {
        $errors[] = "Database query error (token validation): " . $db->error;
    } else {
        $stmt->bind_param("ss", $token, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $show_form = true; // Token is valid and not expired
        } else {
            $errors[] = "Invalid or expired password reset token. Please request a new one.";
            // Optionally, clear the token if it's invalid or found but expired to prevent reuse
            $stmt_clear = $db->prepare("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
            if ($stmt_clear) {
                $stmt_clear->bind_param("s", $token);
                $stmt_clear->execute();
                $stmt_clear->close();
            }
        }
        $stmt->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $show_form) {
    if (!validate_csrf_token()) {
        // Error message is set by the function
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($password)) {
            $errors[] = "New password is required.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        // Add more password complexity rules if needed

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        if (empty($errors)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Update password and clear reset token
            $stmt_update = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            if (!$stmt_update) {
                $errors[] = "Database query error (update password): " . $db->error;
            } else {
                $stmt_update->bind_param("si", $hashed_password, $user['id']);
                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Your password has been reset successfully. You can now log in with your new password.";
                    redirect(SITE_URL . 'authentication/login.php');
                } else {
                    $errors[] = "Failed to update password. Please try again.";
                }
                $stmt_update->close();
            }
        }
    }
}

if ($db) {
    $db->close();
}
$csrf_token = generate_csrf_token();
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
            <h3 class="text-center mb-4">Set New Password</h3>

            <?php
            display_message('error_message'); // For CSRF or other session errors
            if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                 <div class="alert alert-success" role="alert">
                    <p class="mb-0"><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>


            <?php if ($show_form && empty($success_message)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?token=<?php echo htmlspecialchars($token); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimum 8 characters">
                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-enter your new password">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-key"></i> Reset Password</button>
            </form>
            <?php elseif (!$show_form && empty($errors) && empty($success_message)): ?>
                <div class="alert alert-info">Validating token...</div>
            <?php endif; ?>

            <?php if (!$show_form || !empty($success_message)): ?>
                 <hr>
                <p class="text-center"><a href="<?php echo SITE_URL; ?>authentication/login.php">Back to Login</a></p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>functions/functions.js"></script>
</body>
</html>
