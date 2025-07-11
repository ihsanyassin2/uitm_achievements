<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Ensure session is started
}
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'UiTM Achievements'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link href="<?php echo SITE_URL; ?>functions/style.css" rel="stylesheet">
</head>
<body>

<?php include_once 'navbar.php'; ?>

<div class="container mt-4">
    <?php
    // Display session messages if any
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>
<!-- Main content starts here -->
