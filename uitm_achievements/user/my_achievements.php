<?php
// user/my_achievements.php
$page_title = "My Submissions - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login(); // User must be logged in
$user_id = $_SESSION['user_id'];

// TODO: Implement fetching and displaying user's submitted achievements
// TODO: Allow filtering by status (pending, approved, rejected, needs_revision)
// TODO: Allow user to view details, edit (if status allows), or delete (if status allows) their submissions

include_once SITE_ROOT . 'includes/header.php';

$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : 'all';
$allowed_statuses = ['all', 'pending', 'approved', 'rejected', 'needs_revision'];
if (!in_array($filter_status, $allowed_statuses)) {
    $filter_status = 'all'; // Default to 'all' if invalid status is provided
}

$achievements = [];
$db = db_connect();
if ($db) {
    $sql = "SELECT id, title, category_id, status, created_at, approved_at, admin_feedback FROM achievements WHERE user_id = ?";
    $params = [$user_id];
    $types = "i";

    if ($filter_status !== 'all') {
        if ($filter_status === 'rejected') { // Special case for combined status
            $sql .= " AND (status = 'rejected' OR status = 'needs_revision')";
        } else {
            $sql .= " AND status = ?";
            $params[] = $filter_status;
            $types .= "s";
        }
    }
    $sql .= " ORDER BY created_at DESC";

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error fetching achievements: " . $db->error;
    }
    $db->close();
} else {
    $_SESSION['error_message'] = "Database connection failed.";
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">My Submissions</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus-circle"></i> Submit New Achievement
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<div class="mb-3">
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="form-inline">
        <label for="statusFilter" class="mr-2">Filter by status:</label>
        <select name="status" id="statusFilter" class="form-control mr-2" onchange="this.form.submit()">
            <option value="all" <?php if ($filter_status == 'all') echo 'selected'; ?>>All Submissions</option>
            <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
            <option value="approved" <?php if ($filter_status == 'approved') echo 'selected'; ?>>Approved</option>
            <option value="needs_revision" <?php if ($filter_status == 'needs_revision') echo 'selected'; ?>>Needs Revision</option>
            <option value="rejected" <?php if ($filter_status == 'rejected') echo 'selected'; ?>>Rejected</option>
        </select>
        <noscript><button type="submit" class="btn btn-secondary">Filter</button></noscript>
    </form>
</div>


<?php if (empty($achievements)): ?>
    <div class="alert alert-info">
        You have not submitted any achievements yet<?php if($filter_status !== 'all') echo " with the status '".htmlspecialchars($filter_status)."'"; ?>.
        <a href="<?php echo SITE_URL; ?>user/submit_achievement.php">Why not submit one now?</a>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Submitted On</th>
                    <th>Last Updated/Approved</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achievements as $index => $achievement): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($achievement['title']); ?></td>
                    <td>
                        <?php
                        $status_badge = 'secondary';
                        if ($achievement['status'] == 'approved') $status_badge = 'success';
                        elseif ($achievement['status'] == 'pending') $status_badge = 'warning';
                        elseif ($achievement['status'] == 'rejected') $status_badge = 'danger';
                        elseif ($achievement['status'] == 'needs_revision') $status_badge = 'info';
                        echo "<span class='badge badge-".htmlspecialchars($status_badge)."'>" . htmlspecialchars(ucfirst(str_replace('_', ' ', $achievement['status']))) . "</span>";
                        ?>
                    </td>
                    <td><?php echo date("d M Y, H:i", strtotime($achievement['created_at'])); ?></td>
                    <td>
                        <?php
                        if ($achievement['status'] == 'approved' && !empty($achievement['approved_at'])) {
                            echo "Approved: " . date("d M Y, H:i", strtotime($achievement['approved_at']));
                        } elseif (!empty($achievement['updated_at']) && $achievement['updated_at'] != $achievement['created_at']) {
                             echo "Updated: " . date("d M Y, H:i", strtotime($achievement['updated_at']));
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>user/view_submission_detail.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-info" title="View Details"><i class="fas fa-eye"></i></a>
                        <?php if ($achievement['status'] == 'pending' || $achievement['status'] == 'needs_revision'): ?>
                            <a href="<?php echo SITE_URL; ?>user/edit_achievement.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>
                        <?php endif; ?>
                        <?php if ($achievement['status'] == 'pending'): // Only allow delete for pending submissions ?>
                            <a href="<?php echo SITE_URL; ?>user/delete_achievement.php?id=<?php echo $achievement['id']; ?>&csrf_token=<?php echo generate_csrf_token(); ?>"
                               class="btn btn-sm btn-danger confirm-delete"
                               data-message="Are you sure you want to delete this submission? This action cannot be undone." title="Delete">
                               <i class="fas fa-trash-alt"></i>
                            </a>
                        <?php endif; ?>
                         <?php if (!empty($achievement['admin_feedback']) && ($achievement['status'] == 'needs_revision' || $achievement['status'] == 'rejected' )): ?>
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="popover" title="Admin Feedback" data-content="<?php echo htmlspecialchars($achievement['admin_feedback']); ?>">
                                <i class="fas fa-comment-alt"></i> Feedback
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<p class="mt-3">
    <strong>Status Key:</strong>
    <span class="badge badge-warning">Pending</span> - Waiting for admin review.
    <span class="badge badge-success">Approved</span> - Published on the public website.
    <span class="badge badge-info">Needs Revision</span> - Admin has provided feedback, please edit and resubmit.
    <span class="badge badge-danger">Rejected</span> - Submission was not approved.
</p>

<?php
// TODO: Add view_submission_detail.php, edit_achievement.php, delete_achievement.php
include_once SITE_ROOT . 'includes/footer.php';
?>
