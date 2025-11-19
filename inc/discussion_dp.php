<?php
// Task 5: Discussion Board Database Schema
// This file creates the necessary tables for the discussion board
require_once __DIR__ . '/config.php';
// Create topics table
$pdo->exec("
CREATE TABLE IF NOT EXISTS discussion_topics (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 user_id INTEGER NOT NULL,
 subject TEXT NOT NULL,
 message TEXT NOT NULL,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");
// Create comments/replies table
$pdo->exec("
CREATE TABLE IF NOT EXISTS discussion_comments (
 id INTEGER PRIMARY KEY AUTOINCREMENT,
 topic_id INTEGER NOT NULL,
 user_id INTEGER NOT NULL,
 comment_text TEXT NOT NULL,
 created_at TEXT DEFAULT CURRENT_TIMESTAMP,
 updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (topic_id) REFERENCES discussion_topics(id) ON DELETE CASCADE,
 FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
");
// Helper function to get all topics with user information
function get_all_topics($pdo) {
 $stmt = $pdo->query("
   SELECT dt.*, u.name as author_name, u.role as author_role,
          (SELECT COUNT(*) FROM discussion_comments WHERE topic_id = dt.id) as comment_count
   FROM discussion_topics dt
   JOIN users u ON dt.user_id = u.id
   ORDER BY dt.created_at DESC
 ");
 return $stmt->fetchAll();
}
// Helper function to get a single topic with full details
function get_topic_by_id($pdo, $topic_id) {
 $stmt = $pdo->prepare("
   SELECT dt.*, u.name as author_name, u.role as author_role, u.email as author_email
   FROM discussion_topics dt
   JOIN users u ON dt.user_id = u.id
   WHERE dt.id = ?
 ");
 $stmt->execute([$topic_id]);
 return $stmt->fetch();
}
// Helper function to get all comments for a topic
function get_topic_comments($pdo, $topic_id) {
 $stmt = $pdo->prepare("
   SELECT dc.*, u.name as commenter_name, u.role as commenter_role
   FROM discussion_comments dc
   JOIN users u ON dc.user_id = u.id
   WHERE dc.topic_id = ?
   ORDER BY dc.created_at ASC
 ");
 $stmt->execute([$topic_id]);
 return $stmt->fetchAll();
}
// Helper function to create a new topic
function create_topic($pdo, $user_id, $subject, $message) {
 $stmt = $pdo->prepare("
   INSERT INTO discussion_topics (user_id, subject, message)
   VALUES (?, ?, ?)
 ");
 return $stmt->execute([$user_id, $subject, $message]);
}
// Helper function to add a comment
function add_comment($pdo, $topic_id, $user_id, $comment_text) {
 $stmt = $pdo->prepare("
   INSERT INTO discussion_comments (topic_id, user_id, comment_text)
   VALUES (?, ?, ?)
 ");
 return $stmt->execute([$topic_id, $user_id, $comment_text]);
}
// Helper function to update a topic
function update_topic($pdo, $topic_id, $subject, $message) {
 $stmt = $pdo->prepare("
   UPDATE discussion_topics
   SET subject = ?, message = ?, updated_at = CURRENT_TIMESTAMP
   WHERE id = ?
 ");
 return $stmt->execute([$subject, $message, $topic_id]);
}
// Helper function to delete a topic
function delete_topic($pdo, $topic_id) {
 $stmt = $pdo->prepare("DELETE FROM discussion_topics WHERE id = ?");
 return $stmt->execute([$topic_id]);
}
// Helper function to delete a comment
function delete_comment($pdo, $comment_id) {
 $stmt = $pdo->prepare("DELETE FROM discussion_comments WHERE id = ?");
 return $stmt->execute([$comment_id]);
}
// Helper function to check if user owns a topic
function user_owns_topic($pdo, $topic_id, $user_id) {
 $stmt = $pdo->prepare("SELECT user_id FROM discussion_topics WHERE id = ?");
 $stmt->execute([$topic_id]);
 $topic = $stmt->fetch();
 return $topic && $topic['user_id'] == $user_id;
}
// Helper function to check if user owns a comment
function user_owns_comment($pdo, $comment_id, $user_id) {
 $stmt = $pdo->prepare("SELECT user_id FROM discussion_comments WHERE id = ?");
 $stmt->execute([$comment_id]);
 $comment = $stmt->fetch();
 return $comment && $comment['user_id'] == $user_id;
}
