<?php
require_once __DIR__ . '/../inc/config.php';
require_once __DIR__ . '/../inc/flash.php';
require_once __DIR__ . '/../inc/auth.php';

require_admin();

// Get user ID from query
$id = $_GET['id'] ?? null;

if (!$id) {
    flash_set('danger', 'Invalid user ID.');
    header('Location: /admin/users.php');
    exit;
}

// Prevent deleting yourself
if ($id == current_user()['id']) {
    flash_set('danger', 'You cannot delete your own account.');
    header('Location: /admin/users.php');
    exit;
}

// Delete user
$st = $pdo->prepare("DELETE FROM users WHERE id = ?");
$st->execute([$id]);

flash_set('success', 'User deleted successfully!');
header('Location: /admin/users.php');
exit;
