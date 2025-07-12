<?php
// user/dashboard.php
$page_title = "User Dashboard - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found from user/dashboard.php.");
}

// Require login. Admins can also access this page to see the user view.
require_login();

// The header will include the correct sidebar (user_sidebar.php)
include_once SITE_ROOT . 'includes/header.php';

// Fetch some data for dashboard display (examples)
$user_id = $_SESSION['user_id'];
$user_full_name = $_SESSION['user_full_name'];

// Database connection
$db = db_connect();
if (!$db) {
    echo "<div class='alert alert-danger'>Database connection error.</div>";
    // include_once SITE_ROOT . 'includes/footer.php'; // Still include footer for layout
    // exit;
}

// Example data fetching (to be expanded)
$total_submissions = 0;
$approved_submissions = 0;
$pending_submissions = 0;
$rejected_submissions = 0;

if ($db) {
    // Total submissions by user
    $stmt_total = $db->prepare("SELECT COUNT(*) AS total FROM achievements WHERE user_id = ?");
    if ($stmt_total) {
        $stmt_total->bind_param("i", $user_id);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result()->fetch_assoc();
        $total_submissions = $result_total['total'] ?? 0;
        $stmt_total->close();
    } else {
        error_log("DB error (total_submissions): " . $db->error);
    }

    // Approved submissions
    $stmt_approved = $db->prepare("SELECT COUNT(*) AS approved FROM achievements WHERE user_id = ? AND status = 'approved'");
     if ($stmt_approved) {
        $stmt_approved->bind_param("i", $user_id);
        $stmt_approved->execute();
        $result_approved = $stmt_approved->get_result()->fetch_assoc();
        $approved_submissions = $result_approved['approved'] ?? 0;
        $stmt_approved->close();
    } else {
        error_log("DB error (approved_submissions): " . $db->error);
    }

    // Pending submissions
    $stmt_pending = $db->prepare("SELECT COUNT(*) AS pending FROM achievements WHERE user_id = ? AND status = 'pending'");
    if ($stmt_pending) {
        $stmt_pending->bind_param("i", $user_id);
        $stmt_pending->execute();
        $result_pending = $stmt_pending->get_result()->fetch_assoc();
        $pending_submissions = $result_pending['pending'] ?? 0;
        $stmt_pending->close();
    } else {
        error_log("DB error (pending_submissions): " . $db->error);
    }

    // Rejected/Needs Revision submissions
    $stmt_rejected = $db->prepare("SELECT COUNT(*) AS rejected FROM achievements WHERE user_id = ? AND (status = 'rejected' OR status = 'needs_revision')");
    if ($stmt_rejected) {
        $stmt_rejected->bind_param("i", $user_id);
        $stmt_rejected->execute();
        $result_rejected = $stmt_rejected->get_result()->fetch_assoc();
        $rejected_submissions = $result_rejected['rejected'] ?? 0;
        $stmt_rejected->close();
    } else {
        error_log("DB error (rejected_submissions): " . $db->error);
    }

    // Recent activities or messages (to be added)

    if ($db) $db->close();
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus-circle"></i> Submit New Achievement
        </a>
    </div>
</div>

<?php display_message('success_message'); ?>
<?php display_message('error_message'); ?>

<p>Welcome, <strong><?php echo htmlspecialchars($user_full_name); ?>!</strong> This is your personal dashboard where you can manage your achievements and profile.</p>
<?php if (has_role('admin')): ?>
    <div class="alert alert-info" role="alert">
      <i class="fas fa-info-circle"></i> You are currently viewing the User Dashboard as an Administrator.
      <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="btn btn-sm btn-outline-secondary ml-2">Switch to Admin Panel</a>
    </div>
<?php endif; ?>


<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $total_submissions; ?></h5>
                        <p class="card-text small">Total Submissions</p>
                    </div>
                    <i class="fas fa-file-alt fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>user/my_achievements.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $approved_submissions; ?></h5>
                        <p class="card-text small">Approved</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
             <a href="<?php echo SITE_URL; ?>user/my_achievements.php?status=approved" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $pending_submissions; ?></h5>
                        <p class="card-text small">Pending Review</p>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>user/my_achievements.php?status=pending" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $rejected_submissions; ?></h5>
                        <p class="card-text small">Rejected / Needs Revision</p>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>user/my_achievements.php?status=rejected" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Details</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-bullhorn"></i> Quick Actions
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="list-group-item list-group-item-action"><i class="fas fa-plus-circle fa-fw mr-2"></i>Submit a New Achievement</a>
                <a href="<?php echo SITE_URL; ?>user/my_achievements.php" class="list-group-item list-group-item-action"><i class="fas fa-list-alt fa-fw mr-2"></i>View My Submissions</a>
                <a href="<?php echo SITE_URL; ?>user/update_profile.php" class="list-group-item list-group-item-action"><i class="fas fa-user-edit fa-fw mr-2"></i>Update My Profile</a>
                <a href="<?php echo SITE_URL; ?>user/messages.php" class="list-group-item list-group-item-action"><i class="fas fa-comments fa-fw mr-2"></i>View Messages from Admin</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle"></i> Information & Guidelines
            </div>
            <div class="card-body">
                <p>Welcome to the UiTM Achievements portal. Here you can showcase your successes and contributions.</p>
                <ul>
                    <li>Ensure all information submitted is accurate and verifiable.</li>
                    <li>Upload high-quality images and provide valid YouTube links for videos.</li>
                    <li>Check your messages regularly for feedback from administrators.</li>
                    <li>Keep your profile information up-to-date.</li>
                </ul>
                <p><a href="<?php echo SITE_URL; ?>public/index.php" target="_blank">View Public Achievements Page <i class="fas fa-external-link-alt"></i></a></p>
            </div>
        </div>
    </div>
</div>

<?php
// This will include the closing tags for main, row, container-fluid, and body, plus scripts
include_once SITE_ROOT . 'includes/footer.php';
?>
