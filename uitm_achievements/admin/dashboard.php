<?php
$page_title = "Admin Dashboard - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: Admin role required
protect_page('admin');

$admin_name = $_SESSION['user_name']; // Assuming admin's name is stored in session

$pdo = get_pdo_connection();
$stats = [
    'total_users' => 0,
    'total_achievements' => 0,
    'pending_approval' => 0,
    'total_approved' => 0,
];
$recent_submissions = [];

if ($pdo) {
    try {
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $stats['total_users'] = $stmt->fetchColumn();

        // Total achievements
        $stmt = $pdo->query("SELECT COUNT(*) FROM achievements");
        $stats['total_achievements'] = $stmt->fetchColumn();

        // Achievements pending approval
        $stmt = $pdo->query("SELECT COUNT(*) FROM achievements WHERE status = 'pending'");
        $stats['pending_approval'] = $stmt->fetchColumn();

        // Total approved achievements
        $stmt = $pdo->query("SELECT COUNT(*) FROM achievements WHERE status = 'approved'");
        $stats['total_approved'] = $stmt->fetchColumn();

        // Recent submissions (e.g., last 5 pending)
        $stmt_recent = $pdo->prepare("
            SELECT a.id, a.title, u.name as submitter_name, a.created_at
            FROM achievements a
            JOIN users u ON a.user_id = u.id
            WHERE a.status = 'pending'
            ORDER BY a.created_at DESC
            LIMIT 5
        ");
        $stmt_recent->execute();
        $recent_submissions = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Admin Dashboard PDOException: " . $e->getMessage());
        set_flash_message("Error fetching dashboard stats: " . $e->getMessage(), "danger");
    }
}


include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <div class="dashboard-header">
            <h2><i class="fas fa-cogs"></i> Admin Dashboard</h2>
            <p class="lead">Welcome, <?php echo htmlspecialchars($admin_name); ?>! Manage the UiTM Achievements portal.</p>
        </div>
        <hr>

        <h4>Portal Overview:</h4>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                    <div class="stat-number"><?php echo $stats['total_achievements']; ?></div>
                    <div class="stat-label">Total Achievements</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-hourglass-start text-warning"></i></div>
                    <div class="stat-number"><?php echo $stats['pending_approval']; ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card dashboard-stat-card">
                    <div class="stat-icon"><i class="fas fa-check-double text-success"></i></div>
                    <div class="stat-number"><?php echo $stats['total_approved']; ?></div>
                    <div class="stat-label">Total Approved</div>
                </div>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-7">
                <h4><i class="fas fa-exclamation-triangle text-warning"></i> Recent Submissions Awaiting Review:</h4>
                <?php if (!empty($recent_submissions)): ?>
                    <div class="list-group">
                        <?php foreach ($recent_submissions as $submission): ?>
                            <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?action=view&id=<?php echo $submission['id']; ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo htmlspecialchars($submission['title']); ?></h5>
                                    <small><?php echo date("d M Y, H:i", strtotime($submission['created_at'])); ?></small>
                                </div>
                                <p class="mb-1">Submitted by: <?php echo htmlspecialchars($submission['submitter_name']); ?></p>
                                <small class="text-warning">Status: Pending</small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                     <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?filter_status=pending" class="btn btn-outline-primary btn-sm mt-2">View All Pending Submissions</a>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-thumbs-up"></i> No submissions currently pending review. Great job!
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-5">
                <h4><i class="fas fa-tasks"></i> Quick Admin Actions:</h4>
                <div class="list-group">
                    <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-award fa-fw mr-2"></i>Manage All Achievements
                    </a>
                    <a href="<?php echo SITE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users-cog fa-fw mr-2"></i>Manage Users
                    </a>
                     <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-table fa-fw mr-2"></i>User CRUD
                    </a>
                    <a href="<?php echo SITE_URL; ?>admin/system_settings.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tools fa-fw mr-2"></i>System Settings
                    </a>
                    <a href="<?php echo SITE_URL; ?>admin/feedback_center.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-comments-dollar fa-fw mr-2"></i>Feedback Center
                    </a>
                </div>
            </div>
        </div>


        <div class="mt-4">
            <h4><i class="fas fa-chart-line"></i> System Analytics (Placeholder)</h4>
            <div class="card">
                <div class="card-body">
                    <p class="card-text">Graphs and more detailed analytics about submissions, user activity, and public engagement will be displayed here.</p>
                    <!-- Placeholder for charts, e.g., using Chart.js -->
                </div>
            </div>
        </div>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
