<?php
$page_title = "Forgot Password - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// If already logged in, redirect
if (is_logged_in()) {
    redirect(SITE_URL . (has_role('admin') ? 'admin/dashboard.php' : 'user/dashboard.php'));
}

$email = '';
$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (!is_uitm_email($email)) {
        // Silently ignore or give a generic message for non-UiTM emails to prevent enumeration
        // $errors['email'] = "Only UiTM emails are registered in this system.";
        // For now, let's be more direct for testing, but this might be a security consideration
         $errors['email'] = "This service is for @".UITM_EMAIL_DOMAIN." emails only.";
    }

    if (empty($errors)) {
        $pdo = get_pdo_connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user) {
                    // User found. Generate a password reset token.
                    // For a real system, you would:
                    // 1. Generate a unique, cryptographically secure token.
                    // 2. Store the token in the database with an expiry time, associated with the user's ID.
                    // 3. Send an email to the user with a link containing this token.
                    //    e.g., SITE_URL . 'authentication/reset_password.php?token=YOUR_UNIQUE_TOKEN'
                    // 4. The reset_password.php page would verify the token and allow password change.

                    // For this placeholder:
                    $success_message = "If an account with that email exists, a password reset link has been sent (Placeholder - Email sending not implemented).";
                    // In a real scenario, you wouldn't confirm if the email exists to prevent user enumeration.
                    // Just show the success message regardless.

                    // Example token generation (not for production without proper storage and expiry)
                    // $token = bin2hex(random_bytes(32));
                    // $expires = date("U") + 1800; // Token expires in 30 minutes
                    // $stmt_token = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
                    // $stmt_token->execute([$email, $token, $expires]);
                    // mail($email, "Password Reset Request", "Reset link: " . SITE_URL . "authentication/reset_password.php?token=" . $token);

                } else {
                    // No user found, or if you want to prevent enumeration, show the same success message.
                    $success_message = "If an account with that email exists, a password reset link has been sent (Placeholder - Email sending not implemented).";
                }
            } catch (PDOException $e) {
                error_log("Forgot Password PDOException: " . $e->getMessage());
                $errors['database'] = "A database error occurred. Please try again later.";
            }
        } else {
            $errors['database'] = "Could not connect to the database.";
        }
    }
}

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="auth-container">
    <h2><i class="fas fa-key"></i> Forgot Password</h2>
    <p class="text-muted text-center">Enter your UiTM email address and we'll (pretend to) send you a link to reset your password.</p>
    <hr>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (!empty($errors['database'])): ?>
        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
    <?php endif; ?>

    <?php if (!$success_message): // Only show form if no success message yet ?>
    <form action="<?php echo SITE_URL; ?>authentication/forgot_password.php" method="post" novalidate>
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Your UiTM Email Address</label>
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="yourname@uitm.edu.my">
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send Reset Link</button>
    </form>
    <?php endif; ?>
    <hr>
    <div class="text-center">
        <p>Remember your password? <a href="<?php echo SITE_URL; ?>authentication/login.php">Login here</a></p>
        <p><a href="<?php echo SITE_URL; ?>public/index.php"><i class="fas fa-home"></i> Back to Home</a></p>
    </div>
</div>

<?php
// We would also need a `reset_password.php` file to handle the token and actual password update.
// And a table `password_resets` (email, token, expires_at)
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
