<?php
require_once __DIR__ . '/_bootstrap.php';
require_role(['admin','cajero','vendedor']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $q = trim($_GET['q'] ?? '');
  $limit = 15;

  if ($q === '') ok([]);

  $like = '%' . $q . '%';

  $st = $pdo->prepare("SELECT id_cliente, nombre, apellido, telefono, correo, direccion
                       FROM cliente
                       WHERE nombre LIKE ? OR apellido LIKE ? OR telefono LIKE ? OR correo LIKE ?
                       ORDER BY id_cliente DESC
                       LIMIT {$limit}");

  $st->execute([$like,$like,$like,$like]);
  ok($st->fetchAll());
}

if ($method === 'POST') {
  $d = json_in();

  $nombre = trim((string)($d['nombre'] ?? ''));
  if ($nombre === '') fail('Nombre requerido');

  $apellido = trim((string)($d['apellido'] ?? ''));
  $telefono = trim((string)($d['telefono'] ?? ''));
  $correo = trim((string)($d['correo'] ?? ''));
  $direccion = trim((string)($d['direccion'] ?? ''));

  $st = $pdo->prepare("INSERT INTO cliente(nombre, apellido, telefono, correo, direccion)
                       VALUES (?,?,?,?,?)");

  $st->execute([$nombre, $apellido ?: null, $telefono ?: null, $correo ?: null, $direccion ?: null]);

  $id = (int)$pdo->lastInsertId();

  $st2 = $pdo->prepare("SELECT id_cliente, nombre, apellido, telefono, correo, direccion
                        FROM cliente WHERE id_cliente=?");

  $st2->execute([$id]);

  ok($st2->fetch());
}

fail('Método no soportado', 405);
