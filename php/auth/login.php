<?php
session_start();

// Si ya existe sesión, redirigir según rol
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
        header("Location: ../admin/dashboard.php");
    } else {
        header("Location: ../user/dashboard.php");
    }
    exit;
}

include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();

        // 🔹 Comparación directa SIN encriptación
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['rol'] = $user['rol'];

            if ($user['rol'] == 'admin') {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit;
        } else {
            $error_message = "Contraseña incorrecta";
        }
    } else {
        $error_message = "Correo electrónico no registrado";
    }

    if (isset($error_message)) {
        echo "<div class='error-message'>$error_message</div>";
    }
}
?>

<link rel="stylesheet" href="auth.css">

<div class="auth-container">
    <div class="logo">JE<span>&</span>J Streetwear</div>
    <h1>Iniciar Sesión</h1>
    <p>Accede a tu cuenta para continuar</p>

    <form action="login.php" method="POST">
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

    <a href="register.php">¿No tienes cuenta? Regístrate</a>
    <a class="btn-home" href="../../index.php" role="button" aria-label="Volver al inicio">← Volver al inicio</a>
</div>
