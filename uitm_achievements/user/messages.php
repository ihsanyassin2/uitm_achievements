<?php
// user/messages.php
$page_title = "Messages - UiTM Achievements";
$config_path = __DIR__ . '/../config/config.php';
if (file_exists($config_path)) {
    require_once $config_path;
} else {
    die("Critical error: Main configuration file not found.");
}

require_login(); // User must be logged in
$user_id = $_SESSION['user_id'];

// TODO: Implement fetching and displaying messages related to user's submissions
// This could be a list of conversations, or messages grouped by achievement.
// For simplicity, it might initially show admin feedback on submissions.
// A more complex system would involve the `messages` table for two-way chat.

include_once SITE_ROOT . 'includes/header.php';

$conversations = []; // Placeholder for message data
$db = db_connect();

if ($db) {
    // Example: Fetch achievements that have admin feedback or messages associated
    // This is a simplified version focusing on feedback. A full chat would be more complex.
    $stmt = $db->prepare(
        "SELECT a.id as achievement_id, a.title as achievement_title, m.id as message_id, m.message, m.created_at as message_date, m.is_read,
        sender.full_name as sender_name, receiver.full_name as receiver_name
         FROM messages m
         JOIN achievements a ON m.achievement_id = a.id
         JOIN users sender ON m.sender_id = sender.id
         JOIN users receiver ON m.receiver_id = receiver.id
         WHERE (m.receiver_id = ? OR (m.sender_id = ? AND sender.role = 'user')) AND a.user_id = ?
         ORDER BY m.created_at DESC"
    );
    // This query fetches messages where the current user is the receiver OR the sender (if they initiated about their own achievement)
    // and the achievement belongs to the current user.
    // Adjust logic if admins can initiate messages not tied to a specific achievement of this user.

    if ($stmt) {
        $stmt->bind_param("iii", $user_id, $user_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            // Group by achievement_id for display
            $conversations[$row['achievement_id']]['title'] = $row['achievement_title'];
            $conversations[$row['achievement_id']]['messages'][] = $row;
            // Mark messages as read (simplified: mark all fetched for this user as read if they were the receiver)
            // A more robust way: do this via AJAX when a message/conversation is opened.
            if ($row['receiver_name'] == $_SESSION['user_full_name'] && !$row['is_read']) {
                $stmt_mark_read = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
                if($stmt_mark_read){
                    $stmt_mark_read->bind_param("i", $row['message_id']);
                    $stmt_mark_read->execute();
                    $stmt_mark_read->close();
                }
            }
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error fetching messages: " . $db->error;
    }
    $db->close();
} else {
    $_SESSION['error_message'] = "Database connection failed.";
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Messages & Feedback</h1>
</div>

<?php
display_message('success_message');
display_message('error_message');
?>

<p>Here you can find feedback from administrators regarding your submissions and communicate about them.</p>

<?php if (empty($conversations)): ?>
    <div class="alert alert-info">You have no messages or feedback at this time.</div>
<?php else: ?>
    <div class="accordion" id="messageAccordion">
        <?php foreach ($conversations as $achievement_id => $convo_data): ?>
            <div class="card">
                <div class="card-header" id="heading_<?php echo $achievement_id; ?>">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse_<?php echo $achievement_id; ?>" aria-expanded="true" aria-controls="collapse_<?php echo $achievement_id; ?>">
                            Conversation regarding: <strong><?php echo htmlspecialchars($convo_data['title']); ?></strong>
                            <?php
                                $unread_in_convo = false;
                                foreach ($convo_data['messages'] as $msg) {
                                    if ($msg['receiver_name'] == $_SESSION['user_full_name'] && !$msg['is_read']) {
                                        $unread_in_convo = true;
                                        break;
                                    }
                                }
                                if ($unread_in_convo) echo ' <span class="badge badge-danger">New</span>';
                            ?>
                        </button>
                    </h2>
                </div>

                <div id="collapse_<?php echo $achievement_id; ?>" class="collapse <?php // echo $unread_in_convo ? 'show' : ''; ?>" aria-labelledby="heading_<?php echo $achievement_id; ?>" data-parent="#messageAccordion">
                    <div class="card-body">
                        <?php foreach ($convo_data['messages'] as $message): ?>
                            <div class="media mb-3 <?php echo ($message['sender_name'] == $_SESSION['user_full_name']) ? 'text-right' : ''; ?>">
                                <!-- Optional: Add sender avatar -->
                                <div class="media-body">
                                    <h6 class="mt-0 mb-1">
                                        <?php echo ($message['sender_name'] == $_SESSION['user_full_name']) ? "You" : htmlspecialchars($message['sender_name']); ?>
                                        <small class="text-muted">- <?php echo date("d M Y, H:i", strtotime($message['message_date'])); ?></small>
                                    </h6>
                                    <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Reply Form (Placeholder) -->
                        <hr>
                        <h5>Reply:</h5>
                        <form action="<?php echo SITE_URL; ?>user/send_message.php" method="post">
                             <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                             <input type="hidden" name="achievement_id" value="<?php echo $achievement_id; ?>">
                             <?php
                                // Determine admin_id for reply (simplistic: find first admin sender or a default admin)
                                $admin_id_for_reply = null;
                                foreach($convo_data['messages'] as $msg_sender_check) {
                                    if($msg_sender_check['sender_name'] != $_SESSION['user_full_name']) {
                                        // Need to get actual sender ID from users table based on name, or store sender_id in message array
                                        // For now, this is a placeholder. A proper implementation would fetch sender's ID.
                                        // $admin_id_for_reply = get_user_id_by_name($msg_sender_check['sender_name']); // Fictional function
                                        // This needs to be the ID of the admin involved in this conversation.
                                        // A simpler approach: find the user ID of the admin who sent a message in this thread.
                                        // The messages table query already joins users table, so we can get sender's ID directly.
                                        // Let's assume the first non-user sender is the admin to reply to.
                                         $db_reply = db_connect();
                                         if($db_reply){
                                            $stmt_sender = $db_reply->prepare("SELECT id FROM users WHERE full_name = ? AND role = 'admin' LIMIT 1");
                                            if($stmt_sender){
                                                $stmt_sender->bind_param("s", $msg_sender_check['sender_name']);
                                                $stmt_sender->execute();
                                                $res_sender = $stmt_sender->get_result();
                                                if($row_sender = $res_sender->fetch_assoc()){
                                                    $admin_id_for_reply = $row_sender['id'];
                                                }
                                                $stmt_sender->close();
                                            }
                                            $db_reply->close();
                                         }
                                        if($admin_id_for_reply) break;
                                    }
                                }
                                // If no admin found in thread (e.g. user started it), default to a general admin if system allows.
                                // For now, this field might be empty if no clear admin recipient.
                                // A better system would have a dedicated admin_id associated with the achievement review process.
                                if(!$admin_id_for_reply && function_exists('get_setting')){
                                     // $admin_email_for_reply = get_setting('admin_email');
                                     // $admin_user_for_reply = get_user_by_email($admin_email_for_reply); // Fictional
                                     // if($admin_user_for_reply) $admin_id_for_reply = $admin_user_for_reply['id'];
                                }
                             ?>
                             <input type="hidden" name="receiver_id" value="<?php echo $admin_id_for_reply; // This needs to be the admin's ID ?>">
                             <div class="form-group">
                                 <textarea name="message_reply" class="form-control" rows="3" required placeholder="Type your reply..."></textarea>
                             </div>
                             <button type="submit" class="btn btn-primary btn-sm" <?php if(!$admin_id_for_reply) echo "disabled title='Cannot determine admin recipient for reply'";?>>
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                             <?php if(!$admin_id_for_reply) echo "<small class='text-danger d-block'>Could not determine admin recipient for this thread.</small>";?>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<p class="mt-4 text-muted">
    This messaging system is for communication related to your achievement submissions.
    For general inquiries, please contact the site administrator through other channels if available.
</p>

<?php
// TODO: Add send_message.php to handle message submissions.
include_once SITE_ROOT . 'includes/footer.php';
?>
