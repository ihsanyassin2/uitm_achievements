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
        die("Critical error: config.php not found and SITE_URL is not defined for user_sidebar.php.");
    }
}

// This sidebar is accessible by 'user' and 'admin' roles.
// Admin has access to user features as per README.
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['user', 'admin'])) {
    // Optional: Redirect or show error if accessed directly without proper role
    // For now, we'll just not render anything if the role is not appropriate,
    // assuming page-level checks handle redirection.
    // return;
}
?>
<div class="list-group">
    <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="list-group-item list-group-item-action <?php echo isActivePage('user/dashboard.php'); ?>">
        <i class="fas fa-tachometer-alt fa-fw mr-2"></i>Dashboard
    </a>
    <a href="<?php echo SITE_URL; ?>user/submit_achievement.php" class="list-group-item list-group-item-action <?php echo isActivePage('user/submit_achievement.php'); ?>">
        <i class="fas fa-plus-circle fa-fw mr-2"></i>Submit New Achievement
    </a>
    <a href="<?php echo SITE_URL; ?>user/my_achievements.php" class="list-group-item list-group-item-action <?php echo isActivePage('user/my_achievements.php'); ?>">
        <i class="fas fa-list-alt fa-fw mr-2"></i>My Achievements
    </a>
    <a href="<?php echo SITE_URL; ?>user/update_profile.php" class="list-group-item list-group-item-action <?php echo isActivePage('user/update_profile.php'); ?>">
        <i class="fas fa-user-edit fa-fw mr-2"></i>Update Profile
    </a>
    <a href="<?php echo SITE_URL; ?>user/feedback.php" class="list-group-item list-group-item-action <?php echo isActivePage('user/feedback.php'); ?>">
        <i class="fas fa-comments fa-fw mr-2"></i>Feedback / Messages
        <?php
        // Example: Get unread message count - this function would need to be implemented in functions.php
        // $unread_count = get_unread_feedback_count($_SESSION['user_id']);
        // if ($unread_count > 0) {
        //     echo '<span class="badge badge-danger badge-pill ml-auto">' . $unread_count . '</span>';
        // }
        ?>
    </a>
    <a href="<?php echo SITE_URL; ?>public/index.php" class="list-group-item list-group-item-action">
        <i class="fas fa-globe fa-fw mr-2"></i>Public Site
    </a>
    <a href="<?php echo SITE_URL; ?>authentication/logout.php" class="list-group-item list-group-item-action">
        <i class="fas fa-sign-out-alt fa-fw mr-2"></i>Logout
    </a>
</div>

<?php
// Helper function to determine active page for sidebar styling (can be moved to functions.php if used elsewhere)
if (!function_exists('isActivePage')) {
    function isActivePage($page_name) {
        // Compare the current script name with the page name
        // $_SERVER['PHP_SELF'] might include subdirectories, so parse it
        $current_page = basename($_SERVER['PHP_SELF']);
        $page_path_parts = explode('/', $page_name);
        $page_file_name = end($page_path_parts);

        // Check if the current page matches the page name, and if the directory context is also correct
        // This is a simplified check. For more complex routing, a proper router or more detailed checks are needed.
        if (strpos($_SERVER['PHP_SELF'], $page_name) !== false) {
             return 'active';
        }
        return '';
    }
}
?>
