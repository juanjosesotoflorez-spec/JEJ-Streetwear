// =====================
// CARRITO DE JE&J STREETWEAR
// =====================

// Obtener carrito del localStorage
let carrito = JSON.parse(localStorage.getItem("carrito")) || [];

const cartItems = document.getElementById("cart-items");
const subtotalEl = document.getElementById("subtotal");
const totalEl = document.getElementById("total-price");
const emptyCart = document.getElementById("empty-cart");
const cartSummary = document.getElementById("cart-summary");

// =====================
// Mostrar productos en el carrito
// =====================
function mostrarCarrito() {
    cartItems.innerHTML = "";

    if (carrito.length === 0) {
        emptyCart.style.display = "block";
        cartSummary.style.display = "none";
        return;
    }

    emptyCart.style.display = "none";
    cartSummary.style.display = "block";

    carrito.forEach((item, index) => {
        const div = document.createElement("div");
        div.classList.add("cart-item");
        div.innerHTML = `
            <div class="cart-item-info">
                <h4>${item.nombre}</h4>
                <p>Precio: $${item.precio}</p>
                <p>Cantidad: ${item.cantidad}</p>
            </div>
            <button class="btn-remove" onclick="eliminarProducto(${index})">
                <i class="fas fa-trash"></i>
            </button>
        `;
        cartItems.appendChild(div);
    });

    actualizarTotales();
}

// =====================
// Actualizar totales
// =====================
function actualizarTotales() {
    let subtotal = 0;
    carrito.forEach(item => subtotal += item.precio * item.cantidad);
    subtotalEl.textContent = subtotal.toFixed(2);
    totalEl.textContent = subtotal.toFixed(2);
}

// =====================
// Eliminar un producto
// =====================
function eliminarProducto(index) {
    carrito.splice(index, 1);
    localStorage.setItem("carrito", JSON.stringify(carrito));
    mostrarCarrito();
}

// =====================
// Vaciar carrito
// =====================
document.getElementById("clear-cart").addEventListener("click", () => {
    if (confirm("¿Deseas vaciar todo el carrito?")) {
        carrito = [];
        localStorage.removeItem("carrito");
        mostrarCarrito();
    }
});

// =====================
// Finalizar compra (abrir modal de pago)
// =====================
const checkoutBtn = document.getElementById("checkout-button");
const modal = document.getElementById("payment-modal");
const closeModal = document.getElementById("close-modal");
const paymentOptions = document.querySelectorAll(".payment-option");
const paymentForms = document.querySelectorAll(".payment-form");

checkoutBtn.addEventListener("click", () => {
    if (carrito.length === 0) {
        alert("Tu carrito está vacío.");
        return;
    }
    modal.style.display = "flex";
});
closeModal.addEventListener("click", () => modal.style.display = "none");
window.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });

// Mostrar formulario seleccionado
paymentOptions.forEach(option => {
    option.addEventListener("click", () => {
        const formId = option.getAttribute("data-form");
        paymentForms.forEach(f => f.style.display = "none");
        document.getElementById(formId).style.display = "flex";
    });
});

// =====================
// PROCESAR COMPRA - CONEXIÓN CON PHP
// =====================
function procesarCompra() {
    const total = parseFloat(totalEl.textContent.replace(/[^0-9.]/g, "")) || 0;

    if (isNaN(total) || total <= 0) {
        alert("⚠️ Tu carrito está vacío o el total no es válido.");
        return;
    }

    // Detectar método de pago visible
    const visibleForm = Array.from(document.querySelectorAll(".payment-form"))
        .find(f => f.style.display === "flex");
    const metodo = visibleForm ? visibleForm.id : "Desconocido";

    // ✅ IMPORTANTE: Ajustar la ruta a tu archivo PHP
    fetch("../php/user/procesar_pedido.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `total=${encodeURIComponent(total)}&metodo=${encodeURIComponent(metodo)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ ¡Tu pedido ha sido registrado con éxito!");

            // Vaciar carrito
            localStorage.removeItem("carrito");
            carrito = [];
            mostrarCarrito();

            modal.style.display = "none";

            // Redirigir al dashboard de pedidos
            setTimeout(() => {
                window.location.href = "../php/user/pedidos.php";
            }, 1200);
        } else {
            alert("❌ Error al guardar el pedido: " + (data.error || "Desconocido"));
        }
    })
    .catch(err => {
        alert("Error de conexión con el servidor.");
        console.error(err);
    });
}

// =====================
// Inicializar carrito al cargar la página
// =====================
document.addEventListener("DOMContentLoaded", mostrarCarrito);