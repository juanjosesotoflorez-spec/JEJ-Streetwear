// Variables globales
let carrito = [];
let productoActual = null;
let cantidadActual = 1;

// Función para mostrar detalles del producto
function mostrarDetalles(titulo, precio, descripcion, id) {
  productoActual = { titulo, precio, descripcion, id };
  cantidadActual = 1;
  document.getElementById('quantity').value = 1;
  
  document.getElementById('modal-titulo').textContent = titulo;
  document.getElementById('modal-precio').textContent = `$${precio.toFixed(2)}`;
  document.getElementById('modal-descripcion').textContent = descripcion;
  document.getElementById('modal').style.display = 'block';
}

// Función para cerrar modal
function cerrarModal() {
  document.getElementById('modal').style.display = 'none';
}

// Función para cambiar cantidad
function cambiarCantidad(cambio) {
  const input = document.getElementById('quantity');
  let nuevaCantidad = parseInt(input.value) + cambio;
  
  if (nuevaCantidad >= 1 && nuevaCantidad <= 10) {
    input.value = nuevaCantidad;
    cantidadActual = nuevaCantidad;
  }
}

// Función para agregar al carrito
function agregarAlCarrito() {
  if (!productoActual) return;
  
  const itemExistente = carrito.find(item => item.id === productoActual.id);
  
  if (itemExistente) {
    itemExistente.cantidad += cantidadActual;
  } else {
    carrito.push({
      ...productoActual,
      cantidad: cantidadActual
    });
  }
  
  actualizarContadorCarrito();
  cerrarModal();
  mostrarNotificacion('Producto agregado al carrito');
  guardarCarrito();
}

// Función para actualizar contador del carrito
function actualizarContadorCarrito() {
  const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
  document.getElementById('cartCount').textContent = totalItems;
}

// Función para mostrar el carrito
function mostrarCarrito() {
  if (carrito.length === 0) {
    alert('Tu carrito está vacío');
    return;
  }
  
  renderizarCarrito();
  document.getElementById('cartModal').style.display = 'block';
}

// Función para renderizar el carrito
function renderizarCarrito() {
  const cartItems = document.getElementById('cartItems');
  const cartTotal = document.getElementById('cartTotal');
  
  cartItems.innerHTML = '';
  let total = 0;
  
  carrito.forEach((item, index) => {
    const itemElement = document.createElement('div');
    itemElement.className = 'cart-item';
    
    const subtotal = item.precio * item.cantidad;
    total += subtotal;
    
    itemElement.innerHTML = `
      <div class="cart-item-info">
        <h4>${item.titulo}</h4>
        <p>$${item.precio.toFixed(2)} x ${item.cantidad}</p>
      </div>
      <div class="cart-item-actions">
        <span class="cart-item-subtotal">$${subtotal.toFixed(2)}</span>
        <button onclick="eliminarDelCarrito(${index})" class="btn-eliminar">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    `;
    
    cartItems.appendChild(itemElement);
  });
  
  cartTotal.textContent = total.toFixed(2);
}

// Función para eliminar item del carrito
function eliminarDelCarrito(index) {
  carrito.splice(index, 1);
  actualizarContadorCarrito();
  renderizarCarrito();
  guardarCarrito();
}

// Función para vaciar carrito
function vaciarCarrito() {
  if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
    carrito = [];
    actualizarContadorCarrito();
    renderizarCarrito();
    cerrarCarrito();
    guardarCarrito();
  }
}

// Función para proceder a compra
function procederCompra() {
  alert('¡Gracias por tu compra! Total: $' + document.getElementById('cartTotal').textContent);
  carrito = [];
  actualizarContadorCarrito();
  cerrarCarrito();
  guardarCarrito();
}

// Función para cerrar carrito
function cerrarCarrito() {
  document.getElementById('cartModal').style.display = 'none';
}

// Función para mostrar notificación
function mostrarNotificacion(mensaje) {
  const notificacion = document.createElement('div');
  notificacion.className = 'notificacion';
  notificacion.textContent = mensaje;
  document.body.appendChild(notificacion);
  
  setTimeout(() => {
    notificacion.remove();
  }, 2000);
}

// Función para editar producto
function editarProducto(nombre) {
  alert(`Redirigiendo a personalización de: ${nombre}`);
  window.location.href = 'personalizar_ropa.html';
}

// Función para personalizar producto
function personalizarProducto() {
  const titulo = document.getElementById('modal-titulo').textContent;
  alert(`Redirigiendo a personalización de: ${titulo}`);
  cerrarModal();
  window.location.href = 'personalizar_ropa.html';
}

// Función para guardar carrito en localStorage
function guardarCarrito() {
  localStorage.setItem('carrito', JSON.stringify(carrito));
}

// Función para cargar carrito desde localStorage
function cargarCarrito() {
  const carritoGuardado = localStorage.getItem('carrito');
  if (carritoGuardado) {
    carrito = JSON.parse(carritoGuardado);
    actualizarContadorCarrito();
  }
}

// Event listeners para modales
document.addEventListener('DOMContentLoaded', function() {
  // Cargar carrito guardado
  cargarCarrito();
  
  // Cerrar modales al hacer clic fuera
  window.onclick = function(event) {
    const modal = document.getElementById('modal');
    const cartModal = document.getElementById('cartModal');
    
    if (event.target == modal) {
      cerrarModal();
    }
    if (event.target == cartModal) {
      cerrarCarrito();
    }
  }
  
  // Cerrar modal con X
  const span = document.getElementsByClassName('close')[0];
  span.onclick = cerrarModal;
  
  // Event listener para el input de cantidad
  const quantityInput = document.getElementById('quantity');
  if (quantityInput) {
    quantityInput.addEventListener('change', function() {
      cantidadActual = parseInt(this.value) || 1;
      if (cantidadActual < 1) cantidadActual = 1;
      if (cantidadActual > 10) cantidadActual = 10;
      this.value = cantidadActual;
    });
  }
});