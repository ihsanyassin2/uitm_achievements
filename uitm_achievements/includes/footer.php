<!-- Main content ends here -->
</div> <!-- /.container -->

<footer class="footer mt-auto py-3 bg-light text-center">
    <div class="container">
        <span class="text-muted">&copy; <?php echo date("Y"); ?> UiTM Achievements Portal. All rights reserved.</span>
    //    <p><img src="<?php echo SITE_URL; ?>assets/uitm_logo.png" alt="UiTM Logo" style="height: 50px;"></p> // Uncomment when logo is available
    </div>
</footer>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo SITE_URL; ?>functions/functions.js"></script>
<?php
if (isset($GLOBALS['pdo'])) {
    $GLOBALS['pdo'] = null; // Close PDO connection if it was opened in functions.php
}
?>
</body>
</html>
