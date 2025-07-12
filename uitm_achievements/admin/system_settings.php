<?php
// admin/system_settings.php
$page_title = "System Settings - Admin Panel";
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
    $_SESSION['error_message'] = "Database connection error. Cannot load system settings.";
}

$app_settings = [];
if ($db) {
    $result = $db->query("SELECT id, setting_name, setting_value, description FROM settings ORDER BY id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $app_settings[] = $row;
        }
    } else {
        $_SESSION['error_message'] = "Could not fetch settings from database: " . $db->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_settings']) && $db) {
    if (!validate_csrf_token()) {
        // Error message set in session by function
    } else {
        $posted_settings = $_POST['settings'] ?? [];
        $update_errors = 0;

        $stmt_update = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = ?");
        if (!$stmt_update) {
             $_SESSION['error_message'] = "DB Prepare Error (settings update): " . $db->error;
        } else {
            foreach ($posted_settings as $name => $value) {
                $sanitized_name = sanitize_input($name); // Should match existing setting_name
                $sanitized_value = sanitize_input($value); // Basic sanitization, specific settings might need more

                // Check if this setting name actually exists to prevent arbitrary updates if form is manipulated
                $exists = false;
                foreach($app_settings as $s_check) {
                    if ($s_check['setting_name'] === $sanitized_name) {
                        $exists = true;
                        break;
                    }
                }

                if ($exists) {
                    $stmt_update->bind_param("ss", $sanitized_value, $sanitized_name);
                    if (!$stmt_update->execute()) {
                        $update_errors++;
                        error_log("Failed to update setting '{$sanitized_name}': " . $stmt_update->error);
                    }
                } else {
                    error_log("Attempt to update non-existent setting '{$sanitized_name}' was blocked.");
                }
            }
            $stmt_update->close();

            if ($update_errors === 0) {
                $_SESSION['success_message'] = "System settings updated successfully.";
                // Refresh settings displayed on page
                $app_settings = []; // Clear and re-fetch
                $result = $db->query("SELECT id, setting_name, setting_value, description FROM settings ORDER BY id ASC");
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $app_settings[] = $row;
                    }
                }
            } else {
                $_SESSION['error_message'] = "Some settings could not be updated. ({$update_errors} errors occurred)";
            }
        }
    }
}

$csrf_token = generate_csrf_token();
include_once SITE_ROOT . 'includes/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">System Settings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_settings.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-database"></i> Advanced CRUD
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>Configure various system-wide settings for the application. Changes made here can affect the entire site.</p>

<?php if (empty($app_settings) && $db): ?>
    <div class="alert alert-info">No system settings found in the database. You might need to initialize them via the <a href="<?php echo SITE_URL; ?>admin/crud/crud_settings.php">Advanced CRUD page</a> or a setup script.</div>
<?php elseif (!$db): ?>
     <div class="alert alert-danger">Cannot display settings due to a database connection error.</div>
<?php else: ?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <div class="card">
        <div class="card-header">
            Core Settings
        </div>
        <div class="card-body">
            <?php foreach ($app_settings as $setting): ?>
                <div class="form-group row">
                    <label for="setting_<?php echo htmlspecialchars($setting['setting_name']); ?>" class="col-sm-4 col-form-label">
                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $setting['setting_name']))); ?>
                    </label>
                    <div class="col-sm-6">
                        <?php
                        $value = htmlspecialchars($setting['setting_value']);
                        $name = htmlspecialchars($setting['setting_name']);
                        if (strlen($value) > 80 || strpos($value, "\n") !== false || $name === 'site_description') { // Example: use textarea for longer values or specific names
                            echo "<textarea class='form-control form-control-sm' id='setting_{$name}' name='settings[{$name}]' rows='3'>{$value}</textarea>";
                        } else if ($name === 'maintenance_mode') { // Example for a boolean-like setting
                            echo "<select class='form-control form-control-sm' id='setting_{$name}' name='settings[{$name}]'>";
                            echo "<option value='0' ".($value == '0' ? 'selected' : '').">Off</option>";
                            echo "<option value='1' ".($value == '1' ? 'selected' : '').">On</option>";
                            echo "</select>";
                        } else if (strpos($name, 'records_per_page') !== false || strpos($name, '_id') !== false) { // Example for numeric
                             echo "<input type='number' class='form-control form-control-sm' id='setting_{$name}' name='settings[{$name}]' value='{$value}'>";
                        }
                        else {
                            echo "<input type='text' class='form-control form-control-sm' id='setting_{$name}' name='settings[{$name}]' value='{$value}'>";
                        }
                        ?>
                    </div>
                    <div class="col-sm-2">
                        <small class="form-text text-muted" title="<?php echo htmlspecialchars($setting['description']); ?>">
                            <?php echo htmlspecialchars(substr($setting['description'], 0, 50)) . (strlen($setting['description']) > 50 ? '...' : ''); ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="card-footer">
            <button type="submit" name="save_settings" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
        </div>
    </div>
</form>
<?php endif; ?>

<p class="mt-3 text-muted">
    Changes to these settings may require clearing site cache or logging out and back in to take full effect.
    Some settings might be critical for site operation; modify with understanding.
</p>

<?php
if ($db) $db->close();
include_once SITE_ROOT . 'includes/footer.php';
?>
