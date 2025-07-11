<?php
$page_title = "User Dashboard - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: User or Admin role required
protect_page(['user', 'admin']);

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$pdo = get_pdo_connection();
$stats = [
    'total_submissions' => 0,
    'approved_achievements' => 0,
    'pending_achievements' => 0,
    'rejected_achievements' => 0,
];

if ($pdo) {
    try {
        // Total submissions by user
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_submissions'] = $stmt->fetchColumn();

        // Approved achievements
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$user_id]);
        $stats['approved_achievements'] = $stmt->fetchColumn();

        // Pending achievements
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE user_id = ? AND status = 'pending'");
        $stmt->execute([$user_id]);
        $stats['pending_achievements'] = $stmt->fetchColumn();

        // Rejected/Needs Revision achievements
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE user_id = ? AND (status = 'rejected' OR status = 'needs_revision')");
        $stmt->execute([$user_id]);
        $stats['rejected_achievements'] = $stmt->fetchColumn();

    } catch (PDOException $e) {
        error_log("User Dashboard PDOException: " . $e->getMessage());
        set_flash_message("Error fetching dashboard stats: " . $e->getMessage(), "danger");
    }
}


include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="dashboard-header">
            <h2><i class="fas fa-user-shield"></i> User Dashboard</h2>
            <p class="lead">Welcome back, <?php echo htmlspecialchars($user_name); ?>! Here's an overview of your achievements.</p>
        </div>
        <hr>

        <h4>Quick Stats:</h4>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="stat-number"><?php echo $stats['total_submissions']; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
                    <div class="stat-number"><?php echo $stats['approved_achievements']; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-hourglass-half text-warning"></i></div>
                    <div class="stat-number"><?php echo $stats['pending_achievements']; ?></div>
                    <div class="stat-label">Pending Review</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-times-circle text-danger"></i></div>
                    <div class="stat-number"><?php echo $stats['rejected_achievements']; ?></div>
                    <div class="stat-label">Rejected / Needs Revision</div>
                </div>
            </div>
        </div>

        <hr>

        <h4>Quick Actions:</h4>
        <div class="list-group">
            <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="list-group-item list-group-item-action">
                <i class="fas fa-plus-circle fa-fw mr-2"></i>Submit a New Achievement
            </a>
            <a href="<?php echo SITE_URL; ?>user/my_achievements.php" class="list-group-item list-group-item-action">
                <i class="fas fa-list-alt fa-fw mr-2"></i>View My Submitted Achievements
            </a>
            <a href="<?php echo SITE_URL; ?>user/update_profile.php" class="list-group-item list-group-item-action">
                <i class="fas fa-user-edit fa-fw mr-2"></i>Update My Profile
            </a>
            <a href="<?php echo SITE_URL; ?>user/feedback.php" class="list-group-item list-group-item-action">
                <i class="fas fa-comments fa-fw mr-2"></i>View Feedback from Admin
            </a>
        </div>

        <div class="mt-4">
            <h4>Recent Activity / Notifications (Placeholder)</h4>
            <div class="card">
                <div class="card-body">
                    <p class="card-text">No new notifications at this time.</p>
                    <!-- Later: List recent feedback, status changes, etc. -->
                </div>
            </div>
        </div>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
