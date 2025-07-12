<?php
// Ensure session is started (it should be by config.php or header.php)
if (session_status() == PHP_SESSION_NONE) {
    session_name(defined('SESSION_NAME') ? SESSION_NAME : 'uitmAcheivementsSession');
    session_start();
}

// Ensure SITE_URL is defined (it should be by config.php or header.php)
if (!defined('SITE_URL')) {
    // Attempt to find config.php by going up one level from includes
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        // Fallback if config is not found - this is a critical error.
        die("Critical error: Configuration file not found for navbar.");
    }
}

$is_logged_in = isset($_SESSION['user_id']);
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
$user_name = isset($_SESSION['user_full_name']) ? $_SESSION['user_full_name'] : 'Guest';

?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <a class="navbar-brand" href="<?php echo SITE_URL; ?>public/index.php">
        <img src="<?php echo SITE_URL; ?>assets/uitm_logo.png" width="30" height="30" class="d-inline-block align-top" alt="UiTM Logo">
        UiTM Achievements
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>public/index.php"><i class="fas fa-home"></i> Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?php echo SITE_URL; ?>public/view_achievements.php"><i class="fas fa-trophy"></i> Achievements</a>
            </li>

            <?php if ($is_logged_in): ?>
                <?php if ($user_role === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
                    </li>
                     <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>user/dashboard.php"><i class="fas fa-user-cog"></i> User Dashboard (Admin View)</a>
                    </li>
                <?php elseif ($user_role === 'user'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>user/dashboard.php"><i class="fas fa-tachometer-alt"></i> My Dashboard</a>
                    </li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav">
            <?php if ($is_logged_in): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarUserDropdown">
                        <?php if ($user_role === 'admin'): ?>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>admin/dashboard.php"><i class="fas fa-user-shield"></i> Admin Panel</a>
                            <a class="dropdown-item" href="<?php echo SITE_URL; ?>user/update_profile.php"><i class="fas fa-user-edit"></i> Edit My Profile (as User)</a>
                        <?php elseif ($user_role === 'user'): ?>
                             <a class="dropdown-item" href="<?php echo SITE_URL; ?>user/update_profile.php"><i class="fas fa-user-edit"></i> My Profile</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo SITE_URL; ?>authentication/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>authentication/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>authentication/register.php"><i class="fas fa-user-plus"></i> Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
