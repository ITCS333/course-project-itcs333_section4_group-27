<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "course_db";

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");

    // this is to create users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            email VARCHAR(100) UNIQUE,
            password VARCHAR(255),
            role ENUM('admin','student') DEFAULT 'student'
        )
    ");

    //this is to insert default admin
    $check = $pdo->query("SELECT * FROM users WHERE role='admin'")->fetch();
    if (!$check) {
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")
            ->execute(['Admin','admin@course.com',$hashed,'admin']);
        echo "database setup complete. Admin login: admin@course.com / admin123";
    } else {
        echo "Admin already exists.";
    }
} catch (PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
