<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(post_string('correo'));
    $password = (string) ($_POST['password'] ?? '');
    if (!valid_email($email) || $password === '') {
        $error = 'Revisa el correo y la contraseña.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, nombre, correo, password_hash, rol FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int) $user['id'], 'nombre' => $user['nombre'], 'correo' => $user['correo'], 'rol' => $user['rol']];
            flash('success', 'Sesión iniciada correctamente.');
            redirect($user['rol'] === 'admin' ? 'admin/dashboard.php' : 'auth/cuenta.php');
        }
        $error = 'Correo o contraseña incorrectos.';
    }
}
$pageTitle = 'Iniciar sesión';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-5">
  <h1 class="mb-4">INICIAR <span class="text-red">SESIÓN</span></h1>
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <form method="post" class="card app-card p-4" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" required autocomplete="email" value="<?= e($_POST['correo'] ?? '') ?>"></div>
    <div class="mb-4"><label class="form-label" for="password">Contraseña</label><input class="form-control" type="password" id="password" name="password" required autocomplete="current-password"></div>
    <button class="btn btn-red" type="submit">ENTRAR</button>
  </form>
  <p class="mt-3 muted">¿Aún no tienes cuenta? <a href="<?= e(app_url('auth/registro.php')) ?>">Regístrate aquí</a>.</p>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
