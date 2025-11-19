<?php
// Task 5: Create New Discussion Topic
// Allows logged-in users (both admin and students) to create new discussion topics

require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/discussion_db.php';

require_login();
$current_user = current_user($pdo);

$errors = [];
$subject = '';
$message = '';

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
    if (create_topic($pdo, $current_user['id'], $subject, $message)) {
      flash_set('success', 'Topic created successfully!');
      header('Location: discussion_board.php');
      exit;
    } else {
      $errors[] = 'Failed to create topic. Please try again.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Topic</title>
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
      <h1>Create New Discussion Topic</h1>
      <p class="text-muted">Start a new conversation with your classmates and teacher</p>
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
        <form method="POST" action="" id="createTopicForm">
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
              placeholder="Enter a clear and descriptive subject"
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
              placeholder="Write your message here..."
              required
            ><?= e($message) ?></textarea>
            <div class="form-text">Minimum 10 characters</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send me-1" viewBox="0 0 16 16">
                <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.1 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z"/>
              </svg>
              Create Topic
            </button>
            <a href="discussion_board.php" class="btn btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Client-side validation
    document.getElementById('createTopicForm').addEventListener('submit', function(e) {
      const subject = document.getElementById('subject').value.trim();
      const message = document.getElementById('message').value.trim();
      
      if (subject.length < 5) {
        alert('Subject must be at least 5 characters long.');
        e.preventDefault();
        return false;
      }
      
      if (message.length < 10) {
        alert('Message must be at least 10 characters long.');
        e.preventDefault();
        return false;
      }
    });

    // Character counter
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    
    subjectInput.addEventListener('input', function() {
      const remaining = 200 - this.value.length;
      this.nextElementSibling.textContent = `${this.value.length}/200 characters`;
    });
  </script>
</body>
</html>
