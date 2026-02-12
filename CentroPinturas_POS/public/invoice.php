<?php
require_once __DIR__ . '/../app/includes/db.php';
require_once __DIR__ . '/../app/includes/auth.php';
require_once __DIR__ . '/../app/includes/ui.php';

require_login();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo "Factura inválida"; exit; }

$st = $pdo->prepare("SELECT f.id_factura, f.consecutivo, f.fecha_emision, f.total,
                            v.id_venta, v.subtotal, v.descuento, v.impuesto, v.total AS total_venta, v.metodo_pago, v.observacion,
                            c.id_cliente, c.nombre AS c_nombre, c.apellido AS c_apellido, c.telefono, c.correo, c.direccion,
                            u.nombre AS u_nombre
                     FROM factura f
                     JOIN venta v ON v.id_venta = f.id_venta
                     LEFT JOIN cliente c ON c.id_cliente = v.id_cliente
                     JOIN usuario u ON u.id_usuario = v.id_usuario
                     WHERE f.id_factura = ?");
$st->execute([$id]);
$head = $st->fetch();

if (!$head) { http_response_code(404); echo "No existe"; exit; }

$dt = $pdo->prepare("SELECT d.cantidad,
                            d.precio_unitario,
                            (d.cantidad * d.precio_unitario) AS total_linea,
                            p.nombre,
                            p.sku
                     FROM detalle_venta d
                     JOIN producto p ON p.id_producto = d.id_producto
                     WHERE d.id_venta = ?
                     ORDER BY d.id_detalle ASC");

$dt->execute([(int)$head['id_venta']]);
$items = $dt->fetchAll();

$cliente = trim(($head['c_nombre'] ?? 'Cliente') . ' ' . ($head['c_apellido'] ?? ''));

function fmt($n){
    return "₡" . number_format((float)$n, 2);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura <?= htmlspecialchars($head['consecutivo']) ?></title>
<style>
body { font-family: Arial, sans-serif; padding: 30px; }
h1 { margin-bottom: 5px; }
.small { color: #666; font-size: 14px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 1px solid #ddd; padding: 8px; }
th { background: #f4f4f4; text-align: left; }
.right { text-align: right; }
.total { font-weight: bold; }
.box { margin-top: 20px; }
</style>
</head>
<body>

<h1>Factura #<?= htmlspecialchars($head['consecutivo']) ?></h1>
<div class="small">
Fecha: <?= htmlspecialchars($head['fecha_emision']) ?><br>
Vendedor: <?= htmlspecialchars($head['u_nombre']) ?>
</div>

<div class="box">
<strong>Cliente:</strong><br>
<?= htmlspecialchars($cliente ?: 'Cliente contado') ?><br>
<?= htmlspecialchars($head['telefono'] ?? '') ?><br>
<?= htmlspecialchars($head['correo'] ?? '') ?><br>
<?= htmlspecialchars($head['direccion'] ?? '') ?>
</div>

<table>
<thead>
<tr>
<th>Producto</th>
<th>SKU</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Total</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $it): ?>
<tr>
<td><?= htmlspecialchars($it['nombre']) ?></td>
<td><?= htmlspecialchars($it['sku']) ?></td>
<td><?= (int)$it['cantidad'] ?></td>
<td class="right"><?= fmt($it['precio_unitario']) ?></td>
<td class="right"><?= fmt($it['total_linea']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="box right">
Subtotal: <?= fmt($head['subtotal']) ?><br>
Descuento: <?= fmt($head['descuento']) ?><br>
Impuesto: <?= fmt($head['impuesto']) ?><br>
<span class="total">TOTAL: <?= fmt($head['total_venta']) ?></span><br><br>
Método de pago: <?= htmlspecialchars($head['metodo_pago']) ?><br>
<?= htmlspecialchars($head['observacion']) ?>
</div>

</body>
</html>
