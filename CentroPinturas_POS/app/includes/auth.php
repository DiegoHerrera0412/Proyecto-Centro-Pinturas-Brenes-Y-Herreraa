<?php
session_start();
require_once __DIR__ . '/env.php';

function app_key(): string {
  $env = env_load(__DIR__ . '/../../.env');
  return $env['APP_KEY'] ?? 'demo_key_change_me';
}

function sha_pass(string $plain): string {
  return hash('sha256', app_key() . '|' . $plain);
}

function require_login(): void {
  if (empty($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
  }
}

function require_role(array $roles): void {
  require_login();
  $r = $_SESSION['user']['rol'] ?? '';
  if (!in_array($r, $roles, true)) {
    http_response_code(403);
    echo "403 - Sin permisos";
    exit;
  }
}
