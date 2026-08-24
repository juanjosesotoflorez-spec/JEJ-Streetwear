<nav>
    <a href="../index.php">Inicio</a>
    <?php if(isset($_SESSION['rol'])): ?>
        <?php if($_SESSION['rol']=='admin'): ?>
            <a href="../admin/dashboard.php">Admin</a>
        <?php else: ?>
            <a href="../user/dashboard.php">Usuario</a>
        <?php endif; ?>
    <?php endif; ?>
    <a href="../auth/logout.php">Salir</a>
</nav>

