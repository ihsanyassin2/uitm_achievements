<?php
// admin/manage_achievements.php
$page_title = "Manage Achievements - Admin Panel";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

$db = db_connect();
if (!$db) {
    $_SESSION['error_message'] = "Database connection failed.";
}

// Filters
$filter_search_title = isset($_GET['search_title']) ? sanitize_input($_GET['search_title']) : '';
$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$filter_category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : '';
$filter_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : '';

$achievements = [];
$sql_conditions = [];
$sql_params = [];
$sql_types = "";

if ($db) {
    $base_sql = "SELECT a.id, a.title, u.full_name as submitter_name, u.uitm_id as submitter_uitm_id, c.name as category_name, a.status, a.created_at, a.approved_at
                 FROM achievements a
                 JOIN users u ON a.user_id = u.id
                 JOIN achievement_categories c ON a.category_id = c.id";

    if (!empty($filter_search_title)) {
        $sql_conditions[] = "a.title LIKE ?";
        $sql_params[] = "%" . $filter_search_title . "%";
        $sql_types .= "s";
    }
    if (!empty($filter_status)) {
        if ($filter_status === 'rejected_needs_revision') { // Combined filter
            $sql_conditions[] = "(a.status = 'rejected' OR a.status = 'needs_revision')";
        } else {
            $sql_conditions[] = "a.status = ?";
            $sql_params[] = $filter_status;
            $sql_types .= "s";
        }
    }
    if (!empty($filter_category_id)) {
        $sql_conditions[] = "a.category_id = ?";
        $sql_params[] = $filter_category_id;
        $sql_types .= "i";
    }
    if (!empty($filter_user_id)) {
        $sql_conditions[] = "a.user_id = ?";
        $sql_params[] = $filter_user_id;
        $sql_types .= "i";
    }

    $sql_query = $base_sql;
    if (!empty($sql_conditions)) {
        $sql_query .= " WHERE " . implode(" AND ", $sql_conditions);
    }
    $sql_query .= " ORDER BY a.created_at DESC";
    // TODO: Add pagination later

    $stmt = $db->prepare($sql_query);
    if ($stmt) {
        if (!empty($sql_params)) {
            $stmt->bind_param($sql_types, ...$sql_params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $achievements[] = $row;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing SQL statement: " . $db->error;
        error_log("SQL Error in manage_achievements.php: " . $db->error . " | Query: " . $sql_query);
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Achievements</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievements.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-database"></i> Advanced CRUD
        </a>
        <!-- Admin can also submit achievements, perhaps through user interface or a dedicated admin submission form -->
         <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="btn btn-sm btn-outline-primary ml-2">
            <i class="fas fa-plus-circle"></i> Submit New (as User)
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This page provides an overview of all submitted achievements and tools for their review and management. Use the filters to narrow down the list.</p>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header"><i class="fas fa-filter"></i> Filters</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="form-row align-items-end">
            <div class="form-group col-md-3">
                <label for="filterTitle">Search Title</label>
                <input type="text" class="form-control form-control-sm" id="filterTitle" name="search_title" placeholder="Enter keyword..." value="<?php echo htmlspecialchars($filter_search_title); ?>">
            </div>
            <div class="form-group col-md-2">
                <label for="filterStatus">Status</label>
                <select id="filterStatus" name="status" class="form-control form-control-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($filter_status == 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="approved" <?php if ($filter_status == 'approved') echo 'selected'; ?>>Approved</option>
                    <option value="needs_revision" <?php if ($filter_status == 'needs_revision') echo 'selected'; ?>>Needs Revision</option>
                    <option value="rejected" <?php if ($filter_status == 'rejected') echo 'selected'; ?>>Rejected</option>
                    <option value="rejected_needs_revision" <?php if ($filter_status == 'rejected_needs_revision') echo 'selected'; ?>>Rejected or Needs Revision</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="filterCategory">Category</label>
                <select id="filterCategory" name="category_id" class="form-control form-control-sm">
                    <option value="">All Categories</option>
                    <?php
                    if ($db) {
                       $cat_res = $db->query("SELECT id, name FROM achievement_categories ORDER BY name ASC");
                       if ($cat_res) {
                           while($cat_row = $cat_res->fetch_assoc()) {
                               $selected = ($filter_category_id == $cat_row['id']) ? 'selected' : '';
                               echo "<option value='".htmlspecialchars($cat_row['id'])."' ".$selected.">".htmlspecialchars($cat_row['name'])."</option>";
                           }
                       }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-2">
                <label for="filterUser">Submitter (User ID)</label>
                <input type="number" class="form-control form-control-sm" id="filterUser" name="user_id" placeholder="Enter User ID" value="<?php echo htmlspecialchars($filter_user_id); ?>">
            </div>
            <div class="form-group col-md-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Filters</button>
                 <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="btn btn-secondary btn-sm btn-block mt-1">Clear Filters</a>
            </div>
        </form>
    </div>
</div>


<!-- Achievement Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Submitter</th>
                <th>Category</th>
                <th>Status</th>
                <th>Submitted On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($achievements)): ?>
                <tr>
                    <td colspan="7" class="text-center">No achievements found matching your criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($achievements as $achievement): ?>
                <tr>
                    <td><?php echo $achievement['id']; ?></td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>admin/review_achievement.php?id=<?php echo $achievement['id']; ?>" title="Review: <?php echo htmlspecialchars($achievement['title']); ?>">
                            <?php echo htmlspecialchars(substr($achievement['title'], 0, 50)) . (strlen($achievement['title']) > 50 ? '...' : ''); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>public/staff_profile.php?uitm_id=<?php echo htmlspecialchars($achievement['submitter_uitm_id']); ?>" target="_blank" title="View Profile">
                            <?php echo htmlspecialchars($achievement['submitter_name']); ?>
                        </a>
                        (ID: <?php echo $achievement['user_id']; // This is the actual user_id from achievements table ?>)
                    </td>
                    <td><?php echo htmlspecialchars($achievement['category_name']); ?></td>
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
                        <a href="<?php echo SITE_URL; ?>admin/review_achievement.php?id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-primary" title="Review/Manage"><i class="fas fa-tasks"></i> Review</a>
                        <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievements.php?action=edit&id=<?php echo $achievement['id']; ?>" class="btn btn-sm btn-secondary" title="Direct Edit (CRUD)"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- TODO: Add pagination -->

<?php
if ($db) $db->close();
include_once SITE_ROOT . 'includes/footer.php';
?>
