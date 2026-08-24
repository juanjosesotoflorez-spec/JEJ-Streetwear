<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito - JE&J Streetwear</title>
    <link rel="stylesheet" href="css/carrito.css">
</head>
<body>
    <header>
        <div class="logo">JE&J <span>Streetwear</span></div>
        <nav>
            <a href="dashboard.php">Inicio</a>
            <a href="../perfil.php">Mi Perfil</a>
            <a href="../wishlist.php">Wishlist</a>
            <a href="../auth/logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <section class="carrito-container">
            <h2>🛒 Mi Carrito</h2>
            <div id="cartArea">
                <p class="info">Cargando carrito...</p>
            </div>
            <div style="margin-top:12px;">
                <button id="vaciarBtn" class="btn-checkout" style="background:#c0392b;color:#fff;border:none;padding:8px 12px;cursor:pointer;">Vaciar Carrito</button>
                <a href="checkout.php" id="checkoutBtn" class="btn-checkout" style="margin-left:8px;">Proceder al Checkout</a>
            </div>
        </section>
    </main>

    <script>
    // Reutilizable: formatea moneda
    function fc(v){ return '$' + Number(v||0).toFixed(2); }

    function renderCartPage(){
        const area = document.getElementById('cartArea');
        const raw = localStorage.getItem('carrito');
        let carrito = [];
        try { carrito = raw ? JSON.parse(raw) : []; } catch(e){ carrito = []; }

        if(!area) return;
        if(!carrito || carrito.length === 0){
            area.innerHTML = '<p class="info">Tu carrito está vacío.</p>';
            return;
        }

        let total = 0;
        const list = document.createElement('div');
        list.className = 'cart-list';

        carrito.forEach((item, idx) => {
            const price = Number(item.precio || item.price || 0);
            const qty = Number(item.cantidad || item.qty || 1);
            const subtotal = price * qty;
            total += subtotal;

            const row = document.createElement('div');
            row.className = 'cart-row';
            row.style.display = 'flex';
            row.style.justifyContent = 'space-between';
            row.style.alignItems = 'center';
            row.style.padding = '10px 0';
            row.style.borderBottom = '1px solid #eee';

            const info = document.createElement('div');
            info.innerHTML = `<strong>${item.titulo || item.nombre || item.name || item.title || 'Producto'}</strong><br><small>${qty} x ${fc(price)}</small>`;

            const right = document.createElement('div');
            right.innerHTML = `<span style="margin-right:12px;">${fc(subtotal)}</span>`;

            const del = document.createElement('button');
            del.textContent = 'Eliminar';
            del.style.padding = '6px 8px';
            del.style.cursor = 'pointer';
            del.onclick = () => { removeFromCart(idx); };

            right.appendChild(del);
            row.appendChild(info);
            row.appendChild(right);
            list.appendChild(row);
        });

        const summary = document.createElement('div');
        summary.style.marginTop = '12px';
        summary.innerHTML = `<h3>Total: ${fc(total)}</h3>`;

        area.innerHTML = '';
        area.appendChild(list);
        area.appendChild(summary);
    }

    function removeFromCart(idx){
        const raw = localStorage.getItem('carrito');
        let carrito = [];
        try { carrito = raw ? JSON.parse(raw) : []; } catch(e){ carrito = []; }
        if(idx >= 0 && idx < carrito.length){
            carrito.splice(idx,1);
            localStorage.setItem('carrito', JSON.stringify(carrito));
            renderCartPage();
            // también despachar storage para otras pestañas
            window.dispatchEvent(new Event('storage'));
        }
    }

    function vaciarCarrito(){
        if(confirm('¿Vaciar carrito?')){
            localStorage.removeItem('carrito');
            renderCartPage();
            window.dispatchEvent(new Event('storage'));
        }
    }

    document.addEventListener('DOMContentLoaded', function(){
        renderCartPage();
        const vac = document.getElementById('vaciarBtn');
        if(vac) vac.addEventListener('click', vaciarCarrito);
        // actualizar si cambia el localStorage en otra pestaña
        window.addEventListener('storage', function(e){ if(e.key === 'carrito' || e.key === null) renderCartPage(); });
    });
    </script>

    <footer>
        <p>&copy; <?= date("Y") ?> JE&J Streetwear - Todos los derechos reservados</p>
    </footer>
</body>
</html>
