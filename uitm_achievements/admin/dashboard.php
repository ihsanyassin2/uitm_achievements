<?php
// admin/dashboard.php
$page_title = "Admin Dashboard - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found from admin/dashboard.php.");
}

require_login('admin'); // Only admins can access this page

// The header will include the correct sidebar (admin_sidebar.php)
include_once SITE_ROOT . 'includes/header.php';

// Fetch data for dashboard display (examples)
$admin_name = $_SESSION['user_full_name'];

// Database connection
$db = db_connect();
if (!$db) {
    echo "<div class='alert alert-danger'>Database connection error. Dashboard data cannot be loaded.</div>";
    // include_once SITE_ROOT . 'includes/footer.php'; // Still include footer
    // exit; // Or allow page to render with error message
}

// Example data fetching for admin dashboard
$total_users = 0;
$total_achievements = 0;
$pending_achievements = 0;
$approved_achievements = 0;

if ($db) {
    // Total users
    $result_users = $db->query("SELECT COUNT(*) AS total FROM users");
    if ($result_users) $total_users = $result_users->fetch_assoc()['total'] ?? 0;
    else error_log("DB error (total_users): " . $db->error);

    // Total achievements
    $result_total_ach = $db->query("SELECT COUNT(*) AS total FROM achievements");
    if ($result_total_ach) $total_achievements = $result_total_ach->fetch_assoc()['total'] ?? 0;
    else error_log("DB error (total_achievements): " . $db->error);

    // Pending achievements
    $result_pending_ach = $db->query("SELECT COUNT(*) AS total FROM achievements WHERE status = 'pending'");
    if ($result_pending_ach) $pending_achievements = $result_pending_ach->fetch_assoc()['total'] ?? 0;
     else error_log("DB error (pending_achievements): " . $db->error);

    // Approved achievements
    $result_approved_ach = $db->query("SELECT COUNT(*) AS total FROM achievements WHERE status = 'approved'");
    if($result_approved_ach) $approved_achievements = $result_approved_ach->fetch_assoc()['total'] ?? 0;
    else error_log("DB error (approved_achievements): " . $db->error);

    // Fetch recent pending achievements (e.g., last 5)
    $recent_pending = [];
    $stmt_recent = $db->prepare(
        "SELECT a.id, a.title, u.full_name as submitter_name, a.created_at
         FROM achievements a
         JOIN users u ON a.user_id = u.id
         WHERE a.status = 'pending'
         ORDER BY a.created_at DESC LIMIT 5"
    );
    if ($stmt_recent) {
        $stmt_recent->execute();
        $result_recent = $stmt_recent->get_result();
        while ($row = $result_recent->fetch_assoc()) {
            $recent_pending[] = $row;
        }
        $stmt_recent->close();
    } else {
        error_log("DB error (recent_pending): " . $db->error);
    }


    if ($db) $db->close();
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Administrator Dashboard</h1>
</div>

<?php display_message('success_message'); ?>
<?php display_message('error_message'); ?>

<p>Welcome, <strong><?php echo htmlspecialchars($admin_name); ?></strong>. This is the central control panel for managing the UiTM Achievements portal.</p>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $total_users; ?></h5>
                        <p class="card-text small">Total Users</p>
                    </div>
                    <i class="fas fa-users fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>admin/manage_users.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">Manage Users</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
     <div class="col-md-3 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                 <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $total_achievements; ?></h5>
                        <p class="card-text small">Total Achievements</p>
                    </div>
                    <i class="fas fa-trophy fa-2x"></i>
                </div>
            </div>
             <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View All</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $pending_achievements; ?></h5>
                        <p class="card-text small">Pending Review</p>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?status=pending" class="card-footer text-white clearfix small z-1">
                <span class="float-left">Review Submissions</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0"><?php echo $approved_achievements; ?></h5>
                        <p class="card-text small">Approved Achievements</p>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?status=approved" class="card-footer text-white clearfix small z-1">
                <span class="float-left">View Approved</span>
                <span class="float-right"><i class="fas fa-angle-right"></i></span>
            </a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clipboard-list"></i> Recent Pending Submissions
            </div>
            <?php if (empty($recent_pending)): ?>
                <div class="card-body">
                    <p class="text-muted">No submissions currently pending review.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Submitted By</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_pending as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($item['title'], 0, 50)) . (strlen($item['title']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo htmlspecialchars($item['submitter_name']); ?></td>
                                <td><?php echo date("d M Y", strtotime($item['created_at'])); ?></td>
                                <td>
                                    <a href="<?php echo SITE_URL; ?>admin/review_achievement.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-primary">Review</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($recent_pending) >= 5): ?>
                <div class="card-footer text-center">
                    <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?status=pending">View All Pending Submissions</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cogs"></i> Quick Admin Actions
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="list-group-item list-group-item-action"><i class="fas fa-trophy fa-fw mr-2"></i>Manage All Achievements</a>
                <a href="<?php echo SITE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action"><i class="fas fa-users-cog fa-fw mr-2"></i>Manage Users</a>
                <a href="<?php echo SITE_URL; ?>admin/manage_categories.php" class="list-group-item list-group-item-action"><i class="fas fa-sitemap fa-fw mr-2"></i>Manage Categories</a>
                <a href="<?php echo SITE_URL; ?>admin/system_settings.php" class="list-group-item list-group-item-action"><i class="fas fa-cog fa-fw mr-2"></i>System Settings</a>
                <a href="<?php echo SITE_URL; ?>admin/admin_messages.php" class="list-group-item list-group-item-action"><i class="fas fa-envelope-open-text fa-fw mr-2"></i>View Feedback Messages</a>
                 <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="list-group-item list-group-item-action"><i class="fas fa-user-circle fa-fw mr-2"></i>Access User Dashboard View</a>

            </div>
        </div>
    </div>
</div>


<?php
// This will include the closing tags for main, row, container-fluid, and body, plus scripts
include_once SITE_ROOT . 'includes/footer.php';
?>
