<?php
// admin/crud/crud_achievement_categories.php
$page_title = "CRUD - Achievement Categories - Admin Panel";
$config_path = __DIR__ . '/../../config/config.php'; // Adjusted path
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement full CRUD for the 'achievement_categories' table
// - List categories
// - Add new category
// - Edit existing category
// - Delete category (with confirmation, consider impact on existing achievements - e.g., disallow if in use or reassign)
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Achievement Categories (CRUD)</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="#" class="btn btn-sm btn-outline-success disabled"> <!-- TODO: Link to add category form -->
            <i class="fas fa-plus"></i> Add New Category
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This section allows for direct management of achievement categories. The <a href="<?php echo SITE_URL; ?>admin/manage_categories.php">standard category management page</a> might offer a simpler interface for common tasks.</p>

<!-- Placeholder for Category Listing Table -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // TODO: Fetch and display categories from the database
            // Example:
            /*
            $db = db_connect();
            if ($db) {
                $result = $db->query("SELECT id, name, description FROM achievement_categories ORDER BY name ASC");
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : '') . "</td>";
                        echo '<td>
                                <a href="#" class="btn btn-sm btn-warning disabled" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="#" class="btn btn-sm btn-danger disabled confirm-delete" title="Delete"><i class="fas fa-trash-alt"></i></a>
                              </td>';
                        echo "</tr>";
                    }
                } else {
                    echo '<tr><td colspan="4" class="text-center">No categories found or CRUD functionality pending.</td></tr>';
                }
                $db->close();
            } else {
                 echo '<tr><td colspan="4" class="text-center">Database connection error.</td></tr>';
            }
            */
            ?>
            <tr>
                <td colspan="4" class="text-center">Category data will be displayed here. CRUD functionality is pending implementation.</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- TODO: Add forms for Create/Update operations -->

<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
