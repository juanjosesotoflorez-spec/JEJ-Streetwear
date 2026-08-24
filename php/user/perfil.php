<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$id = $_SESSION['user_id'];
// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conn->real_escape_string($_POST['nombre'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $direccion = $conn->real_escape_string($_POST['direccion'] ?? '');

    $conn->query("UPDATE usuarios SET nombre='$nombre', email='$email', direccion='$direccion' WHERE id=$id");
    // recargar los datos
    header("Location: perfil.php");
    exit;
}

$u = $conn->query("SELECT * FROM usuarios WHERE id=$id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/perfil.css">
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="productos.php">Productos</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <section class="perfil-container">
            <h2>Mi Perfil</h2>
            <p class="bienvenida">Hola <strong><?= $u['nombre'] ?></strong>, aquí puedes actualizar tus datos.</p>
            <form method="post">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?= $u['nombre'] ?>">

                <label>Email</label>
                <input type="email" name="email" value="<?= $u['email'] ?>">

                <label>Dirección</label>
                <textarea name="direccion"><?= $u['direccion'] ?></textarea>

                <button type="submit">Actualizar</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> JE&J Streetwear - Todos los derechos reservados</p>
    </footer>
</body>
</html>


