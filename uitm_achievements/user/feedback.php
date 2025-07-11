<?php
$page_title = "Feedback/Messages - UiTM Achievements";
require_once dirname(__FILE__) . '/../config/config.php';
require_once dirname(__FILE__) . '/../functions/functions.php';

// Protect page: User or Admin role required
protect_page(['user', 'admin']);

// Placeholder content
// This page will show a chat-like interface or message list
// for communication between user and admin regarding submissions.

include_once dirname(__FILE__) . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <?php include_once dirname(__FILE__) . '/../includes/user_sidebar.php'; ?>
    </div>
    <div class="col-md-9">
        <h2><i class="fas fa-comments"></i> Feedback and Messages</h2>
        <p>Communicate with administrators regarding your submissions.</p>
        <hr>

        <div class="alert alert-info">
            <strong>Under Construction!</strong> This section will allow you to view feedback from administrators on your achievement submissions and reply to them. It will function like a messaging system.
        </div>

        <!-- Example Chat/Message Structure -->
        <div class="card">
            <div class="card-header">
                Conversation about: <strong>"Example Achievement Title"</strong>
            </div>
            <div class="card-body chat-box">
                <!-- Messages will be loaded here -->
                <div class="message received">
                    <p><strong>Admin:</strong> Great submission! However, could you please provide a higher resolution image for the main photo?</p>
                    <span class="message-meta">October 26, 2023, 10:00 AM</span>
                </div>
                <div class="message sent">
                    <p><strong>You:</strong> Sure, I've uploaded a new image. Please check now.</p>
                    <span class="message-meta">October 26, 2023, 10:05 AM</span>
                </div>
                <div class="message received">
                    <p><strong>Admin:</strong> Perfect, thank you! This is now approved.</p>
                    <span class="message-meta">October 26, 2023, 10:15 AM</span>
                </div>
                 <div class="message sent">
                    <p><strong>You:</strong> Thanks for the quick review!</p>
                    <span class="message-meta">October 26, 2023, 10:17 AM</span>
                </div>
            </div>
            <div class="card-footer" id="chat-input">
                <form action="#" method="post">
                    <div class="input-group">
                        <textarea class="form-control" name="message" placeholder="Type your message..." rows="2" disabled></textarea>
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" disabled><i class="fas fa-paper-plane"></i> Send</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-3">
            <p class="text-muted">No active conversations or feature under construction.</p>
        </div>

    </div>
</div>

<?php
include_once dirname(__FILE__) . '/../includes/footer.php';
?>
