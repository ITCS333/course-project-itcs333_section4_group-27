<?php
function flash_set($type, $msg){ $_SESSION['flash'][$type] = $msg; }
function flash($type = null){
  if ($type === null) {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $k=>$v){
      echo '<div class="alert alert-'.e($k).'">'.e($v).'</div>';
    }
    unset($_SESSION['flash']);
  } else {
    if (!empty($_SESSION['flash'][$type])){
      echo '<div class="alert alert-'.e($type).'">'.e($_SESSION['flash'][$type]).'</div>';
      unset($_SESSION['flash'][$type]);
    }
  }
}
