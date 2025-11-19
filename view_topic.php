<?php
// Task 5: View Topic with Comments
// Displays a topic and all its comments, allows users to add new comments

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

$comments = get_topic_comments($pdo, $topic_id);

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_comment'])) {
  $comment_text = trim($_POST['comment_text'] ?? '');
  
  if (empty($comment_text)) {
    flash_set('danger', 'Comment cannot be empty.');
  } elseif (strlen($comment_text) < 3) {
    flash_set('danger', 'Comment must be at least 3 characters.');
  } else {
    if (add_comment($pdo, $topic_id, $current_user['id'], $comment_text)) {
      flash_set('success', 'Comment added successfully!');
      header("Location: view_topic.php?id=$topic_id");
      exit;
    } else {
      flash_set('danger', 'Failed to add comment.');
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($topic['subject']) ?> - Discussion</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    .comment-box {
      border-left: 3px solid #0d6efd;
      background-color: #f8f9fa;
      padding: 1rem;
      margin-bottom: 1rem;
    }
    .original-post {
      background-color: #e7f3ff;
      border-left: 4px solid #0d6efd;
    }
    .admin-badge {
      font-size: 0.75rem;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
      <a class="navbar-brand" href="index.html">Web Dev Course</a>
      <div class="ms-auto">
        <span class="text-white me-3">Welcome, <?= e($current_user['name']) ?></span>
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container" style="max-width: 900px;">
    <div class="mb-3">
      <a href="discussion_board.php" class="btn btn-outline-secondary btn-sm">
        ← Back to All Topics
      </a>
    </div>

    <?php flash(); ?>

    <!-- Original Topic Post -->
    <div class="card mb-4 original-post">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="mb-1"><?= e($topic['subject']) ?></h4>
          <small class="text-muted">
            Posted by <strong><?= e($topic['author_name']) ?></strong>
            <?php if ($topic['author_role'] === 'admin'): ?>
              <span class="badge bg-danger admin-badge">Teacher</span>
            <?php endif; ?>
            on <?= date('M j, Y \a\t g:i A', strtotime($topic['created_at'])) ?>
          </small>
        </div>
        <?php if ($current_user['id'] == $topic['user_id'] || $current_user['role'] === 'admin'): ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
              Options
            </button>
            <ul class="dropdown-menu">
              <?php if ($current_user['id'] == $topic['user_id']): ?>
                <li><a class="dropdown-item" href="edit_topic.php?id=<?= $topic['id'] ?>">Edit Topic</a></li>
              <?php endif; ?>
              <li>
                <a class="dropdown-item text-danger" 
                   href="delete_topic.php?id=<?= $topic['id'] ?>" 
                   onclick="return confirm('Delete this entire topic and all comments?')">
                  Delete Topic
                </a>
              </li>
            </ul>
          </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <p class="mb-0" style="white-space: pre-wrap;"><?= e($topic['message']) ?></p>
      </div>
    </div>

    <!-- Comments Section -->
    <div class="mb-4">
      <h5 class="mb-3">
        Replies (<?= count($comments) ?>)
      </h5>

      <?php if (empty($comments)): ?>
        <div class="alert alert-info">
          No replies yet. Be the first to comment!
        </div>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div class="comment-box">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <div>
                <strong><?= e($comment['commenter_name']) ?></strong>
                <?php if ($comment['commenter_role'] === 'admin'): ?>
                  <span class="badge bg-danger admin-badge ms-1">Teacher</span>
                <?php endif; ?>
                <br>
                <small class="text-muted">
                  <?= date('M j, Y \a\t g:i A', strtotime($comment['created_at'])) ?>
                </small>
              </div>
              <?php if ($current_user['id'] == $comment['user_id'] || $current_user['role'] === 'admin'): ?>
                <a href="delete_comment.php?id=<?= $comment['id'] ?>&topic_id=<?= $topic_id ?>" 
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this comment?')">
                  Delete
                </a>
              <?php endif; ?>
            </div>
            <p class="mb-0" style="white-space: pre-wrap;"><?= e($comment['comment_text']) ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Add Comment Form -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">Add Your Reply</h6>
      </div>
      <div class="card-body">
        <form method="POST" action="">
          <div class="mb-3">
            <textarea 
              class="form-control" 
              name="comment_text" 
              rows="4" 
              placeholder="Write your reply here..."
              required
            ></textarea>
          </div>
          <button type="submit" name="add_comment" class="btn btn-primary">
            Post Reply
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
