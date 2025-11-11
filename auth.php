<?php
require_once __DIR__ . '/config.php';

function current_user(){ return $_SESSION['user'] ?? null; }
function is_admin(){ return current_user() && (current_user()['role'] === 'admin'); }

function require_login(){
  if (!current_user()){
    flash_set('warning', 'Please log in.');
    header('Location: /login.php'); exit;
  }
}

function require_admin(){
  require_login();
  if (!is_admin()){
    http_response_code(403);
    echo 'Forbidden'; exit;
  }
}

function try_login(PDO $pdo, $email, $password){
  $st = $pdo->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email = ?");
  $st->execute([$email]);
  $u = $st->fetch(PDO::FETCH_ASSOC);
  if ($u && password_verify($password, $u['password_hash'])){
    $_SESSION['user'] = [
      'id'=>$u['id'], 'name'=>$u['name'], 'email'=>$u['email'], 'role'=>$u['role']
    ];
    return true;
  }
  return false;
}

function do_logout(){
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
}

