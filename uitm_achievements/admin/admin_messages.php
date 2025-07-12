<?php
// admin/admin_messages.php
$page_title = "Feedback Messages - Admin Panel";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login('admin');
include_once SITE_ROOT . 'includes/header.php';

// TODO: Implement admin messaging interface
// - List conversations or messages (similar to user's message page but from admin perspective)
// - Ability to reply to users regarding their submissions
// - View message history with a user concerning a specific achievement
// - Mark messages as read/unread
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Feedback & Messages</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo SITE_URL; ?>admin/crud/crud_messages.php" class="btn btn-sm btn-outline-warning">
            <i class="fas fa-database"></i> View Messages Table (CRUD)
        </a>
    </div>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>This page allows administrators to view messages from users and provide feedback, typically related to achievement submissions.</p>

<!-- Placeholder for Message Listing / Conversation View -->
<div class="card">
    <div class="card-header">
        <i class="fas fa-comments"></i> Conversations
        <!-- TODO: Add filters for read/unread, user, achievement -->
    </div>
    <div class="card-body">
        <p class="text-center text-muted">Messaging interface functionality is pending implementation.</p>
        <!-- Example of how a conversation item might look (TODO: Replace with dynamic data)
        <div class="list-group">
            <a href="<?php //echo SITE_URL; ?>admin/view_conversation.php?achievement_id=1&user_id=5" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Regarding: "AI Research Project"</h5>
                    <small>3 days ago</small>
                </div>
                <p class="mb-1">User: John Doe (ID: 5)</p>
                <small class="text-muted">Last message: "I have updated the details as requested..." <span class="badge badge-info">User Reply</span></small>
                 <span class="badge badge-danger float-right mt-1">Unread</span>
            </a>
            <a href="#" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1">Regarding: "Community Outreach Program"</h5>
                    <small class="text-muted">5 days ago</small>
                </div>
                <p class="mb-1">User: Jane Smith (ID: 8)</p>
                <small class="text-muted">Last message: "Approved. Great work!" (You)</small>
            </a>
        </div>
        -->
    </div>
    <div class="card-footer text-muted">
        <!-- TODO: Pagination if many conversations -->
        End of message list.
    </div>
</div>


<?php
include_once SITE_ROOT . 'includes/footer.php';
?>
