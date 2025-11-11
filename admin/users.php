<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin(); // only admin can see this page

// Fetch all users
$st = $pdo->query("SELECT id, name, student_id, email, role, created_at FROM users");
$users = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>User Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container">

    <h1 class="mb-3">User Management</h1>

    <?php flash(); ?>

    <a href="add_user.php" class="btn btn-success mb-3">Add New User</a>

    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Student ID</th>
          <th>Email</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>

      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['id']) ?></td>
          <td><?= e($u['name']) ?></td>
          <td><?= e($u['student_id']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['role']) ?></td>
          <td>
            <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-warning">Edit</a>

            <a href="delete_user.php?id=<?= $u['id'] ?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Are you sure you want to delete this user?');">
               Delete
            </a>
          </td>
        </tr>
      <?php endforeach; ?>

      </tbody>
    </table>

  </div>
</body>
</html>
