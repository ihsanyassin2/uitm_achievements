<?php
// Ensure SITE_URL is defined (it should be by config.php or header.php)
if (!defined('SITE_URL')) {
    // Attempt to find config.php by going up one level from includes
    $configPath = __DIR__ . '/../config/config.php';
    if (file_exists($configPath)) {
        require_once $configPath;
    } else {
        // Fallback if config is not found
        // This is less critical for footer but good for consistency
        // define('SITE_URL', '/'); // Basic fallback
    }
}

$is_user_area = strpos($_SERVER['REQUEST_URI'], '/user/') !== false;
$is_admin_area = strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Close main content wrapper if a sidebar was included
if (($is_user_area && $user_role == 'user') || ($is_admin_area && $user_role == 'admin') || ($is_user_area && $user_role == 'admin')) {
    echo '</main>'; // Close the <main> tag opened in header.php
}
?>
    </div> <!-- /.row -->
</div> <!-- /.container-fluid -->

<footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
        <span class="text-muted">&copy; <?php echo date("Y"); ?> UiTM Achievements Portal. All rights reserved.</span>
        <p><small>Universiti Teknologi MARA (UiTM)</small></p>
    </div>
</footer>

<!-- Bootstrap JS and Popper.js (Popper is required for Bootstrap dropdowns, tooltips, popovers) -->
<!-- jQuery is already included in header.php -->
<!-- <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script> -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Ekko Lightbox JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.min.js"></script>
<!-- Custom JavaScript -->
<script src="<?php echo defined('SITE_URL') ? SITE_URL : ''; ?>functions/functions.js"></script>

<!-- Initialize Bootstrap components and Ekko Lightbox -->
<script>
$(function () {
  // Initialize Bootstrap tooltips
  $('[data-toggle="tooltip"]').tooltip();

  // Initialize Bootstrap popovers
  $('[data-toggle="popover"]').popover();

  // Initialize Ekko Lightbox
  // Delegate to document because lightbox triggers might be added dynamically (e.g., via AJAX)
  $(document).on('click', '[data-toggle="lightbox"]', function(event) {
    event.preventDefault();
    $(this).ekkoLightbox({
        alwaysShowClose: true
        // Add any other global lightbox options here
    });
  });
});
</script>

</body>
</html>
