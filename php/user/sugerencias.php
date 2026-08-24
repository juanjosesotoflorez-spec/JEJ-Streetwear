<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
include('../config/db.php');

$id = $_SESSION['user_id'];

// Obtener email del usuario y mostrar contactos por usuario_id o por email (mensajes enviados desde formulario público)
$email = null;
$ue = $conn->prepare("SELECT email FROM usuarios WHERE id = ? LIMIT 1");
if ($ue) {
    $ue->bind_param('i', $id);
    if ($ue->execute()) {
        $ur = $ue->get_result();
        if ($rowu = $ur->fetch_assoc()) $email = $rowu['email'];
    }
    $ue->close();
}

if ($email) {
    $sugerencias = $conn->query("SELECT * FROM contactos WHERE usuario_id=$id OR email='" . $conn->real_escape_string($email) . "' ORDER BY creado DESC");
} else {
    $sugerencias = $conn->query("SELECT * FROM contactos WHERE usuario_id=$id ORDER BY creado DESC");
}

if (!$sugerencias) { $sugerencias_error = $conn->error; $sugerencias = null; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Sugerencias - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/resenas.css">
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="perfil.php">Mi Perfil</a>
            <a href="../auth/logout.php">Salir</a>
        </nav>
    </header>
    <main>
        <section class="resenas-container">
            <h2>📨 Mis Sugerencias y Mensajes</h2>
            <div id="contactos-list">
                <?php if ($sugerencias instanceof mysqli_result): ?>
                    <ul>
                    <?php while($s = $sugerencias->fetch_assoc()): ?>
                        <li class="resena">
                            <h3><?= htmlspecialchars($s['asunto']) ?></h3>
                            <p class="comentario"><?= nl2br(htmlspecialchars($s['mensaje'])) ?></p>
                            <small class="fecha"><?= $s['creado'] ?></small>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-resenas">Error al cargar tus sugerencias: <?= isset($sugerencias_error) ? htmlspecialchars($sugerencias_error) : 'Sin datos' ?></p>
                <?php endif; ?>
            </div>

            <script>
                // Helper para renderizar contactos (simple)
                async function fetchAndRenderContactos() {
                    try {
                        const res = await fetch('/PROYECTO_2/php/user/get_contactos.php');
                        if (!res.ok) return;
                        const data = await res.json();
                        const container = document.getElementById('contactos-list');
                        if (!container) return;
                        if (!Array.isArray(data) || data.length === 0) {
                            container.innerHTML = '<p class="no-resenas">No tienes mensajes enviados desde el formulario de contacto.</p>';
                            return;
                        }
                        let html = '<ul>';
+                        data.forEach(c => {
                            html += `<li class="resena"><h3>${escapeHtml(c.asunto)}</h3><p class="comentario">${nl2br(escapeHtml(c.mensaje))}</p><small class="fecha">${c.creado}</small></li>`;
                        });
                        html += '</ul>';
+                        container.innerHTML = html;
+                    } catch (e) {
                        console.error('Error fetch contactos:', e);
+                    }
+                }
+
                function escapeHtml(str) {
                    return String(str).replace(/[&<>"']/g, function (s) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[s]; });
                }
+
                function nl2br(str) { return String(str).replace(/\n/g, '<br>'); }
+

                // Escuchar notificaciones cross-tab
                try {
+                    const bc = new BroadcastChannel('contactos');
+                    bc.addEventListener('message', (ev) => { if (ev.data && ev.data.type === 'nuevo_contacto') fetchAndRenderContactos(); });
+                } catch (e) {
+                    window.addEventListener('storage', (ev) => { if (ev.key === 'contactos_nuevo') fetchAndRenderContactos(); });
+                }
+
                // También cargar al inicio
                document.addEventListener('DOMContentLoaded', fetchAndRenderContactos);
+            </script>
        </section>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> JE&J Streetwear</p>
    </footer>
</body>
</html>
