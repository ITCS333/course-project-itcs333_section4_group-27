<?php
// Task 5: Delete Discussion Comment
// Allows users to delete their own comments, or admin to delete any comment

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/discussion_db.php';

require_login();
$current_user = current_user($pdo);

$comment_id = $_GET['id'] ?? 0;
$topic_id = $_GET['topic_id'] ?? 0;

// Verify comment exists and get its owner
$stmt = $pdo->prepare("SELECT user_id FROM discussion_comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
  flash_set('danger', 'Comment not found.');
  header('Location: discussion_board.php');
  exit;
}

// Check permission: user owns comment OR user is admin
$can_delete = ($comment['user_id'] == $current_user['id']) || ($current_user['role'] === 'admin');

if (!$can_delete) {
  flash_set('danger', 'You do not have permission to delete this comment.');
  header("Location: view_topic.php?id=$topic_id");
  exit;
}

// Perform deletion
if (delete_comment($pdo, $comment_id)) {
  flash_set('success', 'Comment deleted successfully.');
} else {
  flash_set('danger', 'Failed to delete comment.');
}

header("Location: view_topic.php?id=$topic_id");
exit;
