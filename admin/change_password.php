<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    
    $st = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $st->execute([current_user()['id']]);
    $admin = $st->fetch(PDO::FETCH_ASSOC);

    if (!password_verify($current, $admin['password_hash'])) {
        flash_set('danger', 'Current password is incorrect.');
    }
    
    elseif ($new !== $confirm) {
        flash_set('danger', 'New passwords do not match.');
    }
    else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $up = $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $up->execute([$hash, current_user()['id']]);

        flash_set('success', 'Password changed successfully!');
        header('Location: /admin/index.php');
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Change Password</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container" style="max-width:600px">
    <h1 class="mb-3">Change Password</h1>

    <?php flash(); ?>

    <form method="post">

      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">New Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>

      <button class="btn btn-warning w-100">Update Password</button>
      <a href="/admin/index.php" class="btn btn-secondary w-100 mt-2">Back</a>

    </form>
  </div>
</body>
</html>
