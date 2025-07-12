<?php
// register.php
$page_title = "Register - UiTM Achievements";
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

// Initialize variables
$uitm_id = '';
$full_name = '';
$email = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validate_csrf_token()) {
        // Error message is set by the function
    } else {
        $uitm_id = sanitize_input($_POST['uitm_id']);
        $full_name = sanitize_input($_POST['full_name']);
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password']; // Not sanitized, will be hashed
        $confirm_password = $_POST['confirm_password'];

        // Validation
        if (empty($uitm_id)) {
            $errors[] = "UiTM ID is required.";
        } // Add more specific validation for UiTM ID format if known

        if (empty($full_name)) {
            $errors[] = "Full Name is required.";
        }

        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!is_valid_uitm_email($email)) {
            $errors[] = "Invalid email format or not a uitm.edu.my email address.";
        }

        if (empty($password)) {
            $errors[] = "Password is required.";
        } elseif (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long.";
        }
        // Add more password complexity rules if needed (e.g., uppercase, number, special char)

        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // If no validation errors, proceed to check database
        if (empty($errors)) {
            $db = db_connect();
            if ($db) {
                // Check if email or UiTM ID already exists
                $stmt_check = $db->prepare("SELECT id FROM users WHERE email = ? OR uitm_id = ?");
                if (!$stmt_check) {
                     $errors[] = "Database query preparation error (check): " . $db->error;
                } else {
                    $stmt_check->bind_param("ss", $email, $uitm_id);
                    $stmt_check->execute();
                    $result_check = $stmt_check->get_result();

                    if ($result_check->num_rows > 0) {
                        // Check specifically which one exists to give a more precise error.
                        // This makes an assumption that uitm_id and email are unique across the table.
                        // The schema enforces this with UNIQUE constraints.
                        $existing_data = $result_check->fetch_assoc(); // Not strictly needed here with the new specific checks

                        $stmt_check_specific = $db->prepare("SELECT email, uitm_id FROM users WHERE email = ? OR uitm_id = ?");
                        $stmt_check_specific->bind_param("ss", $email, $uitm_id);
                        $stmt_check_specific->execute();
                        $conflicts = $stmt_check_specific->get_result()->fetch_all(MYSQLI_ASSOC);
                        $stmt_check_specific->close();

                        foreach($conflicts as $conflict) {
                            if ($conflict['email'] === $email) {
                                $errors[] = "Email address already registered.";
                            }
                            if ($conflict['uitm_id'] === $uitm_id) {
                                $errors[] = "UiTM ID already registered.";
                            }
                        }
                        // Remove duplicates if both matched the same record (though schema should prevent distinct records with same email/uitm_id)
                        $errors = array_unique($errors);

                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Insert new user (default role is 'user')
                        $stmt_insert = $db->prepare("INSERT INTO users (uitm_id, full_name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
                        if (!$stmt_insert) {
                            $errors[] = "Database query preparation error (insert): " . $db->error;
                        } else {
                            $stmt_insert->bind_param("ssss", $uitm_id, $full_name, $email, $hashed_password);
                            if ($stmt_insert->execute()) {
                                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                                // Optionally, log the user in directly or send a verification email
                                redirect(SITE_URL . 'authentication/login.php');
                            } else {
                                $errors[] = "Registration failed. Please try again. Error: " . $stmt_insert->error;
                            }
                            $stmt_insert->close();
                        }
                    }
                    $stmt_check->close();
                }
                $db->close();
            } else {
                $errors[] = "Database connection error.";
            }
        }
    }
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
            <h3 class="text-center mb-4">Create Your Account</h3>

            <?php
            display_message('error_message'); // For CSRF or other session errors
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
                    <label for="uitm_id"><i class="fas fa-id-card"></i> UiTM ID (Staff/Student ID)</label>
                    <input type="text" class="form-control" id="uitm_id" name="uitm_id" value="<?php echo htmlspecialchars($uitm_id); ?>" required placeholder="e.g., 2023xxxxxx">
                </div>

                <div class="form-group">
                    <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email address (@uitm.edu.my only)</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="yourname@uitm.edu.my" pattern=".+@uitm\.edu\.my$" title="Please use your @uitm.edu.my email address.">
                </div>

                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Minimum 8 characters">
                    <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Re-enter your password">
                </div>

                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Register</button>
            </form>
            <hr>
            <p class="text-center">Already have an account? <a href="<?php echo SITE_URL; ?>authentication/login.php">Login here</a></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="<?php echo SITE_URL; ?>functions/functions.js"></script>
</body>
</html>
