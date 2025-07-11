<?php
$page_title = "Login - UiTM Achievements";
// Config and functions will be included by header.php
// No direct session_start() here, header.php handles it.

// If already logged in, redirect to appropriate dashboard
// This check should ideally be at the very top, before any output.
// We'll include config here to check session status early.
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php'; // For is_logged_in, has_role, redirect

if (is_logged_in()) {
    if (has_role('admin')) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } else {
        redirect(SITE_URL . 'user/dashboard.php');
    }
}

$email = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password']; // Do not sanitize password before hashing/verification

    // Validate email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    // Validate password
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    }

    if (empty($errors)) {
        $pdo = get_pdo_connection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Password is correct, start session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    set_flash_message("Login successful. Welcome back, " . htmlspecialchars($user['name']) . "!", "success");

                    // Redirect based on role
                    if ($user['role'] == 'admin') {
                        redirect(SITE_URL . 'admin/dashboard.php');
                    } else {
                        redirect(SITE_URL . 'user/dashboard.php');
                    }
                } else {
                    $errors['credentials'] = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                error_log("Login PDOException: " . $e->getMessage());
                $errors['database'] = "A database error occurred. Please try again later.";
            }
        } else {
            $errors['database'] = "Could not connect to the database.";
        }
    }
}

// Now include the header, as all pre-header logic is done.
include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="auth-container">
    <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
    <hr>

    <?php if (!empty($errors['credentials'])): ?>
        <div class="alert alert-danger"><?php echo $errors['credentials']; ?></div>
    <?php endif; ?>
    <?php if (!empty($errors['database'])): ?>
        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
    <?php endif; ?>

    <form action="<?php echo SITE_URL; ?>authentication/login.php" method="post" novalidate>
        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email address</label>
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>
        <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Login</button>
    </form>
    <hr>
    <div class="text-center">
        <p>Don't have an account? <a href="<?php echo SITE_URL; ?>authentication/register.php">Register here</a></p>
        <p><a href="<?php echo SITE_URL; ?>authentication/forgot_password.php">Forgot your password?</a></p>
        <p><a href="<?php echo SITE_URL; ?>public/index.php"><i class="fas fa-home"></i> Back to Home</a></p>
    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
