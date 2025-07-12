<?php
// admin/crud/crud_achievements.php
$page_title = "CRUD - Achievements Table - Admin Panel";
$config_path = __DIR__ . '/../../config/config.php'; // Adjusted path
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement full CRUD for the 'achievements' table
// - List achievements with search and filter (by user, category, status, date range)
// - Add new achievement (admin might need to add on behalf of someone)
// - Edit existing achievement (all fields, including status, feedback)
// - Delete achievement (with confirmation)
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Achievements (CRUD)</h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-success disabled"> <!-- TODO: Link to add achievement form -->
            <i class="fas fa-plus-circle"></i> Add New Achievement
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This section allows for direct Create, Read, Update, and Delete operations on the <code>achievements</code> table. Changes here directly impact the data. The <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php">standard achievement management page</a> offers a more workflow-oriented approach for review and approval.</p>

<!-- Placeholder for Achievements Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>User ID (Submitter)</th>
                <th>Category ID</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // TODO: Fetch and display achievements from the database
            // Example row:
            /*
            <tr>
                <td>1</td>
                <td>Groundbreaking Research in AI</td>
                <td>5 (User John Doe)</td>
                <td>2 (Research)</td>
                <td><span class="badge badge-success">Approved</span></td>
                <td>2023-02-20</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info disabled" title="View"><i class="fas fa-eye"></i></a>
                    <a href="#" class="btn btn-sm btn-warning disabled" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="#" class="btn btn-sm btn-danger disabled confirm-delete" title="Delete"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            */
            ?>
            <tr>
                <td colspan="7" class="text-center">Achievement data will be displayed here. CRUD functionality is pending implementation.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- TODO: Add forms for Create/Update operations -->
<!-- TODO: Add search and filter options -->


<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
