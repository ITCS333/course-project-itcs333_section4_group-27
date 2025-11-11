<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin();

// Get user ID
$id = $_GET['id'] ?? null;
if (!$id) {
    flash_set('danger', 'Invalid user ID.');
    header('Location: /admin/users.php');
    exit;
}

// Fetch user
$st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$st->execute([$id]);
$user = $st->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    flash_set('danger', 'User not found.');
    header('Location: /admin/users.php');
    exit;
}

// When form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $role       = $_POST['role'] ?? 'student';
    $password   = $_POST['password'] ?? '';

    if ($name && $email) {
        // If password is filled → update password
        if ($password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name=?, student_id=?, email=?, role=?, password_hash=? WHERE id=?";
            $params = [$name, $student_id, $email, $role, $hash, $id];
        } else {
            $sql = "UPDATE users SET name=?, student_id=?, email=?, role=? WHERE id=?";
            $params = [$name, $student_id, $email, $role, $id];
        }

        $update = $pdo->prepare($sql);
        $update->execute($params);

        flash_set('success', 'User updated successfully!');
        header('Location: /admin/users.php');
        exit;
    } else {
        flash_set('danger', 'Name and Email are required.');
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit User</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container" style="max-width:600px">
    <h1 class="mb-3">Edit User</h1>

    <?php flash(); ?>

    <form method="post">

      <div class="mb-3">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" required value="<?= e($user['name']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Student ID</label>
        <input name="student_id" class="form-control" value="<?= e($user['student_id']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Email *</label>
        <input name="email" type="email" class="form-control" required value="<?= e($user['email']) ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control">
          <option value="student" <?= $user['role']=='student'?'selected':'' ?>>Student</option>
          <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">New Password (leave empty to keep old password)</label>
        <input name="password" type="password" class="form-control">
      </div>

      <button class="btn btn-warning w-100">Save Changes</button>
      <a href="/admin/users.php" class="btn btn-secondary w-100 mt-2">Back</a>
    </form>

  </div>
</body>
</html>
