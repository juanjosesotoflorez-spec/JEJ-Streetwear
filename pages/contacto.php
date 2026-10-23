<?php
require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('nombre');
    $email = strtolower(post_string('correo'));
    $subject = post_string('asunto');
    $message = post_string('mensaje');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Escribe un nombre válido.';
    if (!valid_email($email)) $errors[] = 'Escribe un correo electrónico válido.';
    if (mb_strlen($subject) < 3 || mb_strlen($subject) > 160) $errors[] = 'El asunto debe tener entre 3 y 160 caracteres.';
    if (mb_strlen($message) < 10 || mb_strlen($message) > 5000) $errors[] = 'El mensaje debe tener entre 10 y 5000 caracteres.';
    if (!$errors) {
        $stmt = $mysqli->prepare('INSERT INTO mensajes_contacto (nombre, correo, asunto, mensaje) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $subject, $message);
        $stmt->execute();
        flash('success', 'Tu mensaje quedó guardado. Gracias por escribirnos.');
        redirect('contacto.php');
    }
}
$pageTitle = 'Contacto';
require __DIR__ . '/../includes/header.php';
?>
<div class="row g-5">
  <div class="col-lg-7"><p class="eyebrow">HABLEMOS</p><h1>CONTÁCTANOS</h1><p class="muted mb-4">Cuéntanos cómo podemos ayudarte. Este formulario guarda el mensaje en la base de datos del proyecto.</p>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" class="card app-card p-4" data-validate novalidate><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label" for="nombre">Nombre</label><input class="form-control" id="nombre" name="nombre" required minlength="2" maxlength="120" value="<?= e($_POST['nombre'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label" for="correo">Correo</label><input class="form-control" id="correo" name="correo" type="email" required maxlength="190" value="<?= e($_POST['correo'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label" for="asunto">Asunto</label><input class="form-control" id="asunto" name="asunto" required minlength="3" maxlength="160" value="<?= e($_POST['asunto'] ?? '') ?>"></div>
      <div class="mb-3"><label class="form-label" for="mensaje">Mensaje</label><textarea class="form-control" id="mensaje" name="mensaje" rows="5" required minlength="10" maxlength="5000"><?= e($_POST['mensaje'] ?? '') ?></textarea></div>
      <button class="btn btn-red" type="submit">ENVIAR MENSAJE</button>
    </form>
  </div>
  <div class="col-lg-5"><div class="card app-card p-4 mb-4"><h2 class="h3">Información de ejemplo</h2><p class="muted">Correo: hola@jej-streetwear.example</p><p class="muted">Atención: lunes a viernes, 9:00 a. m. – 5:00 p. m.</p><small class="muted">Los datos anteriores son demostrativos y no representan un canal de atención real.</small></div>
    <div class="card app-card p-4"><h2 class="h3">Preguntas frecuentes</h2><h3 class="h5 mt-3">¿Los pagos son reales?</h3><p class="muted">No. Solo puedes escoger resultados simulados; no se recopilan datos bancarios.</p><h3 class="h5">¿Cómo consulto una talla?</h3><p class="muted mb-0">Entra a los detalles del producto para consultar existencias por talla.</p></div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
