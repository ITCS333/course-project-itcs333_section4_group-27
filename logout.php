<?php
require_once __DIR__ . '/inc/config.php';
require_once __DIR__ . '/inc/flash.php';
require_once __DIR__ . '/inc/auth.php';

do_logout();
flash_set('info', 'You have been logged out.');
header('Location: /');
exit;
