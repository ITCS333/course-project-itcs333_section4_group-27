<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
require 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h2>Welcome, <?php echo $_SESSION['user']['name']; ?></h2>
<nav>
    <a href="add_student.php">Add Student</a> |
    <a href="dashboard.php">View Students</a> |
    <a href="logout.php">Logout</a>
</nav>

<?php
$stmt = $pdo->query("SELECT * FROM users WHERE role='student'");
echo "<h3>Registered Students</h3>";
echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Action</th></tr>";
while ($s = $stmt->fetch()) {
    echo "<tr>
        <td>{$s['id']}</td>
        <td>{$s['name']}</td>
        <td>{$s['email']}</td>
        <td>
          <a href='edit_student.php?id={$s['id']}'>Edit</a> |
          <a href='delete_student.php?id={$s['id']}'>Delete</a>
        </td>
    </tr>";
}
echo "</table>";
?>
</body>
</html>
