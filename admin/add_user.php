<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? 'student';

    if ($name && $email && $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $st = $pdo->prepare("INSERT INTO users(name, student_id, email, password_hash, role)
                             VALUES (?,?,?,?,?)");
        $st->execute([$name, $student_id, $email, $hash, $role]);

        flash_set('success', 'User added successfully!');
        header('Location: /admin/users.php');
        exit;
    } else {
        flash_set('danger', 'Please fill all required fields.');
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add User</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container" style="max-width:600px">
    <h1 class="mb-3">Add New User</h1>

    <?php flash(); ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Full Name *</label>
        <input name="name" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Student ID</label>
        <input name="student_id" class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">Email *</label>
        <input name="email" type="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password *</label>
        <input name="password" type="password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control">
          <option value="student">Student</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <button class="btn btn-success w-100">Add User</button>
      <a href="/admin/users.php" class="btn btn-secondary w-100 mt-2">Back</a>
    </form>
  </div>
</body>
</html>
