// Funcionalidad del carrito para páginas de productos
// Este archivo permite agregar productos al carrito desde cualquier página

// Función para agregar productos al carrito
function agregarProductoDesdeCatalogo(nombre, precio, imagen, categoria) {
    const producto = {
        id: Date.now().toString() + Math.random().toString(36).substr(2, 9), // ID único
        nombre: nombre,
        precio: parseFloat(precio),
        imagen: imagen,
        categoria: categoria,
        cantidad: 1
    };
    
    // Obtener carrito existente o crear uno nuevo
    let carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    // Verificar si el producto ya existe en el carrito
    const productoExistente = carrito.find(p => p.nombre === producto.nombre);
    
    if (productoExistente) {
        productoExistente.cantidad += 1;
    } else {
        carrito.push(producto);
    }
    
    // Guardar en localStorage
    localStorage.setItem('carrito', JSON.stringify(carrito));
    
    // Mostrar notificación
    mostrarNotificacionProducto('Producto agregado al carrito');
    
    // Actualizar contador del carrito si existe
    actualizarContadorCarrito();
}

// Función para mostrar notificación de producto agregado
function mostrarNotificacionProducto(mensaje) {
    // Crear notificación
    const notificacion = document.createElement('div');
    notificacion.className = 'notificacion-producto';
    notificacion.innerHTML = `
        <i class="fas fa-check-circle"></i>
        <span>${mensaje}</span>
    `;
    
    // Agregar estilos
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        z-index: 2000;
        animation: slideInRight 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: bold;
        font-family: Arial, sans-serif;
    `;
    
    // Agregar animación
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    // Agregar al DOM
    document.body.appendChild(notificacion);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        notificacion.remove();
    }, 3000);
}

// Función para actualizar contador del carrito en la navegación
function actualizarContadorCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
    
    // Buscar enlace del carrito en la navegación
    const carritoLink = document.querySelector('a[href="carrito.html"]');
    if (carritoLink) {
        // Crear o actualizar contador
        let contador = carritoLink.querySelector('.cart-counter');
        if (!contador) {
            contador = document.createElement('span');
            contador.className = 'cart-counter';
            contador.style.cssText = `
                position: absolute;
                top: -8px;
                right: -8px;
                background: #ef4444;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.8rem;
                font-weight: bold;
                font-family: Arial, sans-serif;
            `;
            carritoLink.style.position = 'relative';
            carritoLink.appendChild(contador);
        }
        
        if (totalItems > 0) {
            contador.textContent = totalItems;
            contador.style.display = 'flex';
        } else {
            contador.style.display = 'none';
        }
    }
}

// Función para obtener el total del carrito
function obtenerTotalCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    return carrito.reduce((total, item) => total + (item.precio * item.cantidad), 0);
}

// Función para obtener la cantidad de productos en el carrito
function obtenerCantidadCarrito() {
    const carrito = JSON.parse(localStorage.getItem('carrito') || '[]');
    return carrito.reduce((total, item) => total + item.cantidad, 0);
}

// Inicializar contador del carrito cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    actualizarContadorCarrito();
});

// Función para ir al carrito
function irAlCarrito() {
    window.location.href = 'carrito.html';
}

// Función para limpiar el carrito
function limpiarCarrito() {
    if (confirm('¿Estás seguro de que quieres vaciar el carrito?')) {
        localStorage.removeItem('carrito');
        actualizarContadorCarrito();
        alert('Carrito vaciado');
    }
} 