<?php
$page_title = "Manage Users - Admin";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: Admin role required
protect_page('admin');

// Placeholder content
// This page will list all users.
// Admins can view profiles, edit roles (e.g., promote to admin - with caution),
// change email (as per README), or deactivate/delete users.

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-users-cog"></i> Manage Users</h2>
        <p>View, edit, and manage user accounts in the system.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This section will allow administrators to manage all registered users. Functionalities will include searching for users, viewing their profiles, editing their details (including role and email as per requirements), and potentially deactivating or deleting accounts.
        </div>

        <!-- Filters and Search -->
        <div class="card mb-3">
            <div class="card-header">Filter and Search Users</div>
            <div class="card-body">
                <form class="form-row">
                    <div class="col-md-5 form-group">
                        <input type="text" class="form-control" name="search_user" placeholder="Search by name, email...">
                    </div>
                    <div class="col-md-4 form-group">
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group">
                        <button type="submit" class="btn btn-primary btn-block" disabled><i class="fas fa-search"></i> Filter Users</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Example Table Structure -->
        <table class="table table-hover table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered On</th>
                    <th>Last Login (Placeholder)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>Admin User</td>
                    <td>admin@uitm.edu.my</td>
                    <td><span class="badge badge-danger">Admin</span></td>
                    <td>2023-01-01</td>
                    <td>2023-11-05</td>
                    <td class="action-icons">
                        <a href="#" class="text-info" title="View Profile"><i class="fas fa-user-circle"></i></a>
                        <a href="#" class="text-primary" title="Edit User"><i class="fas fa-user-edit"></i></a>
                        <!-- Delete for admin might be restricted or need extra confirmation -->
                    </td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>Normal User One</td>
                    <td>user1@uitm.edu.my</td>
                    <td><span class="badge badge-secondary">User</span></td>
                    <td>2023-05-10</td>
                    <td>2023-11-01</td>
                     <td class="action-icons">
                        <a href="#" class="text-info" title="View Profile"><i class="fas fa-user-circle"></i></a>
                        <a href="#" class="text-primary" title="Edit User"><i class="fas fa-user-edit"></i></a>
                        <a href="#" class="text-warning" title="Deactivate User (Confirm)"><i class="fas fa-user-slash"></i></a>
                    </td>
                </tr>
                <!-- More rows will be added dynamically -->
            </tbody>
        </table>
        <p class="text-muted">No users to display based on current filters, or feature under construction.</p>
        <!-- Pagination would go here -->
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php" class="btn btn-success mt-3"><i class="fas fa-table"></i> Advanced CRUD for Users</a>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
