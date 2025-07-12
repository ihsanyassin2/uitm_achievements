<?php
// admin/manage_users.php
$page_title = "Manage Users - Admin Panel";
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
$filter_search_user = isset($_GET['search_user']) ? sanitize_input($_GET['search_user']) : '';
$filter_role = isset($_GET['role']) ? sanitize_input($_GET['role']) : '';

$users_list = [];
$sql_conditions = [];
$sql_params = [];
$sql_types = "";

if ($db) {
    $base_sql = "SELECT id, uitm_id, full_name, email, role, created_at FROM users";

    if (!empty($filter_search_user)) {
        $sql_conditions[] = "(full_name LIKE ? OR email LIKE ? OR uitm_id LIKE ?)";
        $search_param = "%" . $filter_search_user . "%";
        $sql_params[] = $search_param;
        $sql_params[] = $search_param;
        $sql_params[] = $search_param;
        $sql_types .= "sss";
    }
    if (!empty($filter_role)) {
        $sql_conditions[] = "role = ?";
        $sql_params[] = $filter_role;
        $sql_types .= "s";
    }

    $sql_query = $base_sql;
    if (!empty($sql_conditions)) {
        $sql_query .= " WHERE " . implode(" AND ", $sql_conditions);
    }
    $sql_query .= " ORDER BY created_at DESC";
    // TODO: Add pagination

    $stmt = $db->prepare($sql_query);
    if ($stmt) {
        if (!empty($sql_params)) {
            $stmt->bind_param($sql_types, ...$sql_params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $users_list[] = $row;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing SQL statement for users: " . $db->error;
        error_log("SQL Error in manage_users.php: " . $db->error . " | Query: " . $sql_query);
    }
}

// Handle role change action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_role_user_id']) && $db) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) { // Explicitly pass token from POST
        // Error set in session
    } else {
        $user_to_change_id = intval($_POST['change_role_user_id']);
        $new_role = sanitize_input($_POST['new_role']);

        if ($user_to_change_id === $_SESSION['user_id'] && $new_role !== 'admin') {
            $_SESSION['error_message'] = "You cannot demote your own admin account.";
        } elseif (!in_array($new_role, ['user', 'admin'])) {
            $_SESSION['error_message'] = "Invalid role specified.";
        } else {
            $stmt_change_role = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt_change_role) {
                $stmt_change_role->bind_param("si", $new_role, $user_to_change_id);
                if ($stmt_change_role->execute()) {
                    $_SESSION['success_message'] = "User role updated successfully.";
                    // Refresh user list
                    $users_list = []; // Clear and re-fetch (or update in memory)
                    $stmt_exec = $db->prepare($sql_query); // Re-prepare and execute original query
                     if ($stmt_exec) {
                        if (!empty($sql_params)) $stmt_exec->bind_param($sql_types, ...$sql_params);
                        $stmt_exec->execute();
                        $result_refresh = $stmt_exec->get_result();
                        while ($row_refresh = $result_refresh->fetch_assoc()) $users_list[] = $row_refresh;
                        $stmt_exec->close();
                    }
                } else {
                    $_SESSION['error_message'] = "Failed to update user role: " . $stmt_change_role->error;
                }
                $stmt_change_role->close();
            } else {
                 $_SESSION['error_message'] = "DB Prepare Error (change role): " . $db->error;
            }
        }
    }
}
$csrf_token = generate_csrf_token(); // Regenerate for forms
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
         <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-database"></i> Advanced CRUD
        </a>
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php?action=add" class="btn btn-sm btn-outline-primary ml-2">
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This page allows administrators to manage user accounts, including their roles and status.</p>

<!-- Filters -->
<div class="card mb-3">
    <div class="card-header"><i class="fas fa-filter"></i> Filters</div>
    <div class="card-body">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="form-row align-items-end">
            <div class="form-group col-md-4">
                <label for="filterNameEmail">Search Name/Email/UiTM ID</label>
                <input type="text" class="form-control form-control-sm" id="filterNameEmail" name="search_user" placeholder="Enter keyword..." value="<?php echo htmlspecialchars($filter_search_user); ?>">
            </div>
            <div class="form-group col-md-3">
                <label for="filterRole">Role</label>
                <select id="filterRole" name="role" class="form-control form-control-sm">
                    <option value="">All Roles</option>
                    <option value="user" <?php if ($filter_role == 'user') echo 'selected'; ?>>User</option>
                    <option value="admin" <?php if ($filter_role == 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                <button type="submit" class="btn btn-primary btn-sm btn-block">Apply Filters</button>
                <a href="<?php echo SITE_URL; ?>admin/manage_users.php" class="btn btn-secondary btn-sm btn-block mt-1">Clear Filters</a>
            </div>
        </form>
    </div>
</div>

<!-- User Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>UiTM ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Registered On</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users_list)): ?>
                <tr>
                    <td colspan="7" class="text-center">No users found matching your criteria.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users_list as $user): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['uitm_id']); ?></td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>public/staff_profile.php?uitm_id=<?php echo htmlspecialchars($user['uitm_id']); ?>" target="_blank" title="View Public Profile">
                            <?php echo htmlspecialchars($user['full_name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="form-inline d-inline-block m-0 p-0">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="change_role_user_id" value="<?php echo $user['id']; ?>">
                            <select name="new_role" class="form-control form-control-sm d-inline-block" style="width: auto; min-width:80px;" onchange="if(confirm('Change role for <?php echo htmlspecialchars(addslashes($user['full_name'])); ?> to ' + this.value + '?')) { this.form.submit(); } else { this.value='<?php echo $user['role']; ?>';}">
                                <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>User</option>
                                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                            </select>
                            <noscript><button type="submit" class="btn btn-sm btn-link p-0 m-0">Save Role</button></noscript>
                        </form>
                    </td>
                    <td><?php echo date("d M Y", strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" title="Edit User (CRUD)"><i class="fas fa-user-edit"></i></a>
                        <?php if ($user['id'] !== $_SESSION['user_id']): // Admin cannot delete themselves ?>
                        <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php?action=delete&id=<?php echo $user['id']; ?>&csrf_token=<?php echo $csrf_token; ?>"
                           class="btn btn-sm btn-danger confirm-delete"
                           data-message="Are you sure you want to delete user <?php echo htmlspecialchars(addslashes($user['full_name'])); ?> (ID: <?php echo $user['id']; ?>)? This action cannot be undone."
                           title="Delete User (CRUD)"><i class="fas fa-user-times"></i>
                        </a>
                        <?php else: ?>
                            <button class="btn btn-sm btn-danger" disabled title="Cannot delete your own account"><i class="fas fa-user-times"></i></button>
                        <?php endif; ?>
                         <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php?user_id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info" title="View User's Achievements"><i class="fas fa-trophy"></i></a>
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
