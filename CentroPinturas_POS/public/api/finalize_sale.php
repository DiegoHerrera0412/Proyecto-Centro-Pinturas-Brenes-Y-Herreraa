<?php
require_once __DIR__ . '/_bootstrap.php';
require_role(['admin','cajero','vendedor']);

$d = json_in();

$items = $d['items'] ?? [];
if (!is_array($items) || count($items) === 0) fail('Carrito vacío');

$id_cliente = (int)($d['id_cliente'] ?? 0);
if ($id_cliente <= 0) $id_cliente = null;

$descuento = (float)($d['descuento'] ?? 0);
$impuesto = (float)($d['impuesto'] ?? 0);
$metodo = trim((string)($d['metodo_pago'] ?? 'Efectivo'));
$obs = trim((string)($d['observacion'] ?? ''));

$id_usuario = (int)($_SESSION['user']['id_usuario'] ?? 0);
if ($id_usuario <= 0) fail('Usuario inválido', 401);

$ids = [];
foreach ($items as $it) {
  $pid = (int)($it['id_producto'] ?? 0);
  $qty = (int)($it['cantidad'] ?? 0);
  if ($pid <= 0 || $qty <= 0) fail('Item inválido');
  $ids[] = $pid;
}

$in = implode(',', array_fill(0, count($ids), '?'));

$pdo->beginTransaction();
try {
  $st = $pdo->prepare("SELECT id_producto, nombre, precio, stock
                       FROM dbo.producto
                       WHERE id_producto IN ($in) AND activo = 1");
  $st->execute($ids);
  $prods = $st->fetchAll();
  if (count($prods) !== count(array_unique($ids))) fail('Producto no encontrado');

  $byId = [];
  foreach ($prods as $p) $byId[(int)$p['id_producto']] = $p;

  $subtotal = 0.0;
  foreach ($items as $it) {
    $pid = (int)$it['id_producto'];
    $qty = (int)$it['cantidad'];
    $p = $byId[$pid];
    if ((int)$p['stock'] < $qty) fail('Stock insuficiente: ' . $p['nombre'], 409);
    $subtotal += ((float)$p['precio']) * $qty;
  }

  $total = $subtotal - $descuento + $impuesto;
  if ($total < 0) $total = 0;

  $stV = $pdo->prepare("INSERT INTO dbo.venta(id_cliente, id_usuario, subtotal, descuento, impuesto, total, metodo_pago, observacion)
                        OUTPUT INSERTED.id_venta
                        VALUES (?,?,?,?,?,?,?,?)");
  $stV->execute([$id_cliente, $id_usuario, $subtotal, $descuento, $impuesto, $total, $metodo, $obs ?: null]);
  $id_venta = (int)$stV->fetchColumn();

  $consecutivo = 'F' . date('Ymd') . '-' . str_pad((string)$id_venta, 6, '0', STR_PAD_LEFT);

  $stF = $pdo->prepare("INSERT INTO dbo.factura(id_venta, total, consecutivo)
                        OUTPUT INSERTED.id_factura
                        VALUES (?,?,?)");
  $stF->execute([$id_venta, $total, $consecutivo]);
  $id_factura = (int)$stF->fetchColumn();

  $stD = $pdo->prepare("INSERT INTO dbo.detalle_venta(id_venta, id_producto, cantidad, precio_unitario)
                        VALUES (?,?,?,?)");
  $stUpd = $pdo->prepare("UPDATE dbo.producto SET stock = stock - ? WHERE id_producto = ?");
  $stMov = $pdo->prepare("INSERT INTO dbo.inventario_mov(id_producto, tipo, cantidad, referencia)
                          VALUES (?,?,?,?)");

  foreach ($items as $it) {
    $pid = (int)$it['id_producto'];
    $qty = (int)$it['cantidad'];
    $price = (float)$byId[$pid]['precio'];
    $stD->execute([$id_venta, $pid, $qty, $price]);
    $stUpd->execute([$qty, $pid]);
    $stMov->execute([$pid, 'SALIDA', $qty, 'VENTA#' . $id_venta]);
  }

  $pdo->commit();
  ok(['id_factura'=>$id_factura, 'id_venta'=>$id_venta, 'consecutivo'=>$consecutivo]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  fail('Error al finalizar: ' . $e->getMessage(), 500);
}
