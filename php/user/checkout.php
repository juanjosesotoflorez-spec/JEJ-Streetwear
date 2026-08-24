<?php
session_start();
if ($_SESSION['rol'] != 'user') { header("Location: ../auth/login.php"); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - JE&J Streetwear</title>
    <style> .center{max-width:800px;margin:30px auto;padding:20px;border:1px solid #eee} </style>
</head>
<body>
<div class="center">
    <h2>Checkout</h2>
    <p id="status">Cargando carrito...</p>
    <div id="cartPreview"></div>
    <div style="margin-top:12px;">
        <button id="placeOrder">Realizar Pedido</button>
    </div>
</div>

<script>
function fc(v){ return '$' + Number(v||0).toFixed(2); }
function render(){
    const raw = localStorage.getItem('carrito');
    let carrito = [];
    try{ carrito = raw ? JSON.parse(raw) : []; }catch(e){ carrito=[]; }
    const pv = document.getElementById('cartPreview');
    const st = document.getElementById('status');
    if(!carrito || carrito.length===0){ st.textContent='Tu carrito está vacío.'; pv.innerHTML=''; return; }
    let total=0; pv.innerHTML='';
    carrito.forEach(it=>{ const price = Number(it.precio||it.price||0); const qty = Number(it.cantidad||it.qty||1); total += price*qty; const d = document.createElement('div'); d.textContent = (it.titulo||it.nombre||it.name||'Producto') + ' — ' + qty + ' x ' + fc(price); pv.appendChild(d); });
    st.textContent = 'Total: ' + fc(total);
}

async function place(){
    const raw = localStorage.getItem('carrito');
    let carrito = raw ? JSON.parse(raw) : [];
    let total = carrito.reduce((s,it)=> s + (Number(it.precio||it.price||0) * Number(it.cantidad||it.qty||1)), 0);
    const resp = await fetch('crear_pedido.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ items: carrito, total }) });
    const j = await resp.json();
    if(j.ok){
        alert('Pedido creado: #' + j.order_id);
        localStorage.removeItem('carrito');
        render();
        // redirigir a pedidos
        window.location.href = 'pedidos.php';
    } else {
        alert('Error: ' + (j.msg || 'No se pudo crear el pedido'));
    }
}

document.getElementById('placeOrder').addEventListener('click', place);
render();
</script>
</body>
</html>
