<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
include('../config/db.php');

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: usuario.php'); exit; }

$stmt = $conn->prepare('SELECT id, nombre, email, rol, creado FROM usuarios WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();
if (!$user) { header('Location: usuario.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rol = trim($_POST['rol'] ?? 'user');

    if ($nombre === '') $errors[] = 'Nombre requerido.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido.';
    if (!in_array($rol, ['admin','user'])) $errors[] = 'Rol inválido.';

    if (empty($errors)) {
        $check = $conn->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1');
        $check->bind_param('si', $email, $id);
        $check->execute();
        $cr = $check->get_result();
        if ($cr && $cr->num_rows > 0) $errors[] = 'El email ya está en uso por otro usuario.';
        $check->close();
    }

    if (empty($errors)) {
        $uStmt = $conn->prepare('UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?');
        if ($uStmt) {
            $uStmt->bind_param('sssi', $nombre, $email, $rol, $id);
            if ($uStmt->execute()) {
                $uStmt->close();
                header('Location: usuario.php?updated=1');
                exit;
            } else {
                $errors[] = 'No se pudo actualizar: ' . $uStmt->error;
            }
        } else {
            $errors[] = 'Error de base de datos: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar Usuario - JE&J Admin</title>
    <link rel="stylesheet" href="css/usuario.css">
    <style>
        /* ====== ESTILO GENERAL ====== */
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #000000, #1a0000);
            color: #fff;
        }

        .main-header {
            background: #111;
            color: #ff0000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            box-shadow: 0 4px 15px rgba(255, 0, 0, 0.3);
        }

        .main-header .logo {
            font-size: 1.3rem;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .main-header nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 1rem;
            transition: 0.3s;
        }

        .main-header nav a:hover, 
        .main-header nav a.active {
            color: #ff0000;
            border-bottom: 2px solid #ff0000;
        }

        /* ====== FORMULARIO ====== */
        .form-container {
            background: #111;
            border: 1px solid #ff0000;
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.4);
            animation: fadeIn 0.6s ease-in-out;
        }

        h2 {
            text-align: center;
            color: #ff0000;
            margin-bottom: 1.5rem;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            font-weight: 600;
            color: #ff6666;
            margin-bottom: 0.4rem;
        }

        input[type="text"], input[type="email"], select {
            width: 100%;
            padding: 0.7rem;
            border-radius: 6px;
            border: 1px solid #ff0000;
            background: #222;
            color: #fff;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #ff4444;
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
        }

        .errors {
            background: #330000;
            border: 1px solid #ff3333;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            color: #ffcccc;
        }

        /* ====== BOTONES ====== */
        .btn {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s ease;
            cursor: pointer;
        }

        .btn-primary {
            background: #ff0000;
            color: #fff;
            border: none;
        }

        .btn-primary:hover {
            background: #cc0000;
            transform: scale(1.05);
        }

        .btn-secondary {
            background: #333;
            color: #fff;
            border: 1px solid #ff0000;
            margin-left: 0.5rem;
        }

        .btn-secondary:hover {
            background: #550000;
        }

        .main-footer {
            text-align: center;
            padding: 1rem;
            background: #111;
            color: #ff5555;
            font-size: 0.9rem;
            margin-top: 3rem;
            border-top: 1px solid #ff0000;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo">👥 JE&J Admin</div>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="productos.php">👕 Productos</a>
            <a href="usuarios.php" class="active">👥 Usuarios</a>
            <a href="../auth/logout.php">🚪 Salir</a>
        </nav>
    </header>

    <main>
        <section class="form-container">
            <h2>Editar Usuario #<?= $user['id'] ?></h2>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input id="nombre" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? $user['nombre']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol">
                        <option value="user" <?= ((isset($_POST['rol'])?$_POST['rol']:$user['rol'])==='user')?'selected':'' ?>>Usuario</option>
                        <option value="admin" <?= ((isset($_POST['rol'])?$_POST['rol']:$user['rol'])==='admin')?'selected':'' ?>>Administrador</option>
                    </select>
                </div>

                <div class="form-group" style="text-align:center;">
                    <button class="btn btn-primary" type="submit">💾 Guardar Cambios</button>
                    <a class="btn btn-secondary" href="usuario.php">❌ Cancelar</a>
                </div>
            </form>
        </section>
    </main>

    <footer class="main-footer">
        <p>© <?= date('Y') ?> JE&J Streetwear | Panel de Administración</p>
    </footer>
</body>
</html>
