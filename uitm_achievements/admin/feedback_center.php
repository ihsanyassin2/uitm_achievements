<?php
$page_title = "Feedback Center - Admin";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: Admin role required
protect_page('admin');

// Placeholder content
// This page will allow admins to view and manage feedback/messages
// related to achievement submissions. It's the admin counterpart to user/feedback.php.

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/admin_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-comments-dollar"></i> Feedback Center</h2>
        <p>Manage communications with users regarding their achievement submissions.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This area will provide a centralized place for administrators to view all communication threads with users about their submissions. Admins can reply to users, initiate messages, and track the history of feedback given.
        </div>

        <!-- Example: List of conversations or a more integrated view -->
        <h4>Recent Conversations:</h4>
        <div class="list-group">
            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Re: Submission "Breakthrough in Quantum Computing"</h5>
                    <small class="text-muted">3 days ago</small>
                </div>
                <p class="mb-1">User: Dr. Jane Doe - "I've updated the images as requested."</p>
                <small class="text-muted">Last message from user. <span class="badge badge-warning">Needs Reply</span></small>
            </a>
            <a href="#" class="list-group-item list-group-item-action flex-column align-items-start">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Query on "Student Project Wins Award"</h5>
                    <small class="text-muted">5 days ago</small>
                </div>
                <p class="mb-1">Admin: You - "Please clarify the date of the award ceremony."</p>
                <small class="text-muted">Waiting for user response.</small>
            </a>
            <!-- More conversations -->
        </div>
        <p class="text-muted mt-3">No active conversations or feature under construction.</p>
         <a href="<?php echo SITE_URL; ?>admin/crud/crud_feedback_messages.php" class="btn btn-success mt-3"><i class="fas fa-table"></i> Advanced CRUD for Feedback Messages</a>


    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
