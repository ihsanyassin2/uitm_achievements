<?php
// user/update_profile.php
$page_title = "Update Profile - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found from user/update_profile.php.");
}

require_login(); // User must be logged in

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

$db = db_connect();
if (!$db) {
    // This is a critical error, display message and potentially stop script execution
    $_SESSION['error_message'] = "Database connection failed. Please try again later.";
    // header.php will display this if it's set before inclusion
}

// Fetch current user data
$current_user_data = null;
if ($db) {
    $stmt_fetch = $db->prepare("SELECT uitm_id, email, full_name, biography, phone_number, cv_link, google_scholar_link, linkedin_link, scopus_link, isi_link, orcid_link, profile_picture FROM users WHERE id = ?");
    if ($stmt_fetch) {
        $stmt_fetch->bind_param("i", $user_id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        if ($result->num_rows === 1) {
            $current_user_data = $result->fetch_assoc();
        } else {
            // Should not happen for a logged-in user
            $_SESSION['error_message'] = "Could not retrieve your profile data.";
            // redirect(SITE_URL . 'user/dashboard.php'); // Or handle error more gracefully
        }
        $stmt_fetch->close();
    } else {
         $_SESSION['error_message'] = "Error fetching profile data: " . $db->error;
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && $db) {
    if (!validate_csrf_token()) {
        // Error message handled by the function, typically set in $_SESSION['error_message']
    } else {
        // Sanitize and retrieve form data
        // Email cannot be changed by user directly as per requirements ("email change needs to come from admin")
        // UiTM ID also should ideally not be changed by user.
        $full_name = sanitize_input($_POST['full_name']);
        $biography = sanitize_input($_POST['biography']); // Consider allowing some HTML if using a rich text editor, then use a proper HTML sanitizer
        $phone_number = sanitize_input($_POST['phone_number']);
        $cv_link = sanitize_input($_POST['cv_link']);
        $google_scholar_link = sanitize_input($_POST['google_scholar_link']);
        $linkedin_link = sanitize_input($_POST['linkedin_link']);
        $scopus_link = sanitize_input($_POST['scopus_link']);
        $isi_link = sanitize_input($_POST['isi_link']);
        $orcid_link = sanitize_input($_POST['orcid_link']);

        // Password change (optional)
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate inputs
        if (empty($full_name)) {
            $errors[] = "Full Name is required.";
        }
        // Add more validation for URLs if needed (e.g., filter_var with FILTER_VALIDATE_URL)
        if (!empty($cv_link) && !filter_var($cv_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid CV Link URL.";
        if (!empty($google_scholar_link) && !filter_var($google_scholar_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid Google Scholar Link URL.";
        if (!empty($linkedin_link) && !filter_var($linkedin_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid LinkedIn Link URL.";
        if (!empty($scopus_link) && !filter_var($scopus_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid Scopus Link URL.";
        if (!empty($isi_link) && !filter_var($isi_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid ISI Link URL.";
        if (!empty($orcid_link) && !filter_var($orcid_link, FILTER_VALIDATE_URL)) $errors[] = "Invalid ORCID Link URL.";


        // Handle profile picture upload
        $profile_picture_path = $current_user_data['profile_picture']; // Keep old one if not updated
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = SITE_ROOT . 'uploads/images/profile_pictures/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0775, true); // Create directory if it doesn't exist
            }
            $file_extension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $max_file_size = 2 * 1024 * 1024; // 2MB

            if (!in_array($file_extension, $allowed_extensions)) {
                $errors[] = "Invalid file type for profile picture. Allowed types: " . implode(', ', $allowed_extensions);
            } elseif ($_FILES['profile_picture']['size'] > $max_file_size) {
                $errors[] = "Profile picture file size exceeds the limit of 2MB.";
            } else {
                // Generate a unique filename to prevent overwriting
                $new_filename = "user_" . $user_id . "_" . uniqid() . "." . $file_extension;
                $target_file = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                    // Delete old profile picture if it exists and is different
                    if (!empty($profile_picture_path) && file_exists(SITE_ROOT . $profile_picture_path) && (SITE_ROOT . $profile_picture_path) !== $target_file) {
                        unlink(SITE_ROOT . $profile_picture_path);
                    }
                    $profile_picture_path = 'uploads/images/profile_pictures/' . $new_filename; // Relative path for DB
                } else {
                    $errors[] = "Failed to upload profile picture.";
                }
            }
        }


        // Password update logic
        $password_sql_part = "";
        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                $errors[] = "New password must be at least 8 characters long.";
            }
            if ($new_password !== $confirm_password) {
                $errors[] = "New passwords do not match.";
            }
            if (empty($errors)) { // Only proceed if no other errors exist for password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_sql_part = ", password = '" . $db->real_escape_string($hashed_password) . "'";
            }
        }

        if (empty($errors)) {
            $sql = "UPDATE users SET
                        full_name = ?,
                        biography = ?,
                        phone_number = ?,
                        cv_link = ?,
                        google_scholar_link = ?,
                        linkedin_link = ?,
                        scopus_link = ?,
                        isi_link = ?,
                        orcid_link = ?,
                        profile_picture = ?
                        {$password_sql_part}
                    WHERE id = ?";

            $stmt_update = $db->prepare($sql);
            if ($stmt_update) {
                // Parameters: ssssssssssi (10 strings, 1 integer for ID)
                $stmt_update->bind_param("ssssssssssi",
                    $full_name, $biography, $phone_number,
                    $cv_link, $google_scholar_link, $linkedin_link,
                    $scopus_link, $isi_link, $orcid_link,
                    $profile_picture_path, // new profile picture path
                    $user_id
                );

                if ($stmt_update->execute()) {
                    $_SESSION['success_message'] = "Profile updated successfully!";
                    // Update session variables if necessary (e.g., full_name)
                    $_SESSION['user_full_name'] = $full_name;
                    // Refresh current_user_data to show updated info on the form
                     $current_user_data['full_name'] = $full_name;
                     $current_user_data['biography'] = $biography;
                     $current_user_data['phone_number'] = $phone_number;
                     $current_user_data['cv_link'] = $cv_link;
                     $current_user_data['google_scholar_link'] = $google_scholar_link;
                     $current_user_data['linkedin_link'] = $linkedin_link;
                     $current_user_data['scopus_link'] = $scopus_link;
                     $current_user_data['isi_link'] = $isi_link;
                     $current_user_data['orcid_link'] = $orcid_link;
                     $current_user_data['profile_picture'] = $profile_picture_path;

                    // redirect(SITE_URL . 'user/update_profile.php'); // Redirect to refresh and show success
                } else {
                    $errors[] = "Failed to update profile: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                 $errors[] = "Database query preparation error: " . $db->error;
            }
        }
    }
}


if ($db) {
    // $db->close(); // Close connection at the end of the script or in footer
}

$csrf_token = generate_csrf_token();
include_once SITE_ROOT . 'includes/header.php'; // Header will show session messages
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Update Profile</h1>
</div>

<?php
// Display messages if any (these are session messages, handled by display_message in header or here)
// display_message('success_message'); already handled by header if set before header include
// display_message('error_message');   already handled by header if set before header include

if (!empty($errors)): // Display form-specific errors ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($errors as $error): ?>
            <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if(!empty($_SESSION['success_message'])): display_message('success_message'); endif; ?>
<?php if(!empty($_SESSION['error_message'])): display_message('error_message'); endif; ?>


<?php if (!$current_user_data): ?>
    <div class="alert alert-warning">Could not load profile data. Please try again later or contact support.</div>
<?php else: ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-user-edit"></i> Basic Information</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="uitm_id">UiTM ID</label>
                        <input type="text" class="form-control" id="uitm_id" name="uitm_id_display" value="<?php echo htmlspecialchars($current_user_data['uitm_id']); ?>" readonly>
                        <small class="form-text text-muted">UiTM ID cannot be changed.</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email_display" value="<?php echo htmlspecialchars($current_user_data['email']); ?>" readonly>
                        <small class="form-text text-muted">Email address cannot be changed directly. Please contact an administrator for changes.</small>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($current_user_data['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($current_user_data['phone_number']); ?>" placeholder="e.g., 012-3456789">
                    </div>

                     <div class="form-group">
                        <label for="biography">Biography / About Me</label>
                        <textarea class="form-control" id="biography" name="biography" rows="5" placeholder="Tell us a bit about yourself, your expertise, research interests, etc."><?php echo htmlspecialchars($current_user_data['biography']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-link"></i> Professional Links</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="cv_link">CV Link (URL)</label>
                        <input type="url" class="form-control" id="cv_link" name="cv_link" value="<?php echo htmlspecialchars($current_user_data['cv_link']); ?>" placeholder="https://example.com/your-cv.pdf">
                    </div>
                    <div class="form-group">
                        <label for="google_scholar_link">Google Scholar Link (URL)</label>
                        <input type="url" class="form-control" id="google_scholar_link" name="google_scholar_link" value="<?php echo htmlspecialchars($current_user_data['google_scholar_link']); ?>" placeholder="https://scholar.google.com/citations?user=yourid">
                    </div>
                    <div class="form-group">
                        <label for="linkedin_link">LinkedIn Profile Link (URL)</label>
                        <input type="url" class="form-control" id="linkedin_link" name="linkedin_link" value="<?php echo htmlspecialchars($current_user_data['linkedin_link']); ?>" placeholder="https://linkedin.com/in/yourprofile">
                    </div>
                    <div class="form-group">
                        <label for="scopus_link">Scopus Author ID Link (URL)</label>
                        <input type="url" class="form-control" id="scopus_link" name="scopus_link" value="<?php echo htmlspecialchars($current_user_data['scopus_link']); ?>" placeholder="https://www.scopus.com/authid/detail.uri?authorId=xxxx">
                    </div>
                    <div class="form-group">
                        <label for="isi_link">Web of Science (ISI) ResearcherID Link (URL)</label>
                        <input type="url" class="form-control" id="isi_link" name="isi_link" value="<?php echo htmlspecialchars($current_user_data['isi_link']); ?>" placeholder="https://www.webofscience.com/wos/author/record/xxxx">
                    </div>
                    <div class="form-group">
                        <label for="orcid_link">ORCID Link (URL)</label>
                        <input type="url" class="form-control" id="orcid_link" name="orcid_link" value="<?php echo htmlspecialchars($current_user_data['orcid_link']); ?>" placeholder="https://orcid.org/xxxx-xxxx-xxxx-xxxx">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-image"></i> Profile Picture</div>
                <div class="card-body text-center">
                    <img id="profilePicturePreview"
                         src="<?php echo !empty($current_user_data['profile_picture']) ? SITE_URL . htmlspecialchars($current_user_data['profile_picture']) : SITE_URL . 'assets/default_avatar.png'; ?>"
                         alt="Profile Picture" class="img-thumbnail mb-2" style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    <div class="form-group">
                        <label for="profile_picture">Change Profile Picture</label>
                        <input type="file" class="form-control-file image-preview-input" id="profile_picture" name="profile_picture" data-preview-target="#profilePicturePreview">
                        <small class="form-text text-muted">Max 2MB. Allowed: JPG, PNG, GIF.</small>
                    </div>
                     <?php if (!empty($current_user_data['profile_picture'])): ?>
                        <!-- <a href="?remove_picture=true&csrf_token=<?php //echo $csrf_token; // Implement remove picture logic ?>" class="btn btn-sm btn-outline-danger">Remove Picture</a> -->
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-key"></i> Change Password (Optional)</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Leave blank if no change">
                        <small class="form-text text-muted">Minimum 8 characters.</small>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="form-group mt-3">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>
<?php endif; // end of if($current_user_data) ?>


<?php
if ($db) $db->close();
include_once SITE_ROOT . 'includes/footer.php';
?>
