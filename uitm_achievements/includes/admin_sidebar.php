<?php
// Ensure config is loaded for SITE_URL and session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!defined('SITE_URL')) {
    $configPath = dirname(__FILE__) . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        die("Critical error: config.php not found and SITE_URL is not defined for admin_sidebar.php.");
    }
}

// This sidebar is accessible ONLY by 'admin' role.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Optional: Redirect or show error if accessed directly without proper role
    // For now, we'll just not render anything if the role is not appropriate,
    // assuming page-level checks handle redirection.
    // return;
}

// Helper function to determine active page for sidebar styling (can be moved to functions.php if used elsewhere)
// Re-declared here for standalone use, or ensure it's loaded from functions.php
if (!function_exists('isActivePage')) {
    function isActivePage($page_name) {
        $current_page_full_path = $_SERVER['PHP_SELF'];
        if (strpos($current_page_full_path, $page_name) !== false) {
             return 'active';
        }
        return '';
    }
}
?>
<div class="list-group">
    <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/dashboard.php'); ?>">
        <i class="fas fa-tachometer-alt fa-fw mr-2"></i>Admin Dashboard
    </a>
    <a href="<?php echo SITE_URL; ?>admin/manage_achievements.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/manage_achievements.php'); ?>">
        <i class="fas fa-trophy fa-fw mr-2"></i>Manage Achievements
    </a>
    <a href="<?php echo SITE_URL; ?>admin/manage_users.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/manage_users.php'); ?>">
        <i class="fas fa-users-cog fa-fw mr-2"></i>Manage Users
    </a>
    <a href="<?php echo SITE_URL; ?>admin/system_settings.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/system_settings.php'); ?>">
        <i class="fas fa-tools fa-fw mr-2"></i>System Settings
    </a>
    <a href="<?php echo SITE_URL; ?>admin/feedback_center.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/feedback_center.php'); ?>">
        <i class="fas fa-comments-dollar fa-fw mr-2"></i>Feedback Center
    </a>

    <div class="list-group-item">
        <strong class="text-secondary"><i class="fas fa-database fa-fw mr-2"></i>CRUD Operations</strong>
    </div>
    <a href="<?php echo SITE_URL; ?>admin/crud/crud_users.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_users.php'); ?>">
        <i class="fas fa-user fa-fw ml-3 mr-1"></i>Users Table
    </a>
    <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievements.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_achievements.php'); ?>">
        <i class="fas fa-award fa-fw ml-3 mr-1"></i>Achievements Table
    </a>
    <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievement_media.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_achievement_media.php'); ?>">
        <i class="fas fa-photo-video fa-fw ml-3 mr-1"></i>Media Table
    </a>
     <a href="<?php echo SITE_URL; ?>admin/crud/crud_achievement_likes.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_achievement_likes.php'); ?>">
        <i class="fas fa-thumbs-up fa-fw ml-3 mr-1"></i>Likes Table
    </a>
    <a href="<?php echo SITE_URL; ?>admin/crud/crud_feedback_messages.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_feedback_messages.php'); ?>">
        <i class="fas fa-envelope fa-fw ml-3 mr-1"></i>Feedback Table
    </a>
    <a href="<?php echo SITE_URL; ?>admin/crud/crud_system_settings.php" class="list-group-item list-group-item-action <?php echo isActivePage('admin/crud/crud_system_settings.php'); ?>">
        <i class="fas fa-cog fa-fw ml-3 mr-1"></i>Settings Table
    </a>

    <div class="list-group-item mt-2"></div> <!-- Spacer -->

    <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="list-group-item list-group-item-action">
        <i class="fas fa-user-shield fa-fw mr-2"></i>Access User Panel
    </a>
    <a href="<?php echo SITE_URL; ?>public/index.php" class="list-group-item list-group-item-action">
        <i class="fas fa-globe fa-fw mr-2"></i>View Public Site
    </a>
    <a href="<?php echo SITE_URL; ?>authentication/logout.php" class="list-group-item list-group-item-action">
        <i class="fas fa-sign-out-alt fa-fw mr-2"></i>Logout
    </a>
</div>
