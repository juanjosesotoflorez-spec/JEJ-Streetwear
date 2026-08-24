<?php
session_start();

// Si no hay sesión, igual se permite ver el carrito
// pero podrías mostrar un aviso si intenta comprar sin iniciar sesión
$user_id = $_SESSION['user_id'] ?? null;
?>




<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito - JE&J </title>
<link rel="stylesheet" href="estilo.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* Estilos Modal Pago */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal-content {
    background: #1a1a1a;
    border: 3px solid #b30000;
    border-radius: 12px;
    padding: 30px;
    max-width: 450px;
    width: 90%;
    text-align: center;
    box-shadow: 0 0 25px rgba(179,0,0,0.8);
    animation: fadeIn 0.4s ease-in-out;
}
.modal-content h2 {
    margin-bottom: 20px;
    color: #ff1a1a;
}
.payment-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}
.payment-option {
    background: #000;
    border: 2px solid #b30000;
    border-radius: 8px;
    padding: 12px;
    color: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}
.payment-option:hover {
    background: #b30000;
    transform: scale(1.05);
}
.close-btn {
    margin-top: 20px;
    background: #b30000;
    border: none;
    padding: 10px 20px;
    color: #fff;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    transition: 0.3s;
}
.close-btn:hover {
    background: #ff1a1a;
}

/* Formularios de pago */
.payment-form {
    display: none;
    margin-top: 15px;
    flex-direction: column;
    gap: 10px;
    text-align: left;
}
.payment-form input {
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #b30000;
    background: #000;
    color: #fff;
    width: 100%;
}
.confirm-btn {
    background: #ff1a1a;
    margin-top: 10px;
    padding: 10px;
    border-radius: 6px;
    color: #fff;
    font-weight: bold;
    width: 100%;
    cursor: pointer;
}
.confirm-btn:hover {
    background: #b30000;
}

@keyframes fadeIn {
    from {opacity: 0; transform: scale(0.9);}
    to {opacity: 1; transform: scale(1);}
}
/* ======== CENTRAR TEXTO DE PRODUCTO AÑADIDO ======== */
.cart-item-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}
.cart-item-info h4,
.cart-item-info p {
    text-align: center;
}

</style>
</head>
<body>
<header>
    <div class="header-container">
        <h1><i class="fas fa-crown"></i> JE&J Streetwear</h1>
        <nav>
            <ul class="navbar-links">
                <li><a href="../index.html"><i class="fas fa-home"></i> Inicio</a></li>
                <li><a href="catalogo.html"><i class="fas fa-th-large"></i> Catálogo</a></li>
                <li><a href="camisas.html"><i class="fas fa-tshirt"></i> Camisas</a></li>
                <li><a href="zapatos.html"><i class="fas fa-shoe-prints"></i> Zapatos</a></li>
                <li><a href="buzos.html"><i class="fas fa-tshirt"></i> Buzos</a></li>
                <li><a href="accesorios.html"><i class="fas fa-gem"></i> Accesorios</a></li>
                <li><a href="carrito.html"><i class="fas fa-shopping-cart"></i> Carrito</a></li>
                <li><a href="../coleccion/coleccion.html"><i class="fas fa-star"></i> Colección</a></li>
                <li><a href="../contacto.html"><i class="fas fa-phone"></i> Contacto</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
<section class="cart-section">
    <h2><i class="fas fa-shopping-cart"></i> Tu Carrito de Compras</h2>

    <div id="empty-cart" class="empty-cart">
        <i class="fas fa-shopping-cart fa-3x"></i>
        <h3>Tu carrito está vacío</h3>
        <p>Agrega productos desde nuestro catálogo</p>
        <a href="catalogo.html" class="btn-continue-shopping">Continuar Comprando</a>
    </div>

    <div id="cart-items" class="cart-items">
        <!-- Productos dinámicos -->
    </div>

    <div id="cart-summary" class="cart-summary">
        <div class="cart-total">
            <h3>Resumen de la Compra</h3>
            <div class="summary-row"><span>Subtotal:</span><span>$<span id="subtotal">0.00</span></span></div>
            <div class="summary-row"><span>Envío:</span><span>$<span id="shipping">0.00</span></span></div>
            <div class="summary-row total-row"><span>Total:</span><span>$<span id="total-price">0.00</span></span></div>
        </div>
        <div class="cart-actions">
            <button id="clear-cart" class="btn-clear-cart"><i class="fas fa-trash"></i> Vaciar Carrito</button>
            <button id="checkout-button" class="btn-checkout"><i class="fas fa-credit-card"></i> Finalizar Compra</button>
        </div>
    </div>
</section>
</main>

<!-- Modal Métodos de Pago -->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <h2>Elige tu Método de Pago</h2>
        <div class="payment-options">
            <div class="payment-option" data-form="credit-card"><i class="fas fa-credit-card"></i> Tarjeta de Crédito/Débito</div>
            <div class="payment-option" data-form="paypal"><i class="fab fa-paypal"></i> PayPal</div>
            <div class="payment-option" data-form="cash"><i class="fas fa-hand-holding-usd"></i> Pago Contraentrega</div>
            <div class="payment-option" data-form="mobile"><i class="fas fa-mobile-alt"></i> Nequi / Daviplata</div>
        </div>

        <!-- Formularios -->
        <form id="credit-card" class="payment-form">
            <input type="text" placeholder="Número de tarjeta" required>
            <input type="text" placeholder="Nombre en la tarjeta" required>
            <input type="text" placeholder="Fecha de vencimiento MM/AA" required>
            <input type="text" placeholder="CVV" required>
            <button type="button" class="confirm-btn" onclick="procesarCompra()">Confirmar Pago</button>
        </form>

        <form id="paypal" class="payment-form">
            <input type="email" placeholder="Correo PayPal" required>
            <button type="button" class="confirm-btn" onclick="procesarCompra()">Confirmar Pago</button>
        </form>

        <form id="cash" class="payment-form">
            <p>Se cobrará al recibir el pedido en su domicilio.</p>
            <button type="button" class="confirm-btn" onclick="procesarCompra()">Confirmar Pedido</button>
        </form>

        <form id="mobile" class="payment-form">
            <input type="text" placeholder="Número de cuenta Nequi/Daviplata" required>
            <input type="text" placeholder="Nombre titular" required>
            <button type="button" class="confirm-btn" onclick="procesarCompra()">Confirmar Pago</button>
        </form>

        <button class="close-btn" id="close-modal">Cerrar</button>
    </div>
</div>

<footer>
<p>&copy; JE&J Streetwear | líneas de atención.</p>
</footer>

<script src="/js/nav-auth.js"></script>
<script src="carrito.js"></script>
<script>
const checkoutBtn = document.getElementById("checkout-button");
const modal = document.getElementById("payment-modal");
const closeModal = document.getElementById("close-modal");
const paymentOptions = document.querySelectorAll(".payment-option");
const paymentForms = document.querySelectorAll(".payment-form");

checkoutBtn.addEventListener("click", ()=>{ modal.style.display="flex"; });
closeModal.addEventListener("click", ()=>{ modal.style.display="none"; });
window.addEventListener("click", e => { if(e.target===modal) modal.style.display="none"; });

// Mostrar el formulario seleccionado
paymentOptions.forEach(option=>{
    option.addEventListener("click", ()=>{
        const formId = option.getAttribute("data-form");
        paymentForms.forEach(f => f.style.display="none");
        document.getElementById(formId).style.display="flex";
    });
});

// Confirmación de pago
function procesarCompra(){
    alert("¡Su compra está siendo procesada!");
    modal.style.display="none";
}
</script>
</body>
</html>
