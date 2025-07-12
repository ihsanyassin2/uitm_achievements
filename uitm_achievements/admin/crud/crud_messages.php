<?php
// admin/crud/crud_messages.php
$page_title = "CRUD - Messages Table - Admin Panel";
$config_path = __DIR__ . '/../../config/config.php'; // Adjusted path
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement Read and Delete for the 'messages' table.
// - List messages with search and filter (by sender, receiver, achievement_id, date range)
// - View message content
// - Delete message (with confirmation) - use with extreme caution.
// Creating/Updating messages should typically happen through the application's messaging interface, not direct CRUD.
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">View Messages (CRUD)</h1>
    <!-- No "Add New" button typically for direct message table manipulation -->
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This section allows for viewing and potentially deleting records from the <code>messages</code> table. Direct editing or creation of messages here is generally not recommended and should be done via the <a href="<?php echo SITE_URL; ?>admin/admin_messages.php">standard messaging interface</a>.</p>

<!-- Placeholder for Messages Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Achievement ID</th>
                <th>Sender ID</th>
                <th>Receiver ID</th>
                <th>Message (Snippet)</th>
                <th>Is Read</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // TODO: Fetch and display messages from the database
            // Example row:
            /*
            <tr>
                <td>1</td>
                <td>10</td>
                <td>2 (Admin)</td>
                <td>5 (User John Doe)</td>
                <td>Your submission needs more details...</td>
                <td><span class="badge badge-success">Yes</span></td>
                <td>2023-03-01 10:00:00</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info disabled" title="View Full Message"><i class="fas fa-envelope-open-text"></i></a>
                    <a href="#" class="btn btn-sm btn-danger disabled confirm-delete" title="Delete"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
            */
            ?>
            <tr>
                <td colspan="8" class="text-center">Message data will be displayed here. CRUD functionality is pending implementation.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- TODO: Add search and filter options -->
<!-- TODO: Modal for viewing full message content -->

<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
