<?php
$page_title = "System Settings - Admin";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: Admin role required
protect_page('admin');

// Placeholder content
// This page will allow admins to change system-wide settings
// (e.g., site title, admin email, records per page - if stored in DB).

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-tools"></i> System Settings</h2>
        <p>Configure global settings for the UiTM Achievements portal.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This section will allow administrators to modify system-wide configurations, such as the site name, contact emails, number of items per page for listings, and other operational parameters that might be stored in the `system_settings` table.
        </div>

        <!-- Example Settings Form -->
        <form action="#" method="post">
            <div class="card">
                <div class="card-header">General Settings</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="site_title">Site Title</label>
                        <input type="text" class="form-control" id="site_title" name="settings[site_title]" value="UiTM Achievements Portal (from DB)" disabled>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Default Administrator Email</label>
                        <input type="email" class="form-control" id="admin_email" name="settings[admin_email]" value="admin_contact@uitm.edu.my (from DB)" disabled>
                         <small class="form-text text-muted">Email for system notifications or public contact point.</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Content & Display Settings</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="records_per_page">Records Per Page (Public Listings)</label>
                        <input type="number" class="form-control" id="records_per_page" name="settings[records_per_page]" value="10 (from DB)" min="5" max="50" disabled>
                        <small class="form-text text-muted">Number of achievements shown per page on public listing pages.</small>
                    </div>
                     <div class="form-group">
                        <label for="featured_count">Number of Featured Achievements on Homepage</label>
                        <input type="number" class="form-control" id="featured_count" name="settings[featured_count]" value="5 (from DB)" min="3" max="10" disabled>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">Maintenance Mode (Placeholder)</div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="maintenance_mode">Enable Maintenance Mode</label>
                        <select class="form-control" id="maintenance_mode" name="settings[maintenance_mode]" disabled>
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </select>
                        <small class="form-text text-muted">If enabled, public site will show a maintenance message. Admin panel remains accessible.</small>
                    </div>
                     <div class="form-group">
                        <label for="maintenance_message">Maintenance Message</label>
                        <textarea class="form-control" id="maintenance_message" name="settings[maintenance_message]" rows="3" disabled>Site is currently down for scheduled maintenance. Please check back soon.</textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3" disabled><i class="fas fa-save"></i> Save Settings (Form Inactive)</button>
        </form>
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_system_settings.php" class="btn btn-success mt-3"><i class="fas fa-table"></i> Advanced CRUD for Settings</a>


    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
