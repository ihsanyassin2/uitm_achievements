<?php
// login.php
$page_title = "Login - UiTM Achievements";
// Attempt to include config.php. Using __DIR__ to ensure correct path.
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}


// If already logged in, redirect to appropriate dashboard
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
    // Validate CSRF token
    if (!validate_csrf_token()) {
        // Error message is set in session by validate_csrf_token function
        // No need to set another one here, just prevent further processing.
        // $errors[] = "Invalid security token. Please try again.";
    } else {
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password']; // Password will be hashed and compared, not sanitized like other inputs

        // Basic validation
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        if (empty($password)) {
            $errors[] = "Password is required.";
        }

        if (empty($errors)) {
            $db = db_connect();
            if ($db) {
                $stmt = $db->prepare("SELECT id, uitm_id, password, full_name, role FROM users WHERE email = ?");
                if (!$stmt) {
                    $errors[] = "Database query preparation error: " . $db->error;
                } else {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows == 1) {
                        $user = $result->fetch_assoc();
                        if (password_verify($password, $user['password'])) {
                            // Password is correct, set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_uitm_id'] = $user['uitm_id'];
                            $_SESSION['user_full_name'] = $user['full_name'];
                            $_SESSION['user_email'] = $email; // Store email in session as well
                            $_SESSION['user_role'] = $user['role'];
                            $_SESSION['login_time'] = time();

                            // Regenerate session ID for security
                            session_regenerate_id(true);

                            $_SESSION['success_message'] = "Login successful. Welcome back, " . htmlspecialchars($user['full_name']) . "!";

                            // Redirect to appropriate dashboard
                            if ($user['role'] == 'admin') {
                                redirect(SITE_URL . 'admin/dashboard.php');
                            } else {
                                redirect(SITE_URL . 'user/dashboard.php');
                            }
                        } else {
                            $errors[] = "Invalid email or password.";
                        }
                    } else {
                        $errors[] = "Invalid email or password."; // User not found
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

// Generate CSRF token for the form
$csrf_token = generate_csrf_token();

// Include header (will set page title and include common HTML head)
// Since this is an auth page, it might use a simpler header or the main one
// For now, using a simplified structure without sidebars.
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
    <?php include_once SITE_ROOT . 'includes/navbar.php'; // Use the main navbar ?>

    <div class="container auth-container">
        <div class="auth-card">
            <img src="<?php echo SITE_URL; ?>assets/uitm_logo.png" alt="UiTM Logo" class="logo">
            <h3 class="text-center mb-4">Login to Your Account</h3>

            <?php
            // Display session messages (e.g., from CSRF validation failure)
            display_message('error_message');
            display_message('success_message'); // e.g. if redirected from registration

            if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach ($errors as $error): ?>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="yourname@uitm.edu.my">
                </div>
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter your password">
                </div>
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-sign-in-alt"></i> Login</button>
                <div class="mt-3 text-center">
                    <a href="<?php echo SITE_URL; ?>authentication/forgot_password.php">Forgot Password?</a>
                </div>
            </form>
            <hr>
            <p class="text-center">Don't have an account? <a href="<?php echo SITE_URL; ?>authentication/register.php">Register here</a></p>
        </div>
    </div>

    <?php
    // A simplified footer for auth pages or use the main one
    // include_once SITE_ROOT . 'includes/footer.php';
    ?>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>functions/functions.js"></script>
</body>
</html>
