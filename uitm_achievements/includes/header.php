<?php
if (session_status() == PHP_SESSION_NONE) {
    // Start session if not already started (e.g. by config.php)
    // This is a fallback, config.php should ideally handle session start
    session_name(defined('SESSION_NAME') ? SESSION_NAME : 'uitmAcheivementsSession');
    session_start();
}

// If config.php hasn't been included yet (e.g. direct access to a page not including index.php)
if (!defined('SITE_URL')) {
    // Attempt to find config.php by going up one level from includes
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        // Fallback if config is not found - this is a critical error.
        // You might want to redirect to an error page or die with a message.
        die("Critical error: Configuration file not found.");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'UiTM Achievements'; ?></title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <!-- Ekko Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>functions/style.css">

    <!-- You might want to include jQuery here if Bootstrap JS needs it and it's not bundled -->
    <!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Using full jQuery for AJAX -->


</head>
<body>

<?php
// Include navbar, it will handle its own logic based on user role
// The navbar needs to be aware of the SITE_URL for links
include_once SITE_ROOT . 'includes/navbar.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php
        // Logic to include sidebar based on context (admin, user, or none for public)
        // This will be determined by the page that includes this header.
        // For example, user pages will set a variable to include user_sidebar.php
        // Admin pages will set a variable to include admin_sidebar.php

        $current_script = basename($_SERVER['PHP_SELF']);
        $user_pages = ['dashboard.php', 'update_profile.php', 'submit_achievement.php']; // Add user specific pages
        $admin_pages = ['admin_dashboard.php', 'manage_users.php', 'manage_achievements.php']; // Add admin specific pages

        // Determine if the current script is in the /user/ or /admin/ directory
        $is_user_area = strpos($_SERVER['REQUEST_URI'], '/user/') !== false;
        $is_admin_area = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;

        $user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

        if ($is_user_area && $user_role == 'user') {
            include_once SITE_ROOT . 'includes/user_sidebar.php';
            echo '<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">';
        } elseif ($is_admin_area && $user_role == 'admin') {
            include_once SITE_ROOT . 'includes/admin_sidebar.php';
            echo '<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">';
        } elseif ($is_user_area && $user_role == 'admin') { // Admin accessing user area
            include_once SITE_ROOT . 'includes/user_sidebar.php';
            echo '<main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">';
        }
        // For public pages, no sidebar is included by default, and main content takes full width or as defined in the public page itself.
        // Public pages will not typically include this header in this manner, or will have a different structure.
        // This header is more geared towards logged-in user/admin areas.
        // A separate header might be needed for public facing site or this one adapted.
        // For now, if not user or admin area, assume it's a full-width page or public.
        else if (!$is_user_area && !$is_admin_area) {
             // This is for pages like login, register, or public pages that might use a simplified header
             // No sidebar, content will be handled by the specific page template
        }


        ?>
        <!-- Main content of the page will go here -->
