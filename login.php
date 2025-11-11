<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if (try_login($pdo, $email, $pass)) {
    flash_set('success', 'Welcome back!');
    header('Location: /'); exit;
  } else {
    flash_set('danger', 'Invalid email or password.');
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
  <div class="container" style="max-width:480px">
    <h1 class="mb-3">Login</h1>
    <?php flash(); ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <button class="btn btn-success w-100">Login</button>
      <p class="mt-3 small text-muted">Admin: admin@example.com / Admin@12345</p>
    </form>
  </div>
</body>
</html>
