<?php
// Task 5: General Discussion Board - Main Page
// This page displays all discussion topics and allows users to create new ones
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/discussion_db.php';
// Require login to access discussion board
require_login();
$current_user = current_user($pdo);
$topics = get_all_topics($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Discussion Board</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
   .topic-card {
     transition: transform 0.2s, box-shadow 0.2s;
     cursor: pointer;
   }
   .topic-card:hover {
     transform: translateY(-3px);
     box-shadow: 0 4px 12px rgba(0,0,0,0.15);
   }
   .badge-comments {
     background-color: #6c757d;
   }
   .author-badge {
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
<div class="container">
<div class="d-flex justify-content-between align-items-center mb-4">
<h1>Discussion Board</h1>
<a href="create_topic.php" class="btn btn-success">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle me-1" viewBox="0 0 16 16">
<path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
<path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
</svg>
       New Topic
</a>
</div>
<?php flash(); ?>
<?php if (empty($topics)): ?>
<div class="alert alert-info">
<strong>No discussions yet!</strong> Be the first to start a conversation.
</div>
<?php else: ?>
<div class="row">
<?php foreach ($topics as $topic): ?>
<div class="col-md-12 mb-3">
<div class="card topic-card" onclick="window.location.href='view_topic.php?id=<?= $topic['id'] ?>'">
<div class="card-body">
<div class="d-flex justify-content-between align-items-start">
<div class="flex-grow-1">
<h5 class="card-title mb-2"><?= e($topic['subject']) ?></h5>
<p class="card-text text-muted mb-2">
<?= e(substr($topic['message'], 0, 150)) ?><?= strlen($topic['message']) > 150 ? '...' : '' ?>
</p>
<div class="d-flex align-items-center gap-3 small text-muted">
<span>
<strong><?= e($topic['author_name']) ?></strong>
<?php if ($topic['author_role'] === 'admin'): ?>
<span class="badge bg-danger author-badge ms-1">Teacher</span>
<?php endif; ?>
</span>
<span>
<?= date('M j, Y \a\t g:i A', strtotime($topic['created_at'])) ?>
</span>
<span class="badge badge-comments">
<?= $topic['comment_count'] ?>
<?= $topic['comment_count'] == 1 ? 'reply' : 'replies' ?>
</span>
</div>
</div>
<div>
<?php if ($current_user['id'] == $topic['user_id'] || $current_user['role'] === 'admin'): ?>
<div class="dropdown" onclick="event.stopPropagation()">
<button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                         Actions
</button>
<ul class="dropdown-menu">
<?php if ($current_user['id'] == $topic['user_id']): ?>
<li><a class="dropdown-item" href="edit_topic.php?id=<?= $topic['id'] ?>">Edit</a></li>
<?php endif; ?>
<li>
<a class="dropdown-item text-danger"
                              href="delete_topic.php?id=<?= $topic['id'] ?>"
                              onclick="return confirm('Are you sure you want to delete this topic?')">
                             Delete
</a>
</li>
</ul>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<div class="mt-4">
<a href="index.html" class="btn btn-secondary">← Back to Home</a>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
