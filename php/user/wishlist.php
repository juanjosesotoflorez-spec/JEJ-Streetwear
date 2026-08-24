<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
include("../config/db.php");

$id = $_SESSION['user_id'];

// Obtener los productos que el usuario agregó a su wishlist
$wishlist = $conn->query("
    SELECT w.id, p.nombre, p.imagen, p.precio 
    FROM wishlist w 
    JOIN productos p ON w.producto_id = p.id 
    WHERE w.usuario_id = $id
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Wishlist - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/wishlist.css">
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="perfil.php">Mi Perfil</a>
            <a href="carrito.php">Carrito</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <section class="wishlist-container">
            <h2>❤️ Mi Wishlist</h2>
            <p class="bienvenida">Estos son los productos que has guardado para ver más tarde.</p>

            <?php if ($wishlist->num_rows > 0): ?>
                <div class="wishlist-grid">
                    <?php while($w = $wishlist->fetch_assoc()): ?>
                        <div class="wishlist-card">
                            <img src="../uploads/<?= $w['imagen'] ?>" alt="<?= $w['nombre'] ?>">
                            <h3><?= $w['nombre'] ?></h3>
                            <p class="precio">$<?= number_format($w['precio'], 2) ?></p>
                            <div class="actions">
                                <a href="carrito_agregar.php?id=<?= $w['id'] ?>" class="btn-agregar">🛒 Agregar al carrito</a>
                                <a href="wishlist_eliminar.php?id=<?= $w['id'] ?>" class="btn-eliminar">❌ Eliminar</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-items">⚠️ No tienes productos guardados aún.</p>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <p>&copy; <?= date("Y") ?> JE&J Streetwear - Todos los derechos reservados</p>
    </footer>
    <script>
    // Escuchar notificaciones de wishlist desde otras pestañas
    function insertarProductoEnDOM(prod){
        const grid = document.querySelector('.wishlist-grid');
        // Si no hay grid, crearla
        if(!grid){
            const container = document.querySelector('.wishlist-container');
            const div = document.createElement('div'); div.className='wishlist-grid';
            container.appendChild(div);
        }
        const card = document.createElement('div'); card.className='wishlist-card';
        card.innerHTML = `
            <img src="../uploads/${prod.imagen}" alt="${prod.nombre}">
            <h3>${prod.nombre}</h3>
            <p class="precio">$${Number(prod.precio).toFixed(2)}</p>
            <div class="actions">
                <a href="carrito_agregar.php?id=${prod.id}" class="btn-agregar">🛒 Agregar al carrito</a>
                <a href="wishlist_eliminar.php?id=${prod.id}" class="btn-eliminar">❌ Eliminar</a>
            </div>
        `;
        document.querySelector('.wishlist-grid').prepend(card);
    }

    function fetchYInsert(product_id){
        fetch('get_producto.php?id='+encodeURIComponent(product_id))
        .then(r=>r.json()).then(j=>{
            if(j.ok) insertarProductoEnDOM(j.producto);
        }).catch(e=>console.warn('No se pudo cargar producto',e));
    }

    if('BroadcastChannel' in window){
        const ch = new BroadcastChannel('wishlist_channel');
        ch.onmessage = e => {
            if(e.data && e.data.type === 'added' && e.data.product_id){
                fetchYInsert(e.data.product_id);
            }
        };
    }

    window.addEventListener('storage', function(ev){
        if(ev.key === 'wishlist_added' && ev.newValue){
            try{ const data = JSON.parse(ev.newValue); if(data.product_id) fetchYInsert(data.product_id); }catch(e){}
        }
    });
    </script>
</body>
</html>
