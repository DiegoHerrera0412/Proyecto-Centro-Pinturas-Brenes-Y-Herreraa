<?php
require_once __DIR__ . '/../app/includes/auth.php';
if (empty($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}
header('Location: pos.php');
exit;
