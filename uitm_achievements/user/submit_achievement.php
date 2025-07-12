<?php
// user/submit_achievement.php
$page_title = "Submit New Achievement - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login(); // User must be logged in

$user_id = $_SESSION['user_id'];
$errors = [];
$success_message = '';

// Form field values to retain on error
$title = '';
$category_id_selected = '';
$description = '';
$achievement_date = '';
$level_selected = '';
$pic_name = '';
$pic_email = '';
$pic_phone = '';
$youtube_link = '';


$db = db_connect();
if (!$db) {
    $_SESSION['error_message'] = "Database connection failed. Cannot submit achievement.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_achievement']) && $db) {
    if (!validate_csrf_token()) {
        // Error message is set in session by the function
    } else {
        // Sanitize and retrieve form data
        $title = sanitize_input($_POST['title']);
        $category_id_selected = intval($_POST['category_id']);
        $description = sanitize_input($_POST['description']); // Consider allowing safe HTML later
        $achievement_date = !empty($_POST['achievement_date']) ? sanitize_input($_POST['achievement_date']) : null;
        $level_selected = sanitize_input($_POST['level']);
        $pic_name = sanitize_input($_POST['pic_name']);
        $pic_email = sanitize_input($_POST['pic_email']);
        $pic_phone = sanitize_input($_POST['pic_phone']);
        $youtube_link = sanitize_input($_POST['youtube_link']);

        // Validation
        if (empty($title)) $errors[] = "Achievement Title is required.";
        if (empty($category_id_selected)) $errors[] = "Category is required.";
        if (empty($description)) $errors[] = "Description is required.";
        if (empty($level_selected)) $errors[] = "Level is required.";
        if (empty($pic_name)) $errors[] = "PIC Full Name is required.";
        if (empty($pic_email)) {
            $errors[] = "PIC Email is required.";
        } elseif (!filter_var($pic_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid PIC Email format.";
        }
        if (!empty($youtube_link) && !filter_var($youtube_link, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid YouTube Video Link URL.";
        } elseif (!empty($youtube_link) && !preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.?be)\/.+$/', $youtube_link)) {
            $errors[] = "The YouTube link does not appear to be a valid YouTube URL.";
        }


        // Image Upload Handling
        $uploaded_image_paths = [];
        $max_files = 5;
        $max_file_size = 2 * 1024 * 1024; // 2MB per file
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $upload_dir_base = 'uploads/images/achievements/';
        $upload_dir_full = SITE_ROOT . $upload_dir_base;

        if (!is_dir($upload_dir_full)) {
            if (!mkdir($upload_dir_full, 0775, true)) {
                $errors[] = "Failed to create upload directory. Please contact admin.";
            }
        }

        if (isset($_FILES['images']) && is_dir($upload_dir_full)) {
            $file_count = count($_FILES['images']['name']);
            if ($file_count > $max_files) {
                $errors[] = "You can upload a maximum of {$max_files} images.";
            } else {
                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES['images']['error'][$i] == UPLOAD_ERR_OK) {
                        $file_name = $_FILES['images']['name'][$i];
                        $file_size = $_FILES['images']['size'][$i];
                        $file_tmp = $_FILES['images']['tmp_name'][$i];
                        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                        if (!in_array($file_extension, $allowed_extensions)) {
                            $errors[] = "Invalid file type for '{$file_name}'. Allowed: " . implode(', ', $allowed_extensions);
                            continue;
                        }
                        if ($file_size > $max_file_size) {
                            $errors[] = "File '{$file_name}' exceeds size limit of 2MB.";
                            continue;
                        }

                        $new_filename = "ach_" . $user_id . "_" . uniqid() . "." . $file_extension;
                        $target_file = $upload_dir_full . $new_filename;

                        if (move_uploaded_file($file_tmp, $target_file)) {
                            $uploaded_image_paths[] = $upload_dir_base . $new_filename; // Store relative path
                        } else {
                            $errors[] = "Failed to upload image '{$file_name}'.";
                        }
                    } elseif ($_FILES['images']['error'][$i] != UPLOAD_ERR_NO_FILE) {
                        $errors[] = "Error uploading file '{$_FILES['images']['name'][$i]}'. Error code: {$_FILES['images']['error'][$i]}.";
                    }
                }
            }
        }

        if (empty($errors)) {
            $db->begin_transaction();
            try {
                // Insert into achievements table
                $stmt_ach = $db->prepare("INSERT INTO achievements (user_id, category_id, title, description, achievement_date, level, status, pic_name, pic_email, pic_phone) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
                if (!$stmt_ach) throw new Exception("DB Prepare Error (achievements): " . $db->error);

                $stmt_ach->bind_param("iisssssss", $user_id, $category_id_selected, $title, $description, $achievement_date, $level_selected, $pic_name, $pic_email, $pic_phone);
                if (!$stmt_ach->execute()) throw new Exception("DB Execute Error (achievements): " . $stmt_ach->error);

                $new_achievement_id = $db->insert_id;
                $stmt_ach->close();

                // Insert media (images)
                if (!empty($uploaded_image_paths)) {
                    $stmt_media_img = $db->prepare("INSERT INTO achievement_media (achievement_id, media_type, file_path_or_url, caption) VALUES (?, 'image', ?, NULL)");
                    if (!$stmt_media_img) throw new Exception("DB Prepare Error (image_media): " . $db->error);
                    foreach ($uploaded_image_paths as $path) {
                        $stmt_media_img->bind_param("is", $new_achievement_id, $path);
                        if (!$stmt_media_img->execute()) throw new Exception("DB Execute Error (image_media for {$path}): " . $stmt_media_img->error);
                    }
                    $stmt_media_img->close();
                }

                // Insert media (YouTube link)
                if (!empty($youtube_link)) {
                    $stmt_media_yt = $db->prepare("INSERT INTO achievement_media (achievement_id, media_type, file_path_or_url, caption) VALUES (?, 'youtube_video', ?, NULL)");
                     if (!$stmt_media_yt) throw new Exception("DB Prepare Error (youtube_media): " . $db->error);
                    $stmt_media_yt->bind_param("is", $new_achievement_id, $youtube_link);
                    if (!$stmt_media_yt->execute()) throw new Exception("DB Execute Error (youtube_media): " . $stmt_media_yt->error);
                    $stmt_media_yt->close();
                }

                $db->commit();
                $_SESSION['success_message'] = "Achievement submitted successfully! It will be reviewed by an administrator.";
                redirect(SITE_URL . 'user/my_achievements.php');

            } catch (Exception $e) {
                $db->rollback();
                // Delete uploaded files if transaction failed
                foreach ($uploaded_image_paths as $path_to_delete) {
                    if (file_exists(SITE_ROOT . $path_to_delete)) {
                        unlink(SITE_ROOT . $path_to_delete);
                    }
                }
                $errors[] = "Submission failed: " . $e->getMessage();
                error_log("Achievement submission failed for user {$user_id}: " . $e->getMessage());
            }
        }
    }
}

