<?php
// admin/crud/crud_users.php
$page_title = "CRUD - Users Table - Admin Panel";
$config_path = __DIR__ . '/../../config/config.php'; // Adjusted path
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement full CRUD (Create, Read, Update, Delete) for the 'users' table
// - List users with search and filter
// - Add new user
// - Edit existing user (including role, status, password reset if needed)
// - Delete user (with confirmation)
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users (CRUD)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-success disabled"> <!-- TODO: Link to add user form -->
            <i class="fas fa-user-plus"></i> Add New User
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This section allows for direct Create, Read, Update, and Delete operations on the <code>users</code> table. Please use with caution.</p>

<!-- Placeholder for User Listing Table -->
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
            <?php
            // TODO: Fetch and display users from the database
            // Example row:
            /*
            <tr>
                <td>1</td>
                <td>2023000001</td>
                <td>John Doe</td>
                <td>john.doe@uitm.edu.my</td>
                <td><span class="badge badge-primary">User</span></td>
                <td>2023-01-15</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info disabled" title="View"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn btn-sm btn-warning disabled" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="#" class="btn btn-sm btn-danger disabled confirm-delete" title="Delete"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            */
            ?>
            <tr>
                <td colspan="7" class="text-center">User data will be displayed here. CRUD functionality is pending implementation.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- TODO: Add forms for Create/Update operations (possibly in modals or separate pages) -->
<!-- TODO: Add search and filter options -->

<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
