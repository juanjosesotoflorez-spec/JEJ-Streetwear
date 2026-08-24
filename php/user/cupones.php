<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cupones - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/cupones.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <!-- ======= HEADER ======= -->
    <header>
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav>
            <a href="#">Inicio</a>
            <a href="#">Catálogo</a>
            <a href="#">Carrito</a>
            <a href="#">Perfil</a>
            <a href="#">Salir</a>
        </nav>
    </header>

    <!-- ======= CUERPO PRINCIPAL ======= -->
    <main>
        <section class="cupones-container">
            <h2>🎟️ Cupones Especiales</h2>
            <p class="lead">Aprovecha nuestros descuentos exclusivos por tiempo limitado. ¡Luce con estilo pagando menos!</p>

            <div class="grid-cupones">
                <?php
                include('../config/db.php');
                $today = date('Y-m-d');
                // Trae todos los cupones activos que no estén vencidos
                $rs = $conn->query("SELECT * FROM cupones WHERE activo = 1 AND (fecha_expiracion IS NULL OR fecha_expiracion >= '$today') ORDER BY fecha_expiracion ASC");
                if ($rs && $rs->num_rows > 0) {
                    while($cp = $rs->fetch_assoc()){
                        ?>
                        <div class="cupon" data-code="<?= htmlspecialchars($cp['codigo']) ?>">
                            <div class="cupon-icon">🎟️</div>
                            <h3><?= htmlspecialchars($cp['codigo']) ?></h3>
                            <p class="desc"><?= htmlspecialchars($cp['descuento']) ?>% de descuento</p>
                            <span class="codigo"><?= htmlspecialchars($cp['codigo']) ?></span>
                            <span class="fecha">Válido hasta <?= htmlspecialchars($cp['fecha_expiracion']) ?></span>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p>No hay cupones disponibles actualmente.</p>';
                }
                ?>
            </div>
        </section>
    </main>

    <!-- ======= FOOTER ======= -->
    <footer>
        <p>&copy; 2025 JE&J Streetwear - Estilo que marca tendencia</p>
    </footer>

    <script>
    (function(){
        function showToast(msg){
            let t = document.createElement('div');
            t.style.cssText = 'position:fixed; right:20px; bottom:20px; background:#2d8f2d; color:#fff; padding:10px 14px; border-radius:8px; z-index:9999;';
            t.innerText = msg; document.body.appendChild(t);
            setTimeout(()=> t.remove(), 2500);
        }
        document.querySelectorAll('.cupon').forEach(c => {
            c.addEventListener('click', () => {
                const code = c.querySelector('.codigo').innerText;
                navigator.clipboard.writeText(code).then(()=> showToast('✅ Código copiado: ' + code)).catch(()=> alert('No se pudo copiar'));
            });
        });
    })();
    </script>
</body>
</html>
