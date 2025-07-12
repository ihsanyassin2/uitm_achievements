<?php
// Ensure SITE_URL is defined (it should be by config.php or header.php)
if (!defined('SITE_URL')) {
    // Attempt to find config.php by going up one level from includes
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        die("Critical error: Configuration file not found for user_sidebar.");
    }
}

// Determine active page for highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="sidebar-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>user/dashboard.php">
                    <i class="fas fa-tachometer-alt fa-fw"></i>
                    Dashboard <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'submit_achievement.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>user/submit_achievement.php">
                    <i class="fas fa-plus-circle fa-fw"></i>
                    Submit New Achievement
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'my_achievements.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>user/my_achievements.php">
                    <i class="fas fa-list-alt fa-fw"></i>
                    My Submissions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'update_profile.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>user/update_profile.php">
                    <i class="fas fa-user-edit fa-fw"></i>
                    Update Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'messages.php') ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>user/messages.php">
                    <i class="fas fa-comments fa-fw"></i>
                    Messages
                    <?php
                    // Placeholder for unread messages count - to be implemented later
                    // if (isset($_SESSION['user_id']) && function_exists('get_unread_message_count')) {
                    //    $unread_count = get_unread_message_count($_SESSION['user_id']);
                    //    if ($unread_count > 0) {
                    //        echo '<span class="badge badge-danger ml-1">' . $unread_count . '</span>';
                    //    }
                    // }
                    ?>
                </a>
            </li>
        </ul>

        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Admin Access</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>admin/dashboard.php">
                    <i class="fas fa-user-shield fa-fw"></i>
                    Switch to Admin Panel
                </a>
            </li>
        </ul>
        <?php endif; ?>

    </div>
</nav>
