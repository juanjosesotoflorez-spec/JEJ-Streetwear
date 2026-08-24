<?php
session_start();
if ($_SESSION['rol'] != 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuraciones Generales</title>
    <link rel="stylesheet" href="css/configuraciones.css">
</head>
<body>
    <!-- ===== HEADER ===== -->
    <header class="main-header">
        <div class="logo">JE&J <span>Admin</span></div>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="productos.php">📦 Productos</a>
            <a href="pedidos.php">🛒 Pedidos</a>
            <a href="usuarios.php">👤 Usuarios</a>
            <a href="logout.php">🚪 Salir</a>
        </nav>
    </header>

    <!-- ===== CONTENIDO ===== -->
    <main class="container">
        <h2>⚙️ Configuraciones Generales</h2>
        <div class="actions">
            <a href="dashboard.php" class="btn-back">⬅ Volver</a>
        </div>

        <form method="post" class="config-form">
            <label>
                Nombre Tienda:
                <input type="text" name="nombre" value="JE&J Streetwear">
            </label>

            <label>
                Email de Contacto:
                <input type="email" name="email" value="contacto@jej.com">
            </label>

            <label>
                Método de pago principal:
                <select name="pago">
                    <option>Tarjeta</option>
                    <option>Paypal</option>
                    <option>Efectivo</option>
                </select>
            </label>

            <label>
                Método de envío:
                <select name="envio">
                    <option>Envío estándar</option>
                    <option>Envío exprés</option>
                </select>
            </label>

            <button type="submit" class="btn-save">💾 Guardar</button>
        </form>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="main-footer">
        <p>&copy; <?= date("Y") ?> JE&J Streetwear - Panel de Administración</p>
    </footer>
</body>
</html>
