<?php
include("../config/db.php");

$errors = []; // Aquí almacenaremos los errores

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rol = "user";

    // Verifica si el correo ya está registrado
    $sql_check_email = "SELECT * FROM usuarios WHERE email=?";
    $stmt_check = $conn->prepare($sql_check_email);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $errors[] = "Este correo electrónico ya está registrado.";
    }

    // Si no hay errores, inserta el usuario
    if (empty($errors)) {
        $sql = "INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

        if ($stmt->execute()) {
            header("Location: login.php?success=1");
            exit;
        } else {
            $errors[] = "Error al registrar el usuario. Inténtalo más tarde.";
        }
    }
}
?>

<link rel="stylesheet" href="auth.css">

<div class="auth-container">
    <div class="logo">JE<span>&</span>J Streetwear</div>
    <h1>Registro</h1>
    <p>Crea tu cuenta y únete a nuestra comunidad</p>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="nombre" placeholder="Nombre completo" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Registrarme</button>
    </form>

    <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
</div>