$csrf_token = generate_csrf_token();
include_once SITE_ROOT . 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Submit New Achievement</h1>
</div>

<p>Please fill out the form below to submit your achievement. Ensure all information is accurate and complete.</p>

<?php
if (!empty($errors)) {
    echo '<div class="alert alert-danger" role="alert">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . '<br>';
    }
    echo '</div>';
}
// Session messages are handled by header.php, but if header is included after logic, display here too.
display_message('success_message');
display_message('error_message');
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-trophy"></i> Achievement Details</div>
        <div class="card-body">
            <div class="form-group">
                <label for="title">Achievement Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>

            <div class="form-group">
                <label for="category_id">Category <span class="text-danger">*</span></label>
                <select class="form-control" id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    <?php
                    if ($db) {
                       $cat_result = $db->query("SELECT id, name FROM achievement_categories ORDER BY name ASC");
                       if ($cat_result) {
                           while($cat_row = $cat_result->fetch_assoc()) {
                               $selected_attr = ($category_id_selected == $cat_row['id']) ? 'selected' : '';
                               echo "<option value='".htmlspecialchars($cat_row['id'])."' ".$selected_attr.">".htmlspecialchars($cat_row['name'])."</option>";
                           }
                       }
                    } else {
                        echo "<option value='' disabled>Could not load categories</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required data-max-length="5000"><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="achievement_date">Date of Achievement</label>
                    <input type="date" class="form-control" id="achievement_date" name="achievement_date" value="<?php echo htmlspecialchars($achievement_date); ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="level">Level <span class="text-danger">*</span></label>
                    <select class="form-control" id="level" name="level" required>
                        <option value="">-- Select Level --</option>
                        <option value="International" <?php if($level_selected == 'International') echo 'selected'; ?>>International</option>
                        <option value="National" <?php if($level_selected == 'National') echo 'selected'; ?>>National</option>
                        <option value="Institutional" <?php if($level_selected == 'Institutional') echo 'selected'; ?>>Institutional</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-user-tie"></i> Person In Charge (PIC) Details</div>
        <div class="card-body">
             <div class="form-group">
                <label for="pic_name">PIC Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="pic_name" name="pic_name" value="<?php echo htmlspecialchars($pic_name); ?>" required>
            </div>
             <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="pic_email">PIC Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="pic_email" name="pic_email" value="<?php echo htmlspecialchars($pic_email); ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="pic_phone">PIC Phone Number</label>
                    <input type="tel" class="form-control" id="pic_phone" name="pic_phone" value="<?php echo htmlspecialchars($pic_phone); ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><i class="fas fa-photo-video"></i> Media Uploads</div>
        <div class="card-body">
            <div class="form-group">
                <label for="images">Upload Images (Max 5 files, 2MB each)</label>
                <input type="file" class="form-control-file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/gif">
                <small class="form-text text-muted">You can select multiple images. Allowed types: JPG, PNG, GIF. Existing images will not be overwritten by new uploads on edit, new ones will be added.</small>
                <!-- TODO: Display existing images if editing -->
                 <div id="imagePreviewContainer" class="mt-2 d-flex flex-wrap"></div>
            </div>

            <div class="form-group">
                <label for="youtube_link">YouTube Video Link</label>
                <input type="url" class="form-control" id="youtube_link" name="youtube_link" value="<?php echo htmlspecialchars($youtube_link); ?>" placeholder="https://www.youtube.com/watch?v=your_video_id">
                <small class="form-text text-muted">Paste the full YouTube video URL here. Only one YouTube link currently supported per submission through this form.</small>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <button type="submit" name="submit_achievement" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Achievement</button>
        <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>

</form>

<p class="text-muted small">
    <span class="text-danger">*</span> Required fields. <br>
    After submission, your achievement will be reviewed by an administrator before it is published.
    You can track the status of your submissions in the "My Submissions" section.
</p>


<?php
if ($db) $db->close();
include_once SITE_ROOT . 'includes/footer.php';
?>
<script>
// Image preview for multiple files
document.getElementById('images').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('imagePreviewContainer');
    previewContainer.innerHTML = ''; // Clear existing previews
    const files = event.target.files;
    if (files.length > 5) {
        alert('You can only upload a maximum of 5 images.');
        // Optionally clear the file input or truncate the files list
        this.value = ''; // Clears the file input
        return;
    }
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgWrapper = document.createElement('div');
                imgWrapper.style.marginRight = '10px';
                imgWrapper.style.marginBottom = '10px';
                imgWrapper.style.border = '1px solid #ddd';
                imgWrapper.style.padding = '5px';
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;
                img.style.maxWidth = '100px';
                img.style.maxHeight = '100px';
                img.style.objectFit = 'cover';
                imgWrapper.appendChild(img);
                previewContainer.appendChild(imgWrapper);
            }
            reader.readAsDataURL(file);
        }
    }
});
</script>
