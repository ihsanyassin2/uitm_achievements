<?php
// admin/manage_categories.php
$page_title = "Manage Categories - Admin Panel";
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

$categories = [];
if ($db) {
    $result = $db->query("SELECT ac.id, ac.name, ac.description, COUNT(a.id) as achievements_count
                          FROM achievement_categories ac
                          LEFT JOIN achievements a ON ac.id = a.category_id
                          GROUP BY ac.id, ac.name, ac.description
                          ORDER BY ac.name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    } else {
        $_SESSION['error_message'] = "Error fetching categories: " . $db->error;
    }
}

// Handle Add/Edit/Delete actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && $db) {
    if (!validate_csrf_token()) {
        // Error set in session
    } else {
        $action = $_POST['action'] ?? '';
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $category_name = isset($_POST['category_name']) ? sanitize_input($_POST['category_name']) : '';
        $category_description = isset($_POST['category_description']) ? sanitize_input($_POST['category_description']) : '';

        if ($action === 'add' || $action === 'edit') {
            if (empty($category_name)) {
                $_SESSION['error_message'] = "Category name cannot be empty.";
            } else {
                // Check for duplicate category name (optional, but good practice)
                $stmt_check_name = $db->prepare("SELECT id FROM achievement_categories WHERE name = ? AND id != ?");
                if ($stmt_check_name) {
                    $stmt_check_name->bind_param("si", $category_name, $category_id);
                    $stmt_check_name->execute();
                    if ($stmt_check_name->get_result()->num_rows > 0) {
                        $_SESSION['error_message'] = "Another category with this name already exists.";
                    }
                    $stmt_check_name->close();
                }

                if (!isset($_SESSION['error_message'])) { // Proceed if no duplicate error
                    if ($action === 'add') {
                        $stmt = $db->prepare("INSERT INTO achievement_categories (name, description) VALUES (?, ?)");
                        if ($stmt) {
                            $stmt->bind_param("ss", $category_name, $category_description);
                            if ($stmt->execute()) $_SESSION['success_message'] = "Category added successfully.";
                            else $_SESSION['error_message'] = "Failed to add category: " . $stmt->error;
                            $stmt->close();
                        } else $_SESSION['error_message'] = "DB Prepare Error (add category): " . $db->error;
                    } elseif ($action === 'edit' && $category_id > 0) {
                        $stmt = $db->prepare("UPDATE achievement_categories SET name = ?, description = ? WHERE id = ?");
                        if ($stmt) {
                            $stmt->bind_param("ssi", $category_name, $category_description, $category_id);
                            if ($stmt->execute()) $_SESSION['success_message'] = "Category updated successfully.";
                            else $_SESSION['error_message'] = "Failed to update category: " . $stmt->error;
                            $stmt->close();
                        } else $_SESSION['error_message'] = "DB Prepare Error (edit category): " . $db->error;
                    }
                    // Refresh categories list after action by redirecting or re-fetching
                    if (!isset($_SESSION['error_message'])) redirect(SITE_URL . 'admin/manage_categories.php');
                }
            }
        } elseif ($action === 'delete' && $category_id > 0) {
            // Check if category is in use before deleting
            $stmt_check_usage = $db->prepare("SELECT COUNT(*) as count FROM achievements WHERE category_id = ?");
            if ($stmt_check_usage) {
                $stmt_check_usage->bind_param("i", $category_id);
                $stmt_check_usage->execute();
                $usage_count = $stmt_check_usage->get_result()->fetch_assoc()['count'];
                $stmt_check_usage->close();

                if ($usage_count > 0) {
                    $_SESSION['error_message'] = "Cannot delete category: It is currently assigned to {$usage_count} achievement(s). Please reassign them first.";
                } else {
                    $stmt = $db->prepare("DELETE FROM achievement_categories WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("i", $category_id);
                        if ($stmt->execute()) $_SESSION['success_message'] = "Category deleted successfully.";
                        else $_SESSION['error_message'] = "Failed to delete category: " . $stmt->error;
                        $stmt->close();
                    } else $_SESSION['error_message'] = "DB Prepare Error (delete category): " . $db->error;
                }
                 if (!isset($_SESSION['error_message']) || $usage_count == 0) redirect(SITE_URL . 'admin/manage_categories.php');
            } else {
                 $_SESSION['error_message'] = "DB Prepare Error (check usage): " . $db->error;
            }
        }
    }
}
$csrf_token = generate_csrf_token();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Achievement Categories</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievement_categories.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-database"></i> Advanced CRUD
        </a>
        <button type="button" class="btn btn-sm btn-outline-primary ml-2" data-toggle="modal" data-target="#categoryModal" data-action="add">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>Manage the categories used to classify achievements. These categories will be available for users during submission and for filtering on the public website.</p>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-light">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Achievements Count</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr><td colspan="5" class="text-center">No categories found.</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?php echo $category['id']; ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td><?php echo htmlspecialchars(substr($category['description'] ?? '', 0, 100)) . (strlen($category['description'] ?? '') > 100 ? '...' : ''); ?></td>
                    <td><?php echo $category['achievements_count']; ?></td>
                    <td>
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#categoryModal"
                                data-action="edit"
                                data-id="<?php echo $category['id']; ?>"
                                data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                data-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <?php if ($category['achievements_count'] == 0): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i> Delete</button>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-sm btn-danger" disabled title="Cannot delete: Category is in use."><i class="fas fa-trash-alt"></i> Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="categoryForm" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel">Category</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" id="modalAction" value="add">
                <input type="hidden" name="category_id" id="modalCategoryId" value="0">
                <div class="form-group">
                    <label for="modalCategoryName">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="modalCategoryName" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="modalCategoryDescription">Description</label>
                    <textarea class="form-control" id="modalCategoryDescription" name="category_description" rows="3"></textarea>
                </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="categoryFormSubmitButton">Save Category</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    $('#categoryModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var action = button.data('action');
        var modal = $(this);

        modal.find('#modalAction').val(action);
        if (action === 'edit') {
            modal.find('.modal-title').text('Edit Category');
            modal.find('#categoryFormSubmitButton').text('Save Changes');
            modal.find('#modalCategoryId').val(button.data('id'));
            modal.find('#modalCategoryName').val(button.data('name'));
            modal.find('#modalCategoryDescription').val(button.data('description'));
        } else { // add
            modal.find('.modal-title').text('Add New Category');
            modal.find('#categoryFormSubmitButton').text('Add Category');
            modal.find('#modalCategoryId').val('0'); // Important for add action
            modal.find('#categoryForm')[0].reset(); // Reset form fields
        }
    });
});
</script>

<?php
if ($db) $db->close();
include_once SITE_ROOT . 'includes/footer.php';
?>
