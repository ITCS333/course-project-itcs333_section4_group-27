<?php
// Task 5: Edit Discussion Topic
// Allows users to edit their own topics (not admin override)

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

// Check ownership - only the creator can edit
if ($topic['user_id'] != $current_user['id']) {
  flash_set('danger', 'You can only edit your own topics.');
  header('Location: discussion_board.php');
  exit;
}

$errors = [];
$subject = $topic['subject'];
$message = $topic['message'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');
  
  // Validation
  if (empty($subject)) {
    $errors[] = 'Subject is required.';
  } elseif (strlen($subject) < 5) {
    $errors[] = 'Subject must be at least 5 characters.';
  } elseif (strlen($subject) > 200) {
    $errors[] = 'Subject must not exceed 200 characters.';
  }
  
  if (empty($message)) {
    $errors[] = 'Message is required.';
  } elseif (strlen($message) < 10) {
    $errors[] = 'Message must be at least 10 characters.';
  }
  
  if (empty($errors)) {
    if (update_topic($pdo, $topic_id, $subject, $message)) {
      flash_set('success', 'Topic updated successfully!');
      header("Location: view_topic.php?id=$topic_id");
      exit;
    } else {
      $errors[] = 'Failed to update topic. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Topic</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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

  <div class="container" style="max-width: 800px;">
    <div class="mb-4">
      <h1>Edit Topic</h1>
      <p class="text-muted">Update your discussion topic</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
          <?php foreach ($errors as $error): ?>
            <li><?= e($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="">
          <div class="mb-3">
            <label for="subject" class="form-label">
              Subject <span class="text-danger">*</span>
            </label>
            <input 
              type="text" 
              class="form-control" 
              id="subject" 
              name="subject" 
              maxlength="200"
              value="<?= e($subject) ?>"
              required
            >
            <div class="form-text">5-200 characters</div>
          </div>

          <div class="mb-3">
            <label for="message" class="form-label">
              Message <span class="text-danger">*</span>
            </label>
            <textarea 
              class="form-control" 
              id="message" 
              name="message" 
              rows="8"
              required
            ><?= e($message) ?></textarea>
            <div class="form-text">Minimum 10 characters</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Update Topic</button>
            <a href="view_topic.php?id=<?= $topic_id ?>" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
