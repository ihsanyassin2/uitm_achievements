<?php
// admin/crud/crud_settings.php
$page_title = "CRUD - Settings Table - Admin Panel";
$config_path = __DIR__ . '/../../config/config.php'; // Adjusted path
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement CRUD for the 'settings' table
// - List settings
// - Add new setting (use with caution, as application code might depend on specific setting names)
// - Edit existing setting value
// - Delete setting (use with extreme caution)
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage System Settings (CRUD)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-success disabled"> <!-- TODO: Link to add setting form -->
            <i class="fas fa-plus-circle"></i> Add New Setting
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<div class="alert alert-warning">
    <strong>Caution:</strong> Modifying settings directly in this CRUD interface can have significant effects on the application's behavior.
    It is recommended to manage settings through the <a href="<?php echo SITE_URL; ?>admin/system_settings.php">dedicated System Settings page</a> if available, as it may provide more context and validation.
</div>

<!-- Placeholder for Settings Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Setting Name</th>
                <th>Setting Value</th>
                <th>Description</th>
                <th>Last Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // TODO: Fetch and display settings from the database
            // Example:
            /*
            $db = db_connect();
            if ($db) {
                $result = $db->query("SELECT id, setting_name, setting_value, description, updated_at FROM settings ORDER BY setting_name ASC");
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td><strong>" . htmlspecialchars($row['setting_name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars(substr($row['setting_value'], 0, 100)) . (strlen($row['setting_value']) > 100 ? '...' : '') . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['updated_at']) . "</td>";
                        echo '<td>
                                <a href="#" class="btn btn-sm btn-warning disabled" title="Edit Value"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn btn-sm btn-danger disabled confirm-delete" title="Delete Setting (Caution!)"><i class="fas fa-trash-alt"></i></a>
                              </td>';
                        echo "</tr>";
                    }
                } else {
                     echo '<tr><td colspan="6" class="text-center">No settings found or CRUD functionality pending.</td></tr>';
                }
                $db->close();
            } else {
                echo '<tr><td colspan="6" class="text-center">Database connection error.</td></tr>';
            }
            */
            ?>
            <tr>
                <td colspan="6" class="text-center">Settings data will be displayed here. CRUD functionality is pending implementation.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- TODO: Add forms for Create/Update operations -->

<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
