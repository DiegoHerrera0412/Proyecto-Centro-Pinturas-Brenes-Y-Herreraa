<?php
require_once __DIR__ . '/_bootstrap.php';
require_role(['admin','cajero','vendedor']);

$q = trim($_GET['q'] ?? '');
$limit = 25;

if ($q === '') {
  $st = $pdo->query("SELECT id_producto, nombre, sku, precio, stock
                     FROM producto
                     WHERE activo = 1
                     ORDER BY id_producto DESC
                     LIMIT {$limit}");
  ok($st->fetchAll());
}

$like = '%' . $q . '%';

$st = $pdo->prepare("SELECT id_producto, nombre, sku, precio, stock
                     FROM producto
                     WHERE activo = 1
                     AND (nombre LIKE ? OR sku LIKE ?)
                     ORDER BY nombre ASC
                     LIMIT {$limit}");

$st->execute([$like, $like]);

ok($st->fetchAll());
