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
                     JOIN  usuario u ON u.id_usuario = v.id_usuario
                     WHERE f.id_factura = ?");
$st->execute([$id]);
$head = $st->fetch();
if (!$head) { http_response_code(404); echo "No existe"; exit; }

$dt = $pdo->prepare("SELECT d.cantidad, d.precio_unitario, p.nombre, p.sku, d.total_linea
                     FROM dbo.detalle_venta d
                     JOIN dbo.producto p ON p.id_producto = d.id_producto
                     WHERE d.id_venta = ?
                     ORDER BY d.id_detalle ASC");
$dt->execute([(int)$head['id_venta']]);
$items = $dt->fetchAll();

$cliente = trim(($head['c_nombre'] ?? 'Cliente') . ' ' . ($head['c_apellido'] ?? ''));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Factura <?= h($head['consecutivo']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">

</head>
<body class="bg invoice">
  <div class="invoice-shell">
    <div class="invoice-card">
      <div class="inv-top">
        <div class="brand mini">
          <div class="logo-dot"></div>
          <div>
            <div class="brand-title">Centro de Pinturas</div>
            <div class="brand-sub">Brenes y Herrera</div>
          </div>
        </div>

        <div class="inv-meta">
          <div class="inv-title">Factura</div>
          <div class="inv-no"><?= h($head['consecutivo']) ?></div>
          <div class="muted"><?= h((new DateTime($head['fecha_emision']))->format('Y-m-d H:i')) ?></div>
        </div>
      </div>

      <div class="inv-grid">
        <div class="inv-box">
          <div class="k">Cliente</div>
          <div class="v"><?= h($cliente ?: 'Cliente contado') ?></div>
          <div class="muted"><?= h($head['telefono'] ?? '') ?></div>
          <div class="muted"><?= h($head['correo'] ?? '') ?></div>
          <div class="muted"><?= h($head['direccion'] ?? '') ?></div>
        </div>

        <div class="inv-box">
          <div class="k">Atendió</div>
          <div class="v"><?= h($head['u_nombre']) ?></div>
          <div class="k mt">Pago</div>
          <div class="v"><?= h($head['metodo_pago']) ?></div>
        </div>
      </div>

      <div class="inv-table">
        <div class="inv-row head">
          <div>Producto</div>
          <div class="r">Cant.</div>
          <div class="r">Precio</div>
          <div class="r">Total</div>
        </div>
        <?php foreach ($items as $it): ?>
          <div class="inv-row">
            <div>
              <div class="pname"><?= h($it['nombre']) ?></div>
              <div class="psku muted"><?= h($it['sku'] ?? '') ?></div>
            </div>
            <div class="r"><?= (int)$it['cantidad'] ?></div>
            <div class="r"><?= money_crc($it['precio_unitario']) ?></div>
            <div class="r"><?= money_crc($it['total_linea']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="inv-totals">
        <div class="tot-row"><span>Subtotal</span><span><?= money_crc($head['subtotal']) ?></span></div>
        <div class="tot-row"><span>Descuento</span><span><?= money_crc($head['descuento']) ?></span></div>
        <div class="tot-row"><span>Impuesto</span><span><?= money_crc($head['impuesto']) ?></span></div>
        <div class="tot-row strong"><span>Total</span><span><?= money_crc($head['total_venta']) ?></span></div>
      </div>

      <?php if (!empty($head['observacion'])): ?>
        <div class="inv-note"><span class="k">Obs:</span> <?= h($head['observacion']) ?></div>
      <?php endif; ?>

      <div class="inv-actions no-print">
        <a class="btn" href="/pos.php">Volver</a>
        <button class="btn primary" onclick="window.print()">Imprimir</button>
      </div>
    </div>
  </div>
</body>
</html>
