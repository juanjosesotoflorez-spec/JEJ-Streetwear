<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('nombre');
    $email = strtolower(post_string('correo'));
    $phone = post_string('telefono');
    $password = (string) ($_POST['password'] ?? '');
    $confirmation = (string) ($_POST['confirmar_password'] ?? '');
    if (mb_strlen($name) < 3 || mb_strlen($name) > 120) $errors[] = 'Escribe tu nombre completo (3 a 120 caracteres).';
    if (!valid_email($email)) $errors[] = 'Escribe un correo electrónico válido.';
    if (strlen($phone) > 30) $errors[] = 'El teléfono es demasiado largo.';
    if (strlen($password) < 10) $errors[] = 'La contraseña debe tener al menos 10 caracteres.';
    if ($password !== $confirmation) $errors[] = 'Las contraseñas no coinciden.';
    if (empty($_POST['acepta'])) $errors[] = 'Debes aceptar las condiciones para crear una cuenta.';
    if (!$errors) {
        $stmt = $mysqli->prepare('SELECT id FROM usuarios WHERE correo = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'Ese correo ya tiene una cuenta.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare('INSERT INTO usuarios (nombre, correo, telefono, password_hash) VALUES (?, ?, NULLIF(?, \'\'), ?)');
            $stmt->bind_param('ssss', $name, $email, $phone, $hash);
            $stmt->execute();
            flash('success', 'Tu cuenta quedó creada. Ya puedes iniciar sesión.');
            redirect('auth/login.php');
        }
    }
}
$pageTitle = 'Crear cuenta';
require __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center"><div class="col-lg-7">
  <h1 class="mb-4">CREAR <span class="text-red">CUENTA</span></h1>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
  <form method="post" class="card app-card p-4" data-validate novalidate>
    <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label" for="nombre">Nombre completo</label><input class="form-control" id="nombre" name="nombre" required minlength="3" maxlength="120" value="<?= e($_POST['nombre'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label" for="correo">Correo electrónico</label><input class="form-control" type="email" id="correo" name="correo" required maxlength="190" value="<?= e($_POST['correo'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label" for="telefono">Teléfono (opcional)</label><input class="form-control" id="telefono" name="telefono" maxlength="30" value="<?= e($_POST['telefono'] ?? '') ?>"></div>
    <div class="mb-3"><label class="form-label" for="password">Contraseña (mínimo 10 caracteres)</label><input class="form-control" type="password" id="password" name="password" required minlength="10" autocomplete="new-password"></div>
    <div class="mb-3"><label class="form-label" for="confirmar_password">Confirmar contraseña</label><input class="form-control" type="password" id="confirmar_password" name="confirmar_password" required minlength="10" autocomplete="new-password"></div>
    <div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="acepta" name="acepta" required><label class="form-check-label" for="acepta">Acepto las condiciones de uso de esta tienda demostrativa.</label></div>
    <button class="btn btn-red" type="submit">CREAR CUENTA</button>
  </form>
  <p class="mt-3 muted">¿Ya tienes cuenta? <a href="<?= e(app_url('auth/login.php')) ?>">Inicia sesión</a>.</p>
</div></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
