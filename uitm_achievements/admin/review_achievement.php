<?php
// admin/review_achievement.php
$page_title = "Review Achievement - Admin Panel";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
$admin_id = $_SESSION['user_id'];

$achievement_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$achievement_id) {
    $_SESSION['error_message'] = "No achievement ID provided for review.";
    redirect(SITE_URL . 'admin/manage_achievements.php');
}

$db = db_connect();
if (!$db) {
    $_SESSION['error_message'] = "Database connection error.";
    // Allow page to render and show this message via header
}

$achievement_data = null;
$media_items = [];
$submitter_data = null;

if ($db) {
    // Fetch achievement details along with category name and submitter info
    $stmt_ach = $db->prepare(
        "SELECT a.*, c.name as category_name, u.full_name as submitter_name, u.email as submitter_email, u.uitm_id as submitter_uitm_id
         FROM achievements a
         JOIN achievement_categories c ON a.category_id = c.id
         JOIN users u ON a.user_id = u.id
         WHERE a.id = ?"
    );
    if ($stmt_ach) {
        $stmt_ach->bind_param("i", $achievement_id);
        $stmt_ach->execute();
        $result_ach = $stmt_ach->get_result();
        if ($result_ach->num_rows === 1) {
            $achievement_data = $result_ach->fetch_assoc();
            $page_title = "Review: " . htmlspecialchars($achievement_data['title']);

            // Fetch media for this achievement
            $stmt_media = $db->prepare("SELECT id, media_type, file_path_or_url, caption FROM achievement_media WHERE achievement_id = ? ORDER BY id ASC");
            if ($stmt_media) {
                $stmt_media->bind_param("i", $achievement_id);
                $stmt_media->execute();
                $result_media = $stmt_media->get_result();
                while ($row_media = $result_media->fetch_assoc()) {
                    $media_items[] = $row_media;
                }
                $stmt_media->close();
            } else {
                $_SESSION['error_message'] = "Error fetching media: " . $db->error;
            }
        } else {
            $_SESSION['error_message'] = "Achievement not found with ID: " . $achievement_id;
        }
        $stmt_ach->close();
    } else {
        $_SESSION['error_message'] = "Error fetching achievement details: " . $db->error;
    }
}

// Handle form submission for status change / feedback
if ($_SERVER["REQUEST_METHOD"] == "POST" && $db && $achievement_data) {
    if (!validate_csrf_token()) {
        // Error set in session by function
    } else {
        $action = isset($_POST['action']) ? sanitize_input($_POST['action']) : '';
        $admin_feedback = isset($_POST['admin_feedback']) ? sanitize_input($_POST['admin_feedback']) : '';
        $new_status = $achievement_data['status']; // Default to current status

        $allowed_actions = ['approve', 'reject', 'needs_revision', 'update_feedback'];
        if (!in_array($action, $allowed_actions)) {
            $_SESSION['error_message'] = "Invalid action specified.";
        } else {
            $sql_update = "UPDATE achievements SET admin_feedback = ?";
            $params = [$admin_feedback];
            $types = "s";

            $current_timestamp = date("Y-m-d H:i:s");

            if ($action === 'approve') {
                $new_status = 'approved';
                $sql_update .= ", status = ?, approved_at = ?";
                $params[] = $new_status;
                $params[] = $current_timestamp;
                $types .= "ss";
            } elseif ($action === 'reject') {
                $new_status = 'rejected';
                $sql_update .= ", status = ?";
                $params[] = $new_status;
                $types .= "s";
                 if (empty($admin_feedback)) $_SESSION['warning_message'] = "It's recommended to provide feedback when rejecting.";

            } elseif ($action === 'needs_revision') {
                $new_status = 'needs_revision';
                $sql_update .= ", status = ?";
                $params[] = $new_status;
                $types .= "s";
                if (empty($admin_feedback)) {
                     $_SESSION['error_message'] = "Feedback is required when marking as 'Needs Revision'.";
                     $action = 'invalid'; // Prevent further processing if feedback is missing
                }
            }
            // For 'update_feedback', only feedback is updated, status remains.

            if ($action !== 'invalid') {
                $sql_update .= " WHERE id = ?";
                $params[] = $achievement_id;
                $types .= "i";

                $stmt_update_status = $db->prepare($sql_update);
                if ($stmt_update_status) {
                    $stmt_update_status->bind_param($types, ...$params);
                    if ($stmt_update_status->execute()) {
                        $_SESSION['success_message'] = "Achievement status updated to '" . htmlspecialchars($new_status) . "' and feedback saved.";
                        // Refresh achievement data to show changes
                        $achievement_data['status'] = $new_status;
                        $achievement_data['admin_feedback'] = $admin_feedback;
                        if ($new_status == 'approved') $achievement_data['approved_at'] = $current_timestamp;
                        // TODO: Notify user via email/internal message system
                    } else {
                        $_SESSION['error_message'] = "Failed to update achievement: " . $stmt_update_status->error;
                    }
                    $stmt_update_status->close();
                } else {
                     $_SESSION['error_message'] = "DB Prepare Error (update status): " . $db->error;
                }
            }
        }
    }
}


