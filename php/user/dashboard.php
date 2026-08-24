<?php
session_start();
if ($_SESSION['rol'] != 'user') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");
?>


<link rel="stylesheet" type="text/css" href="css/user.css">

<h1>Dashboard Usuario</h1>
<ul>
    <li><a href="../../index.php">Inicio / Panel Principal</a></li>
    <li><a href="perfil.php">Mi Perfil</a></li>
    <li><a href="pedidos.php">Mis Pedidos</a></li>
    <li><a href="wishlist.php">Lista de Deseos</a></li>
    <li><a href="carrito.php">Carrito de Compras</a></li>
    <li><a href="pagos.php">Métodos de Pago</a></li>
    <li><a href="cupones.php">Cupones / Descuentos</a></li>
</ul>

<a href="../auth/logout.php">Cerrar Sesión</a>
    <!-- Badge de sugerencias: se actualiza por JS -->
    <style>
        #sugerencias-badge { display: inline-block; background: #e74c3c; color: white; border-radius: 999px; padding: 2px 8px; font-size: 0.85rem; margin-left: 8px; vertical-align: middle; }
    </style>
    <script>
        async function updateUserSugerenciasCount() {
            try {
                const res = await fetch('/PROYECTO_2/php/user/get_contactos.php');
                if (!res.ok) return;
                const data = await res.json();
                const a = document.querySelector('a[href="sugerencias.php"]');
                if (!a) return;
                let badge = document.getElementById('sugerencias-badge');
                if (!badge) {
                    badge = document.createElement('span'); badge.id = 'sugerencias-badge'; a.appendChild(badge);
                }
                const count = Array.isArray(data) ? data.length : 0;
                badge.textContent = count > 0 ? count : '';
                badge.style.display = count > 0 ? 'inline-block' : 'none';
            } catch (e) { console.error('Error actualizando sugerencias count', e); }
        }

        // Escuchar notificaciones cross-tab
        try {
            const bc = new BroadcastChannel('contactos');
            bc.addEventListener('message', (ev) => { if (ev.data && ev.data.type === 'nuevo_contacto') updateUserSugerenciasCount(); });
        } catch (e) {
            window.addEventListener('storage', (ev) => { if (ev.key === 'contactos_nuevo') updateUserSugerenciasCount(); });
        }

        document.addEventListener('DOMContentLoaded', updateUserSugerenciasCount);
    </script>

    <?php include("../includes/footer.php"); ?>

