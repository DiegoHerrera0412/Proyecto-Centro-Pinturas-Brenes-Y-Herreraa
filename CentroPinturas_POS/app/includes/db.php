<?php
// app/includes/db.php
require_once __DIR__ . '/env.php';

// Cargar variables desde .env si existe
$__env = env_load(__DIR__ . '/../../.env');

$DB_HOST = $__env['DB_HOST'] ?? 'localhost';
$DB_NAME = $__env['DB_NAME'] ?? 'CentroPinturas';
$DB_USER = $__env['DB_USER'] ?? 'root';
$DB_PASS = $__env['DB_PASS'] ?? '';
$DB_PORT = $__env['DB_PORT'] ?? '3306';

try {
    $dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset=utf8mb4";

    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Error de conexión a la base de datos: ' . $e->getMessage());
}
