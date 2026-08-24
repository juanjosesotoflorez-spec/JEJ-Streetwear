<?php
session_start();
if ($_SESSION['rol'] != 'admin') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
include("../config/db.php");

$cupones = $conn->query("SELECT * FROM cupones ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Marketing y Promociones</title>
    <link rel="stylesheet" href="css/marketing.css">
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <div class="logo">📢 Marketing JE&J</div>
        <nav>
            <a href="dashboard.php">🏠 Dashboard</a>
            <a href="productos.php">👕 Productos</a>
            <a href="pedidos.php">📦 Pedidos</a>
            <a href="usuarios.php">👥 Usuarios</a>
            <a href="marketing.php" class="active">📢 Marketing</a>
            <a href="../auth/logout.php">🚪 Salir</a>
        </nav>
    </header>

    <!-- Contenido principal -->
    <main class="container">
        <h2>📢 Marketing y Promociones</h2>
        <div class="actions">
            <a class="btn-back" href="dashboard.php">⬅ Volver</a>
            <a class="btn-new" href="cupon_nuevo.php">➕ Nuevo Cupón</a>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Código</th>
                    <th>Descuento</th>
                    <th>Expira</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c=$cupones->fetch_assoc()): ?>
                <tr data-id="<?= $c['id'] ?>">
                    <td><?= $c['id'] ?></td>
                    <td><?= htmlspecialchars($c['codigo']) ?></td>
                    <td><?= $c['descuento'] ?>%</td>
                    <td><?= $c['fecha_expiracion'] ?></td>
                    <td>
                        <span class="status <?= $c['activo'] ? 'activo':'inactivo' ?>">
                            <?= $c['activo'] ? 'Sí':'No' ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn editar" href="cupon_editar.php?id=<?= $c['id'] ?>">✏️ Editar</a>
                        <button class="btn toggle" data-id="<?= $c['id'] ?>">Activar/Desactivar</button>
                        <button class="btn eliminar" data-id="<?= $c['id'] ?>">🗑️ Eliminar</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <div id="toast" style="position:fixed; right:20px; bottom:20px; z-index:9999; display:none;"></div>
    <script>
    (function(){
        function showToast(msg,type='success'){const el=document.getElementById('toast');el.innerHTML='<div style="background:'+(type==='success'?'#2d8f2d':'#c0392b')+'; color:#fff; padding:12px 18px; border-radius:8px; box-shadow:0 6px 18px rgba(0,0,0,0.4);">'+msg+'</div>';el.style.display='block';setTimeout(()=>el.style.display='none',3500);}

        document.querySelectorAll('.eliminar').forEach(btn=>btn.addEventListener('click',()=>{
            const id=btn.dataset.id; if(!confirm('Eliminar cupón #'+id+'?')) return; fetch('cupon_borrar.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(data=>{ if(data && data.success){ const row=document.querySelector('tr[data-id="'+id+'"]'); if(row) row.remove(); showToast('Cupón eliminado'); }else showToast('Error: '+(data.error||'')); }).catch(()=>showToast('Error de red','error'));
        }));

        document.querySelectorAll('.toggle').forEach(btn=>btn.addEventListener('click',()=>{
            const id=btn.dataset.id; fetch('cupon_toggle.php?id='+encodeURIComponent(id)).then(r=>r.json()).then(data=>{ if(data && data.ok){ const row=document.querySelector('tr[data-id="'+id+'"]'); if(row){ const span=row.querySelector('.status'); if(span) span.textContent = data.activo ? 'Sí' : 'No'; span.className = 'status ' + (data.activo ? 'activo':'inactivo'); } showToast('Estado actualizado'); } else showToast('Error: '+(data.error||''),'error'); }).catch(()=>showToast('Error de red','error'));
        }));
    })();
    </script>

    <!-- Footer -->
    <footer class="main-footer">
        <p>© 2025 JE&J Streetwear | Panel de Marketing</p>
    </footer>
</body>
</html>
