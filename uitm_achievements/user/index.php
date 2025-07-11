<?php
// This file can act as a router or redirect for the /user/ section.
// For simplicity, it will redirect to the user dashboard.

require_once dirname(__FILE__) . '/../config/config.php'; // For SITE_URL
require_once dirname(__FILE__) . '/../functions/functions.php'; // For redirect and protect_page

// Protect this area - only logged-in users (user or admin) should access
// Admin can access user panel as per requirements
protect_page(['user', 'admin']);

// Redirect to the user dashboard
redirect(SITE_URL . 'user/dashboard.php');
exit;

// Alternatively, this could load a default view if dashboard.php was more of a controller
// $page_title = "User Area";
// include_once dirname(__FILE__) . '/../includes/header.php';
// echo "<h1>Welcome to the User Area</h1>";
// echo "<p>Please navigate using the sidebar.</p>";
// include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; // Example if content was here
// include_once dirname(__FILE__) . '/../includes/footer.php';

?>
