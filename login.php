<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Login</h2>
<form method="POST">
    <label>Email</label><br>
    <input type="email" name="email" required><br><br>
    <label>Password</label><br>
    <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
<p style="color:red;"><?php echo $error ?? ''; ?></p>
</body>
</html>
