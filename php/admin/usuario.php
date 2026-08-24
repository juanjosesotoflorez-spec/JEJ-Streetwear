<?php 
session_start();
if ($_SESSION['rol'] != 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
include("../config/db.php");

$usuarios = $conn->query("SELECT * FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="css/usuario.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo">👥 Admin JE&J Streetwear</div>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="productos.php">👕 Productos</a>
            <a href="pedidos.php">📦 Pedidos</a>
            <a href="usuarios.php" class="active">👥 Usuarios</a>
            <a href="../auth/logout.php">🚪 Salir</a>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container">
        <h2>👥 Gestión de Usuarios</h2>
        <a class="btn-back" href="dashboard.php">⬅ Volver</a>
        <a class="btn-back" href="informe_usuarios.php"> 👨informe usuarios</a>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Creado</th>
                        <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($u=$usuarios->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= $u['nombre'] ?></td>
                    <td><?= $u['email'] ?></td>
                    <td><span class="rol <?= $u['rol'] ?>"><?= ucfirst($u['rol']) ?></span></td>
                    <td><?= $u['creado'] ?></td>
                        <td>
                            <a class="btn editar" href="usuario_editar.php?id=<?= $u['id'] ?>">✏️ Editar</a>
                            <button class="btn eliminar-usuario" data-id="<?= $u['id'] ?>">🗑️ Eliminar</button>
                        </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <div id="toast" style="position:fixed; right:20px; bottom:20px; z-index:9999; display:none;"></div>

    <script>
    (function(){
        function showToast(msg, type='success'){
            const el = document.getElementById('toast');
            el.innerHTML = '<div style="background:'+(type==='success'? '#2d8f2d':'#c0392b')+'; color:#fff; padding:12px 18px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.4);">'+msg+'</div>';
            el.style.display = 'block';
            setTimeout(()=> el.style.display='none', 3500);
        }

        document.querySelectorAll('.eliminar-usuario').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const id = btn.dataset.id;
                if (!confirm('¿Eliminar usuario ID '+id+'?')) return;
                fetch('usuario_borrar.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(data=>{
                    if (data && data.success){
                        const row = btn.closest('tr');
                        if (row) row.remove();
                        showToast('Usuario eliminado', 'success');
                    } else {
                        showToast('Error: ' + (data.error || 'No se pudo eliminar'), 'error');
                    }
                }).catch(()=> showToast('Error de red', 'error'));
            });
        });
    })();
    </script>

    <!-- Footer -->
    <footer class="main-footer">
        <p>© 2025 JE&J Streetwear | Panel de Administración</p>
    </footer>
</body>
</html>