$csrf_token = generate_csrf_token();
include_once SITE_ROOT . 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Review Achievement</h1>
    <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to All Achievements
    </a>
</div>

<?php
display_message('success_message');
display_message('error_message');
display_message('warning_message');
?>

<?php if ($achievement_data): ?>
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><strong>Title:</strong> <?php echo htmlspecialchars($achievement_data['title']); ?></h5>
                    <span>Current Status:
                        <?php
                        $status_badge = 'secondary';
                        if ($achievement_data['status'] == 'approved') $status_badge = 'success';
                        elseif ($achievement_data['status'] == 'pending') $status_badge = 'warning';
                        elseif ($achievement_data['status'] == 'rejected') $status_badge = 'danger';
                        elseif ($achievement_data['status'] == 'needs_revision') $status_badge = 'info';
                        echo "<span class='badge badge-".htmlspecialchars($status_badge)."'>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $achievement_data['status']))) . "</span>";
                        ?>
                    </span>
                </div>
                <div class="card-body">
                    <p><strong>Category:</strong> <?php echo htmlspecialchars($achievement_data['category_name']); ?></p>
                    <p><strong>Level:</strong> <?php echo htmlspecialchars($achievement_data['level']); ?></p>
                    <p><strong>Date of Achievement:</strong> <?php echo !empty($achievement_data['achievement_date']) ? date("d M Y", strtotime($achievement_data['achievement_date'])) : "N/A"; ?></p>
                    <p><strong>Submitted By:</strong> <a href="<?php echo SITE_URL . 'public/staff_profile.php?uitm_id=' . htmlspecialchars($achievement_data['submitter_uitm_id']); ?>" target="_blank"><?php echo htmlspecialchars($achievement_data['submitter_name']); ?></a> (<?php echo htmlspecialchars($achievement_data['submitter_email']); ?>)</p>
                    <p><strong>Submitted On:</strong> <?php echo date("d M Y, H:i", strtotime($achievement_data['created_at'])); ?></p>
                    <?php if($achievement_data['status'] == 'approved' && !empty($achievement_data['approved_at'])): ?>
                        <p><strong>Approved On:</strong> <?php echo date("d M Y, H:i", strtotime($achievement_data['approved_at'])); ?></p>
                    <?php endif; ?>
                    <hr>
                    <h6>Description:</h6>
                    <div class="bg-light p-3 rounded" style="white-space: pre-wrap;"><?php echo htmlspecialchars($achievement_data['description']); ?></div>
                    <hr>
                    <h6>Person In Charge (PIC):</h6>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($achievement_data['pic_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($achievement_data['pic_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo !empty($achievement_data['pic_phone']) ? htmlspecialchars($achievement_data['pic_phone']) : "N/A"; ?></p>
                </div>
            </div>

            <?php if (!empty($media_items)): ?>
            <div class="card mb-3">
                <div class="card-header"><h5><i class="fas fa-photo-video"></i> Submitted Media</h5></div>
                <div class="card-body">
                    <div class="row">
                    <?php foreach ($media_items as $media): ?>
                        <div class="col-md-<?php echo ($media['media_type'] == 'youtube_video' ? '12' : '6'); ?> mb-3">
                            <?php if ($media['media_type'] == 'image'): ?>
                                <a href="<?php echo SITE_URL . htmlspecialchars($media['file_path_or_url']); ?>" data-toggle="lightbox" data-gallery="achievement-gallery" data-title="<?php echo htmlspecialchars($media['caption'] ?? $achievement_data['title']); ?>">
                                    <img src="<?php echo SITE_URL . htmlspecialchars($media['file_path_or_url']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($media['caption'] ?? 'Achievement Image'); ?>" style="max-height: 200px; object-fit: cover;">
                                </a>
                                <?php if (!empty($media['caption'])): ?><p class="small text-muted mt-1"><?php echo htmlspecialchars($media['caption']); ?></p><?php endif; ?>
                            <?php elseif ($media['media_type'] == 'youtube_video'):
                                $youtube_embed_url = preg_replace(
                                    "/\s*[a-zA-Z\/\/:\.]*youtu(be.com\/watch\?v=|.be\/)([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i",
                                    "https://www.youtube.com/embed/$2",
                                    $media['file_path_or_url']
                                );
                            ?>
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($youtube_embed_url); ?>" allowfullscreen></iframe>
                                </div>
                                <?php if (!empty($media['caption'])): ?><p class="small text-muted mt-1"><?php echo htmlspecialchars($media['caption']); ?></p><?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card sticky-top" style="top: 70px;">
                <div class="card-header"><h5><i class="fas fa-gavel"></i> Admin Actions</h5></div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $achievement_id; ?>" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <div class="form-group">
                            <label for="admin_feedback">Feedback for User (Optional for Approve/Reject, Required for 'Needs Revision')</label>
                            <textarea class="form-control" id="admin_feedback" name="admin_feedback" rows="5" placeholder="Provide constructive feedback or reasons for action..."><?php echo htmlspecialchars($achievement_data['admin_feedback']); ?></textarea>
                        </div>

                        <div class="btn-group d-flex mb-2" role="group" aria-label="Status Actions">
                            <button type="submit" name="action" value="approve" class="btn btn-success w-100 <?php if($achievement_data['status'] == 'approved') echo 'active'; ?>"><i class="fas fa-check-circle"></i> Approve</button>
                            <button type="submit" name="action" value="needs_revision" class="btn btn-info w-100 <?php if($achievement_data['status'] == 'needs_revision') echo 'active'; ?>"><i class="fas fa-edit"></i> Needs Revision</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger w-100 <?php if($achievement_data['status'] == 'rejected') echo 'active'; ?>"><i class="fas fa-times-circle"></i> Reject</button>
                        </div>
                         <button type="submit" name="action" value="update_feedback" class="btn btn-secondary btn-block"><i class="fas fa-save"></i> Save Feedback Only</button>
                    </form>
                    <hr>
                    <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievements.php?action=edit&id=<?php echo $achievement_id; ?>" class="btn btn-outline-warning btn-block">
                        <i class="fas fa-pencil-alt"></i> Edit Full Achievement Details (Advanced CRUD)
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-danger">
        Could not load achievement data. It might have been deleted or an error occurred.
        <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php">Return to achievement list.</a>
    </div>
<?php endif; ?>

<?php
if ($db) $db->close();
// For lightbox functionality (optional, include JS library if used)
// Example: <script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
// $(document).on('click', '[data-toggle="lightbox"]', function(event) { event.preventDefault(); $(this).ekkoLightbox(); });
include_once SITE_ROOT . 'includes/footer.php';
?>
<!-- Ekko Lightbox CSS is now in header.php -->
<!-- Ekko Lightbox JS and initialization are now in footer.php -->
