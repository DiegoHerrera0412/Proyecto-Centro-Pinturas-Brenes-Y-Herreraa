<?php
require_once __DIR__ . '/_bootstrap.php';
require_role(['admin','cajero','vendedor']);

$q = trim($_GET['q'] ?? '');
$limit = 25;

if ($q === '') {
  $st = $pdo->query("SELECT TOP {$limit} id_producto, nombre, sku, precio, stock
                     FROM dbo.producto
                     WHERE activo = 1
                     ORDER BY creado_en DESC");
  ok($st->fetchAll());
}

$like = '%' . $q . '%';
$st = $pdo->prepare("SELECT TOP {$limit} id_producto, nombre, sku, precio, stock
                     FROM dbo.producto
                     WHERE activo = 1 AND (nombre LIKE ? OR sku LIKE ?)
                     ORDER BY CASE WHEN sku = ? THEN 0 ELSE 1 END, nombre ASC");
$st->execute([$like, $like, $q]);
ok($st->fetchAll());
