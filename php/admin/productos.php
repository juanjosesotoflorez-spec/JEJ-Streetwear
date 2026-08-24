<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

// Mostrar lista de productos
$productos = $conn->query("SELECT * FROM productos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Productos - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/productos.css">
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Admin</span></div>
        <nav>
            <a href="resumen_admin.php">Resumen</a>
            <a href="productos.php" class="active">Productos</a>
            <a href="pedidos.php">Pedidos</a>
            <a href="usuarios.php">Usuarios</a>
            <a href="../auth/logout.php">Salir</a>
        </nav>
    </header>

    <main>
        <section class="productos-container">
            <div class="top-bar">
                <h2>🛍️ Gestión de Productos</h2>
                <div class="acciones">
                    <a href="dashboard.php" class="btn volver">⬅ Volver</a>
                    <a href="producto_nuevo.php" class="btn nuevo">➕ Nuevo Producto</a>
                    <a href="informe_productos.php" class="btn nuevo"> 💾 informe productos</a>
                </div>
            </div>

            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th></th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $productos->fetch_assoc()): ?>
                    <?php
                        // Calcular miniatura
                        $img = '/imagenes/logo2.png';
                        if (!empty($p['imagen'])) {
                            if (preg_match('#^https?://#i', $p['imagen'])) $img = $p['imagen'];
                            elseif (strpos($p['imagen'], 'imagenes/') !== false) $img = '/' . ltrim($p['imagen'], '/');
                            else $img = '/imagenes/' . ltrim($p['imagen'], '/');
                        }
                    ?>
                    <tr data-id="<?= $p['id'] ?>">
                        <td><?= $p['id'] ?></td>
                        <td style="width:56px;"><img src="<?= htmlspecialchars($img) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px;"></td>
                        <td><?= $p['nombre'] ?></td>
                        <td>$<?= number_format($p['precio'], 2) ?></td>
                        <td><?= $p['stock'] ?></td>
                        <td><?= $p['categoria'] ?></td>
                        <td>
                            <a href="producto_editar.php?id=<?= $p['id'] ?>" class="btn editar">✏️ Editar</a>
                            <button class="btn eliminar-btn" data-id="<?= $p['id'] ?>">🗑️ Eliminar</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </main>

    <!-- Toast container -->
    <div id="toast" style="position:fixed; right:20px; bottom:20px; z-index:9999; display:none;"></div>

    <script>
    // Mostrar toast basado en query params
    (function(){
        function showToast(msg, type='success'){
            const el = document.getElementById('toast');
            el.innerHTML = '<div style="background:'+(type==='success'? '#2d8f2d':'#c0392b')+'; color:#fff; padding:12px 18px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.4);">'+msg+'</div>';
            el.style.display = 'block';
            setTimeout(()=> el.style.display='none', 3500);
        }

        const params = new URLSearchParams(window.location.search);
        if (params.get('added') === '1') showToast('Producto agregado correctamente', 'success');
        if (params.get('updated') === '1') showToast('Producto actualizado', 'success');
        if (params.get('deleted') === '1') showToast('Producto eliminado', 'success');

        // Eliminar via AJAX
        document.querySelectorAll('.eliminar-btn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const id = btn.dataset.id;
                if (!confirm('¿Eliminar producto #' + id + '? Esta acción no se puede deshacer.')) return;
                fetch('producto_borrar.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(data=>{
                    if (data && data.success){
                        const row = document.querySelector('tr[data-id="'+id+'"]');
                        if (row) row.remove();
                        showToast('Producto eliminado', 'success');
                    } else {
                        showToast('Error al eliminar: ' + (data.error||'error'), 'error');
                    }
                }).catch(()=> showToast('Error de red', 'error'));
            });
        });
    })();
    </script>

    <footer>
        <p>&copy; <?= date("Y") ?> JE&J Streetwear | Panel de Administración</p>
    </footer>
</body>
</html>

