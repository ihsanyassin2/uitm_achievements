<?php
$page_title = "Update Profile - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: User or Admin role required
protect_page(['user', 'admin']);

$user_id = $_SESSION['user_id'];
$pdo = get_pdo_connection();
$user_data = null;
$errors = [];
$success_message = '';

// Fetch current user data
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT name, email, phone_number, biography, cv_link, google_scholar_link, linkedin_link, scopus_link, isi_link, orcid_link FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            set_flash_message("User data not found.", "danger");
            redirect(SITE_URL . 'user/dashboard.php'); // Or an error page
        }
    } catch (PDOException $e) {
        error_log("Update Profile (fetch) PDOException: " . $e->getMessage());
        set_flash_message("Error fetching profile data: " . $e->getMessage(), "danger");
        // Allow page to load with empty form or redirect, depending on desired UX
    }
} else {
    set_flash_message("Database connection error.", "danger");
    // Allow page to load with empty form or redirect
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_data) {
    // Sanitize and retrieve form data
    $name = sanitize_input($_POST['name']);
    // Email cannot be changed through this form as per README ("email change needs to come from admin")
    // If it were allowed: $email = sanitize_input($_POST['email']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $biography = sanitize_input($_POST['biography']); // Or use a more permissive sanitizer for HTML if allowed
    $cv_link = filter_var(sanitize_input($_POST['cv_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['cv_link']) : '';
    $google_scholar_link = filter_var(sanitize_input($_POST['google_scholar_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['google_scholar_link']) : '';
    $linkedin_link = filter_var(sanitize_input($_POST['linkedin_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['linkedin_link']) : '';
    $scopus_link = filter_var(sanitize_input($_POST['scopus_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['scopus_link']) : '';
    $isi_link = filter_var(sanitize_input($_POST['isi_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['isi_link']) : '';
    $orcid_link = filter_var(sanitize_input($_POST['orcid_link']), FILTER_VALIDATE_URL) ? sanitize_input($_POST['orcid_link']) : '';

    // Validate Name
    if (empty($name)) {
        $errors['name'] = "Full Name is required.";
    } elseif (strlen($name) < 3) {
        $errors['name'] = "Name must be at least 3 characters long.";
    }

    // Validate Phone Number (optional, but if provided, basic validation)
    if (!empty($phone_number) && !preg_match('/^[0-9\-\+\s\(\)]{7,20}$/', $phone_number)) {
        $errors['phone_number'] = "Invalid phone number format.";
    }

    // Validate URLs (simple check, more robust validation might be needed if specific formats are expected)
    $url_fields = [
        'cv_link' => $cv_link, 'google_scholar_link' => $google_scholar_link,
        'linkedin_link' => $linkedin_link, 'scopus_link' => $scopus_link,
        'isi_link' => $isi_link, 'orcid_link' => $orcid_link
    ];
    foreach ($url_fields as $field_name => $url_value) {
        if (!empty($url_value) && !filter_var($url_value, FILTER_VALIDATE_URL)) {
            $errors[$field_name] = "Please enter a valid URL (e.g., http://example.com).";
        }
    }


    if (empty($errors)) {
        if ($pdo) {
            try {
                $sql = "UPDATE users SET
                            name = :name,
                            phone_number = :phone_number,
                            biography = :biography,
                            cv_link = :cv_link,
                            google_scholar_link = :google_scholar_link,
                            linkedin_link = :linkedin_link,
                            scopus_link = :scopus_link,
                            isi_link = :isi_link,
                            orcid_link = :orcid_link,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':phone_number', $phone_number);
                $stmt->bindParam(':biography', $biography);
                $stmt->bindParam(':cv_link', $cv_link);
                $stmt->bindParam(':google_scholar_link', $google_scholar_link);
                $stmt->bindParam(':linkedin_link', $linkedin_link);
                $stmt->bindParam(':scopus_link', $scopus_link);
                $stmt->bindParam(':isi_link', $isi_link);
                $stmt->bindParam(':orcid_link', $orcid_link);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $success_message = "Profile updated successfully!";
                    set_flash_message($success_message, "success");
                    // Refresh user data to show updated values in form
                    $stmt_refresh = $pdo->prepare("SELECT name, email, phone_number, biography, cv_link, google_scholar_link, linkedin_link, scopus_link, isi_link, orcid_link FROM users WHERE id = ?");
                    $stmt_refresh->execute([$user_id]);
                    $user_data = $stmt_refresh->fetch(PDO::FETCH_ASSOC);
                    // Update session name if it changed
                    $_SESSION['user_name'] = $name;
                } else {
                    $errors['database'] = "Failed to update profile. Please try again.";
                }
            } catch (PDOException $e) {
                error_log("Update Profile (update) PDOException: " . $e->getMessage());
                $errors['database'] = "A database error occurred: " . $e->getMessage();
            }
        } else {
            $errors['database'] = "Database connection error.";
        }
    }
     if (!empty($errors)) {
        $error_message_summary = "Please correct the errors below.";
        set_flash_message($error_message_summary, "danger");
    }
}


include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-user-edit"></i> Update Profile</h2>
        <p>Keep your information up to date.</p>
        <hr>

        <?php if (!$user_data && !$pdo): ?>
            <div class="alert alert-danger">Could not load profile data due to a database connection error. Please try again later.</div>
        <?php elseif (!$user_data): ?>
            <div class="alert alert-warning">Profile data could not be loaded.</div>
        <?php else: ?>

        <form action="<?php echo SITE_URL; ?>user/update_profile.php" method="post" novalidate>
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                        <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?php echo $errors['name']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" class="form-control" id="email" name="email_display" value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" readonly>
                        <small class="form-text text-muted">Email address cannot be changed here. Please contact an administrator if a change is needed.</small>
                    </div>

                    <div class="form-group">
                        <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" class="form-control <?php echo isset($errors['phone_number']) ? 'is-invalid' : ''; ?>" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user_data['phone_number'] ?? ''); ?>">
                        <?php if (isset($errors['phone_number'])): ?><div class="invalid-feedback"><?php echo $errors['phone_number']; ?></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Professional Information</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="biography"><i class="fas fa-book-reader"></i> Biography / About Me</label>
                        <textarea class="form-control" id="biography" name="biography" rows="5"><?php echo htmlspecialchars($user_data['biography'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="cv_link"><i class="fas fa-file-pdf"></i> CV Link (URL)</label>
                        <input type="url" class="form-control <?php echo isset($errors['cv_link']) ? 'is-invalid' : ''; ?>" id="cv_link" name="cv_link" value="<?php echo htmlspecialchars($user_data['cv_link'] ?? ''); ?>" placeholder="https://example.com/your-cv.pdf">
                        <?php if (isset($errors['cv_link'])): ?><div class="invalid-feedback"><?php echo $errors['cv_link']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="google_scholar_link"><i class="fab fa-google"></i> Google Scholar Profile Link</label>
                        <input type="url" class="form-control <?php echo isset($errors['google_scholar_link']) ? 'is-invalid' : ''; ?>" id="google_scholar_link" name="google_scholar_link" value="<?php echo htmlspecialchars($user_data['google_scholar_link'] ?? ''); ?>" placeholder="https://scholar.google.com/citations?user=...">
                        <?php if (isset($errors['google_scholar_link'])): ?><div class="invalid-feedback"><?php echo $errors['google_scholar_link']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="linkedin_link"><i class="fab fa-linkedin"></i> LinkedIn Profile Link</label>
                        <input type="url" class="form-control <?php echo isset($errors['linkedin_link']) ? 'is-invalid' : ''; ?>" id="linkedin_link" name="linkedin_link" value="<?php echo htmlspecialchars($user_data['linkedin_link'] ?? ''); ?>" placeholder="https://linkedin.com/in/yourprofile">
                        <?php if (isset($errors['linkedin_link'])): ?><div class="invalid-feedback"><?php echo $errors['linkedin_link']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="scopus_link"><i class="fas fa-atom"></i> Scopus Author ID Link</label>
                        <input type="url" class="form-control <?php echo isset($errors['scopus_link']) ? 'is-invalid' : ''; ?>" id="scopus_link" name="scopus_link" value="<?php echo htmlspecialchars($user_data['scopus_link'] ?? ''); ?>" placeholder="https://www.scopus.com/authid/detail.uri?authorId=...">
                        <?php if (isset($errors['scopus_link'])): ?><div class="invalid-feedback"><?php echo $errors['scopus_link']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="isi_link"><i class="fas fa-flask"></i> ISI/Web of Science (ResearcherID) Link</label>
                        <input type="url" class="form-control <?php echo isset($errors['isi_link']) ? 'is-invalid' : ''; ?>" id="isi_link" name="isi_link" value="<?php echo htmlspecialchars($user_data['isi_link'] ?? ''); ?>" placeholder="https://www.webofscience.com/wos/author/record/...">
                        <?php if (isset($errors['isi_link'])): ?><div class="invalid-feedback"><?php echo $errors['isi_link']; ?></div><?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="orcid_link"><i class="fab fa-orcid"></i> ORCID Link</label>
                        <input type="url" class="form-control <?php echo isset($errors['orcid_link']) ? 'is-invalid' : ''; ?>" id="orcid_link" name="orcid_link" value="<?php echo htmlspecialchars($user_data['orcid_link'] ?? ''); ?>" placeholder="https://orcid.org/0000-0000-0000-0000">
                        <?php if (isset($errors['orcid_link'])): ?><div class="invalid-feedback"><?php echo $errors['orcid_link']; ?></div><?php endif; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Save Changes</button>
        </form>
        <?php endif; // end else for if($user_data) ?>
    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
