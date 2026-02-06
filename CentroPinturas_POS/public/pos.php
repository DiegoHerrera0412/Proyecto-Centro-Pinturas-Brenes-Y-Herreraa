<?php
require_once __DIR__ . '/../app/includes/db.php';
require_once __DIR__ . '/../app/includes/auth.php';
require_once __DIR__ . '/../app/includes/ui.php';

require_role(['admin','cajero','vendedor']);

$me = $_SESSION['user'];
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Punto de Venta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">
  
</head>
<body class="bg">
  <div class="shell">
    <aside class="sidebar">
      <div class="sidebar-top">
        <div class="brand mini">
          <div class="logo-dot"></div>
          <div>
            <div class="brand-title">Centro de Pinturas</div>
            <div class="brand-sub">POS</div>
          </div>
        </div>

        <div class="userbox">
          <div class="avatar"><?= h(mb_strtoupper(mb_substr($me['nombre'],0,1))) ?></div>
          <div class="user-meta">
            <div class="user-name"><?= h($me['nombre']) ?></div>
            <div class="user-role"><?= h($me['rol']) ?></div>
          </div>
        </div>
      </div>

      <nav class="nav">
        <a class="nav-item active" href="/pos.php">Punto de venta</a>
        <div class="nav-muted">Solo módulo POS</div>
      </nav>

      <div class="sidebar-bottom">
        <a class="btn ghost w100" href="/logout.php">Cerrar sesión</a>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div>
          <div class="kicker">Facturación rápida</div>
          <div class="title">Punto de Venta</div>
        </div>

        <div class="topbar-actions">
          <div class="chip" id="todayChip"></div>
        </div>
      </header>

      <div class="grid">
        <section class="card">
          <div class="card-h">
            <div>
              <div class="card-title">Productos</div>
              <div class="card-sub">Buscá por nombre o SKU y agregá al carrito.</div>
            </div>
          </div>

          <div class="search-row">
            <input id="qProduct" class="input" placeholder="Ej: Látex blanca, THN-1L, rodillo..." autocomplete="off">
            <button class="btn" id="btnScan">Buscar</button>
          </div>

          <div class="list" id="productsList"></div>
        </section>

        <section class="card">
          <div class="card-h">
            <div>
              <div class="card-title">Venta</div>
              <div class="card-sub">Cliente + carrito + cobro.</div>
            </div>
          </div>

          <div class="customer-box">
            <div class="row">
              <input id="qClient" class="input" placeholder="Buscar cliente (nombre / teléfono / correo)" autocomplete="off">
              <button class="btn" id="btnFindClient">Buscar</button>
            </div>
            <div class="row">
              <div class="select" id="clientSelect">
                <div class="select-value" id="clientValue">Cliente contado</div>
                <div class="select-menu" id="clientMenu"></div>
              </div>
              <button class="btn ghost" id="btnNewClient">Nuevo</button>
            </div>
          </div>

          <div class="cart" id="cart"></div>

          <div class="totals">
            <div class="tot-row"><span>Subtotal</span><span id="tSubtotal">₡0.00</span></div>
            <div class="tot-row"><span>Descuento</span><span><input id="discount" class="input mini" value="0" inputmode="decimal"></span></div>
            <div class="tot-row"><span>Impuesto</span><span><input id="tax" class="input mini" value="0" inputmode="decimal"></span></div>
            <div class="tot-row strong"><span>Total</span><span id="tTotal">₡0.00</span></div>
          </div>

          <div class="pay">
            <div class="row">
              <select id="payMethod" class="input">
                <option value="Efectivo">Efectivo</option>
                <option value="SINPE">SINPE</option>
                <option value="Tarjeta">Tarjeta</option>
              </select>
              <input id="note" class="input" placeholder="Observación (opcional)">
            </div>
            <button class="btn primary w100" id="btnFinish">Finalizar venta</button>
            <div class="hint" id="finishHint"></div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <dialog id="dlgClient" class="dlg">
    <form method="dialog" class="dlg-card" id="clientForm">
      <div class="dlg-h">
        <div class="dlg-title">Nuevo cliente</div>
        <button class="x" value="cancel" aria-label="Cerrar">✕</button>
      </div>

      <div class="dlg-grid">
        <div>
          <label class="label">Nombre</label>
          <input class="input" name="nombre" required>
        </div>
        <div>
          <label class="label">Apellido</label>
          <input class="input" name="apellido">
        </div>
        <div>
          <label class="label">Teléfono</label>
          <input class="input" name="telefono">
        </div>
        <div>
          <label class="label">Correo</label>
          <input class="input" name="correo" type="email">
        </div>
        <div class="span2">
          <label class="label">Dirección</label>
          <input class="input" name="direccion">
        </div>
      </div>

      <div class="dlg-actions">
        <button class="btn ghost" value="cancel">Cancelar</button>
        <button class="btn primary" id="btnSaveClient" value="default">Guardar</button>
      </div>
      <div class="hint" id="clientHint"></div>
    </form>
  </dialog>

  <script src="assets/js/pos.js"></script>


</body>
</html>
