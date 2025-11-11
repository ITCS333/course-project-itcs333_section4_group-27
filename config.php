<?php
ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'On');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dsn = 'sqlite:' . __DIR__ . '/../database.sqlite';
$pdo = new PDO($dsn);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  student_id TEXT,
  email TEXT NOT NULL UNIQUE,
  password_hash TEXT NOT NULL,
  role TEXT NOT NULL CHECK(role IN ('admin','student')) DEFAULT 'student',
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
");

$check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$check->execute(['admin@example.com']);
if (!$check->fetchColumn()) {
  $hash = password_hash('Admin@12345', PASSWORD_BCRYPT);
  $ins = $pdo->prepare("INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,?)");
  $ins->execute(['Course Admin','admin@example.com',$hash,'admin']);
}

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
