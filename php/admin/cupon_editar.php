<?php
session_start(); if(!isset($_SESSION['rol']) || $_SESSION['rol']!=='admin'){ header('Location: ../auth/login.php'); exit; }
include('../config/db.php');
$id = intval($_GET['id'] ?? 0); if($id<=0){ header('Location: marketing.php'); exit; }
$stmt = $conn->prepare('SELECT * FROM cupones WHERE id = ? LIMIT 1'); $stmt->bind_param('i',$id); $stmt->execute(); $res = $stmt->get_result(); $cupon = $res->fetch_assoc(); $stmt->close(); if(!$cupon){ header('Location: marketing.php'); exit; }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $codigo = trim($_POST['codigo'] ?? ''); $descuento = floatval($_POST['descuento'] ?? 0); $fecha = $_POST['fecha_expiracion'] ?? null; $activo = isset($_POST['activo'])?1:0;
    if ($codigo === '') $errors[] = 'Código requerido.'; if ($descuento <= 0) $errors[] = 'Descuento inválido.';
    if(empty($errors)){
        $u = $conn->prepare('UPDATE cupones SET codigo=?, descuento=?, fecha_expiracion=?, activo=? WHERE id=?'); $u->bind_param('sdssi',$codigo,$descuento,$fecha,$activo,$id);
        if($u->execute()){ header('Location: marketing.php?updated=1'); exit; } else $errors[] = 'Error: '.$u->error;
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Editar Cupón</title><link rel="stylesheet" href="css/marketing.css"></head><body>
<main class="container"><h2>Editar Cupón #<?= $cupon['id'] ?></h2>
<?php if(!empty($errors)): ?><div class="errors"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
<form method="post">
<label>Código <input name="codigo" value="<?= htmlspecialchars($_POST['codigo'] ?? $cupon['codigo']) ?>"></label><br>
<label>Descuento (%) <input name="descuento" type="number" step="0.01" value="<?= htmlspecialchars($_POST['descuento'] ?? $cupon['descuento']) ?>"></label><br>
<label>Fecha expiración <input name="fecha_expiracion" type="date" value="<?= htmlspecialchars($_POST['fecha_expiracion'] ?? $cupon['fecha_expiracion']) ?>"></label><br>
<label><input type="checkbox" name="activo" <?= ((isset($_POST['activo'])?1:$cupon['activo']) ? 'checked':'') ?>> Activo</label><br>
<button type="submit">Guardar</button> <a href="marketing.php">Cancelar</a>
</form></main></body></html>
