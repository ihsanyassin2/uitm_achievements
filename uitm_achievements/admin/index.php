<?php
// This file can act as a router or redirect for the /admin/ section.
// For simplicity, it will redirect to the admin dashboard.

require_once dirname(__FILE__) . '/../config/config.php'; // For SITE_URL
require_once dirname(__FILE__) . '/../functions/functions.php'; // For redirect and protect_page

// Protect this area - only logged-in 'admin' users should access
protect_page('admin'); // Only 'admin' role

// Redirect to the admin dashboard
redirect(SITE_URL . 'admin/dashboard.php');
exit;

// Alternatively, this could load a default view if dashboard.php was more of a controller
// $page_title = "Admin Area";
// include_once dirname(__FILE__) . '/../includes/header.php';
// echo "<h1>Welcome to the Admin Area</h1>";
// echo "<p>Please navigate using the sidebar.</p>";
// include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; // Example if content was here
// include_once dirname(__FILE__) . '/../includes/footer.php';

?>
