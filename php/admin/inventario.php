<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
include("../config/db.php");

// Seleccionamos columnas útiles para inventario
$inv_query = "SELECT id, nombre, precio, stock, categoria, talla, color, imagen FROM productos ORDER BY id DESC";
$inv_res = $conn->query($inv_query);
$inventario_rows = [];
$inv_error = null;
if ($inv_res) {
    while ($row = $inv_res->fetch_assoc()) { $inventario_rows[] = $row; }
    $inv_res->free();
} else {
    // Guardamos el error para mostrarlo al admin (no mostraremos stack traces)
    $inv_error = $conn->error;
}

// Obtener categorías para filtro (manejo de fallo similar)
$catRes = $conn->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria <> ''");
if (!$catRes) { /* si falla, catRes será falsy y el select se mostrará vacío */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario — JE&J Admin</title>
    <link rel="stylesheet" href="css/inventario.css">
    <style>
    /* Small helper to ensure action buttons look consistent if CSS file missing */
    .actions { display:flex; gap:10px; align-items:center; margin-bottom:12px; }
    .search { flex:1; }
    .btn { display:inline-block; padding:8px 12px; border-radius:6px; text-decoration:none; color:#fff; background:#ff1744; }
    .btn.secondary { background:#333; color:#fff; }
    .badge { background:#222; color:#fff; padding:6px 10px; border-radius:999px; }
    </style>
</head>
<body>
    <header class="main-header">
        <h1 class="logo">JE&J <span>Admin</span></h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="productos.php">Productos</a>
            <a href="inventario.php" class="active">Inventario</a>
            <a href="../auth/logout.php">Salir</a>
        </nav>
    </header>

    <main class="container">
        <div class="top-row" style="display:flex; justify-content:space-between; align-items:center; gap:20px;">
            <h2>Gestión de Inventario</h2>
            <div class="actions">
                <a href="dashboard.php" class="btn secondary">← Volver</a>
                <a href="productos.php" class="btn">Ver Productos</a>
            </div>
        </div>

        <div class="controls" style="margin-top:18px; display:flex; gap:12px; align-items:center;">
            <input id="search" class="search" type="search" placeholder="Buscar por nombre, SKU, categoría..." />
            <select id="filter-categoria">
                <option value="">Todas las categorías</option>
                <?php if ($catRes): while($c = $catRes->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($c['categoria']) ?>"><?= htmlspecialchars($c['categoria']) ?></option>
                <?php endwhile; endif; ?>
            </select>
            <span class="badge" id="total-count"><?= count($inventario_rows) ?></span>
        </div>

        <div class="table-container">
            <table id="inv-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Categoría</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inv_error): ?>
                        <tr><td colspan="9" style="color:#ffb4b4; text-align:center; padding:18px;">Error al cargar inventario: <?= htmlspecialchars($inv_error) ?></td></tr>
                    <?php else: foreach($inventario_rows as $i): ?>
                        <?php $low = intval($i['stock']) < 5; ?>
                        <tr data-id="<?= $i['id'] ?>" class="<?= $low ? 'low-stock':'' ?>">
                            <td><?= $i['id'] ?></td>
                            <td class="prod-name" style="text-align:left; display:flex; gap:10px; align-items:center;">
                                    <?php
                                        // Construir ruta de imagen robusta:
                                        $img_src = '/imagenes/logo2.png'; // fallback general
                                        if (!empty($i['imagen'])) {
                                            $val = trim($i['imagen']);
                                            // URL absoluta
                                            if (preg_match('#^https?://#i', $val)) {
                                                $img_src = $val;
                                            } elseif (strpos($val, 'imagenes/') !== false) {
                                                $img_src = '/' . ltrim($val, '/');
                                            } else {
                                                // solo nombre de archivo
                                                $img_src = '/imagenes/' . ltrim($val, '/');
                                            }
                                            // si el archivo no existe en el servidor, fallback a logo
                                            $docPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $img_src;
                                            if (strpos($img_src, 'http') !== 0 && !file_exists($docPath)) {
                                                $img_src = '/imagenes/logo2.png';
                                            }
                                        }
                                    ?>
                                    <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($i['nombre']) ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;"/>
                                <div>
                                    <div style="font-weight:700"><?= htmlspecialchars($i['nombre']) ?></div>
                                    <div style="font-size:0.9rem;color:#bbb">ID: <?= $i['id'] ?></div>
                                </div>
                            </td>
                            <td>$<?= number_format($i['precio'] ?? 0, 2) ?></td>
                            <td class="stock-cell"><?= $i['stock'] ?></td>
                            <td><?= htmlspecialchars($i['categoria'] ?? '') ?></td>
                            <td><?= htmlspecialchars($i['talla'] ?? '') ?></td>
                            <td><?= htmlspecialchars($i['color'] ?? '') ?></td>
                            <td>
                                <a class="btn secondary" href="producto_editar.php?id=<?= $i['id'] ?>">Editar</a>
                                <button class="btn adjust-stock" data-id="<?= $i['id'] ?>">Ajustar</button>
                                <a class="btn" href="producto_borrar.php?id=<?= $i['id'] ?>">Borrar</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer class="main-footer">
        <p>© <?= date('Y') ?> JE&J Streetwear. Panel de administración.</p>
    </footer>

    <!-- Modal simple para ajustar stock -->
    <div id="stock-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); align-items:center; justify-content:center;">
        <div style="background:#1f1f1f; padding:20px; border-radius:8px; width:360px; color:#fff;">
            <h3>Ajustar stock — <span id="modal-prod-name"></span></h3>
            <input id="modal-stock-input" type="number" min="0" style="width:100%; padding:8px; margin-top:12px;" />
            <div style="display:flex; gap:8px; justify-content:flex-end; margin-top:12px;">
                <button id="modal-cancel" class="btn secondary">Cancelar</button>
                <button id="modal-save" class="btn">Guardar</button>
            </div>
        </div>
    </div>

    <script>
    // Client-side filtering and adjust-stock handler
    (function(){
        const search = document.getElementById('search');
        const filter = document.getElementById('filter-categoria');
        const table = document.getElementById('inv-table');
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const totalBadge = document.getElementById('total-count');

        function updateCount(){
            const visible = rows.filter(r => r.style.display !== 'none').length;
            totalBadge.textContent = visible;
        }

        function applyFilter(){
            const q = search.value.trim().toLowerCase();
            const cat = filter.value;
            rows.forEach(r => {
                const rowText = r.textContent.toLowerCase();
                const categoriaCell = r.querySelector('td:nth-child(6)'); // categoría column (1-based)
                const categoria = categoriaCell ? categoriaCell.textContent.trim() : '';
                const matchText = rowText.includes(q) || categoria.toLowerCase().includes(q);
                const match = matchText && (cat === '' || categoria === cat);
                r.style.display = match ? '' : 'none';
            });
            updateCount();
        }

        search.addEventListener('input', applyFilter);
        filter.addEventListener('change', applyFilter);

        // Modal stock
        const modal = document.getElementById('stock-modal');
        const modalName = document.getElementById('modal-prod-name');
        const modalInput = document.getElementById('modal-stock-input');
        let currentRowId = null;

        table.addEventListener('click', function(e){
            if(e.target.classList.contains('adjust-stock')){
                const id = e.target.dataset.id;
                currentRowId = id;
                const row = table.querySelector('tr[data-id="'+id+'"]');
                modalName.textContent = row.querySelector('.prod-name div').innerText;
                modalInput.value = row.querySelector('.stock-cell').innerText.trim();
                modal.style.display = 'flex';
            }
        });

        document.getElementById('modal-cancel').addEventListener('click', ()=> modal.style.display='none');
        document.getElementById('modal-save').addEventListener('click', ()=>{
            const newVal = parseInt(modalInput.value,10);
            if(isNaN(newVal) || newVal < 0) return alert('Ingresa un stock válido');
            // enviar POST al endpoint
            fetch('inventario_update_stock.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({id: currentRowId, stock: newVal})
            }).then(r=>r.json()).then(data=>{
                if(data.ok){
                    const row = table.querySelector('tr[data-id="'+currentRowId+'"]');
                    row.querySelector('.stock-cell').innerText = newVal;
                    row.classList.toggle('low-stock', newVal < 5);
                    modal.style.display = 'none';
                } else {
                    alert('Error: ' + (data.error || 'No se pudo actualizar'));
                }
            }).catch(err=>alert('Error de red'));
        });

        // Inicial
        updateCount();
    })();
    </script>
</body>
</html>
