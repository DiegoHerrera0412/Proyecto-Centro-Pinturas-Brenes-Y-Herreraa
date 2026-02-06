<?php
require_once __DIR__ . '/../app/includes/db.php';
require_once __DIR__ . '/../app/includes/auth.php';
require_once __DIR__ . '/../app/includes/ui.php';

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $correo = trim($_POST['correo'] ?? '');
  $pass = (string)($_POST['pass'] ?? '');
  if ($correo !== '' && $pass !== '') {
    $st = $pdo->prepare("SELECT u.id_usuario, u.nombre, u.correo, u.pass_hash, r.nombre AS rol
                         FROM usuario u
                         JOIN rol r ON r.id_rol = u.id_rol
                         WHERE u.correo = ? AND u.activo = 1");
    $st->execute([$correo]);
    $u = $st->fetch();
    if ($u && hash_equals($u['pass_hash'], sha_pass($pass))) {
      $_SESSION['user'] = [
        'id_usuario' => (int)$u['id_usuario'],
        'nombre' => $u['nombre'],
        'correo' => $u['correo'],
        'rol' => $u['rol'],
      ];
      header('Location: pos.php');
      exit;
    }
  }
  $err = 'Credenciales inválidas';
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingresar | Punto de Venta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg">
  <div class="auth-shell">
    <div class="auth-card">
      <div class="brand">
        <div class="logo-dot"></div>
        <div>
          <div class="brand-title">Centro de Pinturas</div>
          <div class="brand-sub">Brenes y Herrera — POS</div>
        </div>
      </div>

      <h1 class="h1">Iniciar sesión</h1>
      <p class="muted">Accedé para facturar y registrar ventas.</p>

      <?php if ($err): ?>
        <div class="alert"><?= h($err) ?></div>
      <?php endif; ?>

      <form method="post" class="form">
        <label class="label">Correo</label>
        <input class="input" name="correo" type="email" autocomplete="username" required>

        <label class="label">Contraseña</label>
        <input class="input" name="pass" type="password" autocomplete="current-password" required>

        <button class="btn primary w100" type="submit">Entrar</button>
      </form>

      <div class="foot">
        <span class="pill">demo</span>
        <span class="muted">admin@demo.com / Admin123*</span>
      </div>
    </div>
  </div>
</body>
</html>
