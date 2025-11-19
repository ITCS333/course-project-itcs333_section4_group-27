<?php
// Task 5: Delete Discussion Topic
// Allows users to delete their own topics, or admin to delete any topic

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/discussion_db.php';

require_login();
$current_user = current_user($pdo);

$topic_id = $_GET['id'] ?? 0;
$topic = get_topic_by_id($pdo, $topic_id);

if (!$topic) {
  flash_set('danger', 'Topic not found.');
  header('Location: discussion_board.php');
  exit;
}

// Check permission: user owns topic OR user is admin
$can_delete = ($topic['user_id'] == $current_user['id']) || ($current_user['role'] === 'admin');

if (!$can_delete) {
  flash_set('danger', 'You do not have permission to delete this topic.');
  header('Location: discussion_board.php');
  exit;
}

// Perform deletion
if (delete_topic($pdo, $topic_id)) {
  flash_set('success', 'Topic deleted successfully.');
} else {
  flash_set('danger', 'Failed to delete topic.');
}

header('Location: discussion_board.php');
exit;
