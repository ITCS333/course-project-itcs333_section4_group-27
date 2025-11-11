<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>ITCS333 Course Page</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container">
    <h1 class="mb-3">Welcome to the ITCS333 Course Page</h1>

    <?php flash(); ?>

    <?php if (current_user()): ?>
      <div class="alert alert-success">
        Logged in as: <strong><?= e(current_user()['name']) ?></strong>
        (role: <?= e(current_user()['role']) ?>)
      </div>

      <?php if (is_admin()): ?>
        <a href="/admin/index.php" class="btn btn-primary">Go to Admin Portal</a>
      <?php endif; ?>

      <a href="/logout.php" class="btn btn-outline-danger ms-2">Logout</a>
    <?php else: ?>
      <a href="/login.php" class="btn btn-success">Login</a>
    <?php endif; ?>
  </div>
</body>
</html>

