<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin(); 
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Portal</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container">
    <h1>Admin Portal</h1>
    <?php flash(); ?>
    <p>Admin logged in successfully.</p>

    <a href="/admin/users.php" class="btn btn-primary">Manage Users</a>
    <a href="/admin/change_password.php" class="btn btn-warning ms-2">Change Password</a>
    <a class="btn btn-secondary ms-2" href="/">Back to Home</a>
  </div>
</body>
</html>

