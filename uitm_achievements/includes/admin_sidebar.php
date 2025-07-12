<?php
// Ensure SITE_URL is defined (it should be by config.php or header.php)
if (!defined('SITE_URL')) {
    // Attempt to find config.php by going up one level from includes
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        die("Critical error: Configuration file not found for admin_sidebar.");
    }
}

// Check if user is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // This sidebar should not be accessible to non-admins.
    // Redirect to login or a 'not authorized' page.
    // For now, just don't render anything or die.
    // header('Location: ' . SITE_URL . 'authentication/login.php');
    // exit;
    // Or simply return/die to prevent output:
    echo "<p>Access Denied. Adminstrator access required.</p>"; // Or handle more gracefully
    return;
}

// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Core Management</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php' && $current_dir == 'admin') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    Admin Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_achievements.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_achievements.php">
                    <i class="fas fa-trophy fa-fw"></i>
                    Manage Achievements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_users.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_users.php">
                    <i class="fas fa-users-cog fa-fw"></i>
                    Manage Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'manage_categories.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/manage_categories.php">
                     <i class="fas fa-sitemap fa-fw"></i>
                    Manage Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'system_settings.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/system_settings.php">
                    <i class="fas fa-cogs fa-fw"></i>
                    System Settings
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'admin_messages.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/admin_messages.php">
                    <i class="fas fa-envelope-open-text fa-fw"></i>
                    Feedback Messages
                     <?php
                    // Placeholder for unread messages count for admin - to be implemented later
                    // if (isset($_SESSION['user_id']) && function_exists('get_admin_unread_message_count')) {
                    //    $unread_count = get_admin_unread_message_count();
                    //    if ($unread_count > 0) {
                    //        echo '<span class="badge badge-danger ml-1">' . $unread_count . '</span>';
                    //    }
                    // }
                    ?>
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>CRUD Operations</span>
            <a class="d-flex align-items-center text-muted" href="#" aria-label="More CRUD operations">
                <!-- <i class="fas fa-plus-circle"></i> -->
            </a>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'crud_users.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/crud/crud_users.php">
                    <i class="fas fa-user-edit fa-fw"></i> Users Table
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'crud_achievements.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/crud/crud_achievements.php">
                    <i class="fas fa-edit fa-fw"></i> Achievements Table
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'crud_achievement_categories.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/crud/crud_achievement_categories.php">
                    <i class="fas fa-tags fa-fw"></i> Categories Table
                </a>
            </li>
             <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'crud_messages.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/crud/crud_messages.php">
                    <i class="fas fa-comments fa-fw"></i> Messages Table
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'crud_settings.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>admin/crud/crud_settings.php">
                    <i class="fas fa-cog fa-fw"></i> Settings Table
                </a>
            </li>
            <!-- Add more links to other CRUD tables as needed -->
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>User Area Access</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>user/dashboard.php">
                    <i class="fas fa-user-circle fa-fw"></i>
                    View User Dashboard
                </a>
            </li>
        </ul>

    </div>
</nav>
