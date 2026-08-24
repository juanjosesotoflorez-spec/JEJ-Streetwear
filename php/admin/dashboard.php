<?php
session_start();
if ($_SESSION['rol'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");
?>
<?php include("../includes/header.php"); ?>


<link rel="stylesheet" href="css/admin.css">


<h1>Dashboard Administrador</h1>

<ul>
    <li><a href="../../index.php">Volver al inicio</a></li>
    <li><a href="resumen.php">Panel Principal / Resumen</a></li>
    <li><a href="productos.php">Gestión de Productos</a></li>
    <li><a href="pedidos.php">Gestión de Pedidos</a></li>
    <li><a href="inventario.php">Gestión de Inventario</a></li>
    <li><a href="usuario.php">Gestión de Usuarios</a></li>
    <li><a href="marketing.php">Marketing y Promociones</a></li>
</ul>

<a href="../auth/logout.php">Cerrar Sesión</a>
    <!-- Badge admin de contactos -->
    <style>
        #admin-contactos-badge { display: inline-block; background: #2ecc71; color: white; border-radius: 999px; padding: 2px 8px; font-size: 0.85rem; margin-left: 8px; vertical-align: middle; }
    </style>
    <script>
        async function updateAdminContactosCount() {
            try {
                const res = await fetch('/PROYECTO_2/php/admin/get_contactos.php');
                if (!res.ok) return;
                const data = await res.json();
                const a = document.querySelector('a[href="sugerencias.php"]');
                if (!a) return;
                let badge = document.getElementById('admin-contactos-badge');
                if (!badge) { badge = document.createElement('span'); badge.id = 'admin-contactos-badge'; a.appendChild(badge); }
                const count = Array.isArray(data) ? data.length : 0;
                badge.textContent = count > 0 ? count : '';
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            } catch (e) { console.error('Error actualizando admin contactos', e); }
        }

        try { const bc = new BroadcastChannel('contactos'); bc.addEventListener('message', (ev) => { if (ev.data && ev.data.type === 'nuevo_contacto') updateAdminContactosCount(); }); }
        catch (e) { window.addEventListener('storage', (ev) => { if (ev.key === 'contactos_nuevo') updateAdminContactosCount(); }); }

        document.addEventListener('DOMContentLoaded', updateAdminContactosCount);
    </script>

    <?php include("../includes/footer.php"); ?>
