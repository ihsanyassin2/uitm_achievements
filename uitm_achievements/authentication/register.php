<?php
$page_title = "Register - UiTM Achievements";
// Config and functions will be included by header.php
// No direct session_start() here, header.php handles it.

// If already logged in, redirect to appropriate dashboard
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

if (is_logged_in()) {
    if (has_role('admin')) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } else {
        redirect(SITE_URL . 'user/dashboard.php');
    }
}

$name = '';
$email = '';
$phone_number = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $password = $_POST['password']; // Do not sanitize password before hashing
    $confirm_password = $_POST['confirm_password'];

    // Validate Name
    if (empty($name)) {
        $errors['name'] = "Full Name is required.";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters long.";
    }

    // Validate Email
    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    } elseif (!is_uitm_email($email)) {
        $errors['email'] = "Only emails from @" . UITM_EMAIL_DOMAIN . " are allowed.";
    } else {
        // Check if email already exists
        $pdo_check = get_pdo_connection();
        if ($pdo_check) {
            $stmt = $pdo_check->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = "This email address is already registered.";
            }
        } else {
            $errors['database'] = "Database connection error during email check.";
        }
    }

    // Validate Phone Number (optional, but if provided, basic validation)
    if (!empty($phone_number) && !preg_match('/^[0-9\-\+\s\(\)]{7,20}$/', $phone_number)) {
        $errors['phone_number'] = "Invalid phone number format.";
    }

    // Validate Password
    if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Password must include at least one uppercase letter, one lowercase letter, and one number.";
    }


    // Validate Confirm Password
    if (empty($confirm_password)) {
        $errors['confirm_password'] = "Please confirm your password.";
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $pdo_insert = get_pdo_connection(); // Get a fresh connection or reuse if $pdo_check is global and still valid
        if ($pdo_insert) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $default_role = 'user'; // New registrations are always 'user'

            try {
                $stmt = $pdo_insert->prepare("INSERT INTO users (name, email, password, phone_number, role) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $email, $hashed_password, $phone_number, $default_role])) {
                    // Optionally, log the user in directly or send to login page
                    // For now, redirect to login with a success message
                    set_flash_message("Registration successful! Please login with your new account.", "success");
                    redirect(SITE_URL . 'authentication/login.php');
                } else {
                    $errors['database'] = "Failed to register user. Please try again.";
                }
            } catch (PDOException $e) {
                error_log("Registration PDOException: " . $e->getMessage());
                // Check for unique constraint violation specifically, though email check should catch it
                if ($e->getCode() == 23000) { // SQLSTATE[23000]: Integrity constraint violation
                     $errors['email'] = "This email address is already registered (database error).";
                } else {
                    $errors['database'] = "A database error occurred during registration. Please try again later.";
                }
            }
        } else {
             $errors['database'] = "Could not connect to the database for registration.";
        }
    }
}

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="auth-container">
    <h2><i class="fas fa-user-plus"></i> Create Account</h2>
    <p class="text-muted text-center">Join UiTM Achievements to showcase your success!</p>
    <hr>

    <?php if (!empty($errors['database'])): ?>
        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
    <?php endif; ?>

    <form action="<?php echo SITE_URL; ?>authentication/register.php" method="post" novalidate>
        <div class="form-group">
            <label for="name"><i class="fas fa-user"></i> Full Name</label>
            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            <?php if (isset($errors['name'])): ?>
                <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> UiTM Email Address (e.g., yourname@uitm.edu.my)</label>
            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required placeholder="example@uitm.edu.my">
            <small class="form-text text-muted">Only emails ending with <strong>@<?php echo UITM_EMAIL_DOMAIN; ?></strong> are allowed.</small>
            <?php if (isset($errors['email'])): ?>
                <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="phone_number"><i class="fas fa-phone"></i> Phone Number (Optional)</label>
            <input type="tel" class="form-control <?php echo isset($errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($phone_number); ?>" placeholder="e.g., 012-3456789">
            <?php if (isset($errors['phone_number'])): ?>
                <div class="invalid-feedback"><?php echo $errors['phone_number']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password"><i class="fas fa-lock"></i> Password</label>
            <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
            <small class="form-text text-muted">Min. 8 characters, with uppercase, lowercase, and a number.</small>
            <?php if (isset($errors['password'])): ?>
                <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm Password</label>
            <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
            <?php if (isset($errors['confirm_password'])): ?>
                <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-user-plus"></i> Register</button>
    </form>
    <hr>
    <div class="text-center">
        <p>Already have an account? <a href="<?php echo SITE_URL; ?>authentication/login.php">Login here</a></p>
        <p><a href="<?php echo SITE_URL; ?>public/index.php"><i class="fas fa-home"></i> Back to Home</a></p>
    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
