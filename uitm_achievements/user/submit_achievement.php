<?php
$page_title = "Submit New Achievement - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: User or Admin role required
protect_page(['user', 'admin']);

// Placeholder content
// Form for submitting achievements will go here.
// Fields: Title, Description, Category, Level, Picture uploads, YouTube links.

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-plus-circle"></i> Submit New Achievement</h2>
        <p>Share your success story with the UiTM community and the world.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This form will allow you to input details about your achievement, upload supporting images, and link to videos.
        </div>

        <form action="#" method="post" enctype="multipart/form-data">
            <!-- Title -->
            <div class="form-group">
                <label for="title">Achievement Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category">Category</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="Academic">Academic</option>
                    <option value="Research">Research</option>
                    <option value="Student Development">Student Development</option>
                    <option value="Industrial Linkages">Industrial Linkages</option>
                    <option value="Internationalization">Internationalization</option>
                    <option value="Recognition & Awards">Recognition & Awards</option>
                    <option value="CSR">Corporate Social Responsibility (CSR)</option>
                </select>
            </div>

            <!-- Level -->
            <div class="form-group">
                <label for="level">Level</label>
                <select class="form-control" id="level" name="level" required>
                     <option value="">-- Select Level --</option>
                    <option value="International">International</option>
                    <option value="National">National</option>
                    <option value="Institutional">Institutional</option>
                </select>
            </div>

            <!-- Image Uploads -->
            <div class="form-group">
                <label for="images">Upload Images (Max 5MB per image, JPG/PNG)</label>
                <input type="file" class="form-control-file" id="images" name="images[]" multiple accept="image/jpeg, image/png">
                <small class="form-text text-muted">You can select multiple images.</small>
            </div>

            <!-- YouTube Link -->
            <div class="form-group">
                <label for="youtube_link">YouTube Video Link (Optional)</label>
                <input type="url" class="form-control" id="youtube_link" name="youtube_link" placeholder="https://www.youtube.com/watch?v=...">
            </div>

            <hr>
            <p><strong>Person in Charge (PIC) for this Achievement/Project</strong></p>
             <div class="form-group">
                <label for="pic_name">PIC Name</label>
                <input type="text" class="form-control" id="pic_name" name="pic_name" value="<?php echo $_SESSION['user_name'] ?? ''; ?>" required>
                 <small class="form-text text-muted">Defaults to your name, but can be changed if you are submitting on behalf of someone else (with their permission).</small>
            </div>
             <div class="form-group">
                <label for="pic_email">PIC Email</label>
                <input type="email" class="form-control" id="pic_email" name="pic_email" value="<?php echo $_SESSION['user_email'] ?? ''; ?>" required>
            </div>
             <div class="form-group">
                <label for="pic_phone">PIC Phone Number</label>
                <input type="tel" class="form-control" id="pic_phone" name="pic_phone" value="<?php /* TODO: Fetch from user profile if available */ ?>" required>
            </div>


            <button type="submit" class="btn btn-primary" disabled><i class="fas fa-paper-plane"></i> Submit for Review (Form Inactive)</button>
        </form>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
