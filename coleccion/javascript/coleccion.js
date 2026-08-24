// ===== VARIABLES GLOBALES =====
let carrito = [];
let productos = [];

// ===== FUNCIONES PRINCIPALES =====

// Función para inicializar la página
function inicializarPagina() {
    console.log('🚀 Inicializando página de colección...');
    
    // Cargar productos desde el DOM
    cargarProductos();
    
    // Configurar event listeners
    configurarEventListeners();
    
    // Cargar carrito desde localStorage
    cargarCarrito();
    
    // Mostrar mensaje de bienvenida
    mostrarMensaje('¡Bienvenido a nuestra colección!', 'success');
}

// Función para cargar productos desde el DOM
function cargarProductos() {
    const productosElements = document.querySelectorAll('.producto');
    productosElements.forEach((elemento, index) => {
        const producto = {
            id: index + 1,
            elemento: elemento,
            categoria: elemento.dataset.categoria,
            titulo: elemento.querySelector('.producto__titulo').textContent,
            precio: parseFloat(elemento.querySelector('.precio__actual').textContent.replace('$', '').replace(',', '')),
            imagen: elemento.querySelector('img').src,
            descripcion: elemento.querySelector('.producto__descripcion').textContent
        };
        productos.push(producto);
    });
    
    console.log(`📦 ${productos.length} productos cargados`);
}

// Función para configurar event listeners
function configurarEventListeners() {
    // Filtros
    const filtros = document.querySelectorAll('.filtro-btn');
    filtros.forEach(filtro => {
        filtro.addEventListener('click', () => filtrarProductos(filtro.dataset.filtro));
    });
    
    // Botones de compra
    const botonesComprar = document.querySelectorAll('.btn--comprar');
    botonesComprar.forEach((boton, index) => {
        boton.addEventListener('click', () => agregarAlCarrito(productos[index]));
    });
    
    // Navegación
    const navLinks = document.querySelectorAll('.nav__link');
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            navLinks.forEach(l => l.classList.remove('nav__link--active'));
            link.classList.add('nav__link--active');
        });
    });
}

// ===== FUNCIONES DE FILTRADO =====

// Función para filtrar productos
function filtrarProductos(categoria) {
    console.log(`🔍 Filtrando por: ${categoria}`);
    
    // Actualizar botones de filtro
    actualizarBotonesFiltro(categoria);
    
    // Obtener todos los productos
    const productosElements = document.querySelectorAll('.producto');
    
    productosElements.forEach(producto => {
        const categoriaProducto = producto.dataset.categoria;
        
        if (categoria === 'todos' || categoriaProducto === categoria) {
            mostrarProducto(producto);
        } else {
            ocultarProducto(producto);
        }
    });
    
    // Mostrar mensaje de filtro aplicado
    const mensaje = categoria === 'todos' ? 'Mostrando todos los productos' : `Mostrando productos: ${categoria}`;
    mostrarMensaje(mensaje, 'info');
}

// Función para actualizar botones de filtro
function actualizarBotonesFiltro(categoriaActiva) {
    const filtros = document.querySelectorAll('.filtro-btn');
    filtros.forEach(filtro => {
        filtro.classList.remove('filtro-btn--activo');
        if (filtro.dataset.filtro === categoriaActiva) {
            filtro.classList.add('filtro-btn--activo');
        }
    });
}

// Función para mostrar producto con animación
function mostrarProducto(producto) {
    producto.style.display = 'block';
    producto.style.animation = 'fadeInUp 0.6s ease-out';
}

// Función para ocultar producto
function ocultarProducto(producto) {
    producto.style.display = 'none';
}

// ===== FUNCIONES DEL CARRITO =====

// Función para agregar producto al carrito
function agregarAlCarrito(producto) {
    console.log(`🛒 Agregando al carrito: ${producto.titulo}`);
    
    // Buscar si el producto ya está en el carrito
    const productoExistente = carrito.find(item => item.id === producto.id);
    
    if (productoExistente) {
        productoExistente.cantidad += 1;
        mostrarMensaje(`Cantidad actualizada: ${producto.titulo}`, 'info');
    } else {
        carrito.push({
            ...producto,
            cantidad: 1
        });
        mostrarMensaje(`✅ ${producto.titulo} agregado al carrito`, 'success');
    }
    
    // Guardar carrito en localStorage
    guardarCarrito();
    
    // Actualizar contador del carrito
    actualizarContadorCarrito();
    
    // Animación del botón
    animarBotonComprar(producto.id);
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

// Función para actualizar contador del carrito
function actualizarContadorCarrito() {
    const totalItems = carrito.reduce((total, item) => total + item.cantidad, 0);
    
    // Crear o actualizar contador en el header
    let contador = document.querySelector('.carrito-contador');
    if (!contador) {
        contador = document.createElement('span');
        contador.className = 'carrito-contador';
        document.querySelector('.header__titulo').appendChild(contador);
    }
    
    if (totalItems > 0) {
        contador.textContent = `🛒 ${totalItems}`;
        contador.style.display = 'inline-block';
    } else {
        contador.style.display = 'none';
    }
}

// ===== FUNCIONES DE ANIMACIÓN =====

// Función para animar botón de compra
function animarBotonComprar(productoId) {
    const boton = document.querySelector(`[data-producto-id="${productoId}"]`) || 
                  document.querySelectorAll('.btn--comprar')[productoId - 1];
    
    if (boton) {
        boton.style.transform = 'scale(1.1)';
        boton.style.backgroundColor = '#4CAF50';
        boton.textContent = '¡Agregado!';
        
        setTimeout(() => {
            boton.style.transform = 'scale(1)';
            boton.style.backgroundColor = '';
            boton.textContent = 'Agregar al Carrito';
        }, 1000);
    }
}

// ===== FUNCIONES DE UTILIDAD =====

// Función para mostrar mensajes
function mostrarMensaje(texto, tipo = 'info') {
    // Crear elemento de mensaje
    const mensaje = document.createElement('div');
    mensaje.className = `mensaje mensaje--${tipo}`;
    mensaje.textContent = texto;
    
    // Estilos del mensaje
    mensaje.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        z-index: 1000;
        animation: slideInRight 0.3s ease-out;
        max-width: 300px;
    `;
    
    // Colores según tipo
    const colores = {
        success: '#4CAF50',
        error: '#f44336',
        warning: '#ff9800',
        info: '#2196F3'
    };
    
    mensaje.style.backgroundColor = colores[tipo] || colores.info;
    
    // Agregar al DOM
    document.body.appendChild(mensaje);
    
    // Remover después de 3 segundos
    setTimeout(() => {
        mensaje.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (mensaje.parentNode) {
                mensaje.parentNode.removeChild(mensaje);
            }
        }, 300);
    }, 3000);
}

// Función para formatear precio
function formatearPrecio(precio) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP'
    }).format(precio);
}

// Función para calcular total del carrito
function calcularTotalCarrito() {
    return carrito.reduce((total, item) => total + (item.precio * item.cantidad), 0);
}

// ===== FUNCIONES DE BÚSQUEDA =====

// Función para buscar productos (futura implementación)
function buscarProductos(termino) {
    console.log(`🔍 Buscando: ${termino}`);
    
    const productosElements = document.querySelectorAll('.producto');
    
    productosElements.forEach(producto => {
        const titulo = producto.querySelector('.producto__titulo').textContent.toLowerCase();
        const descripcion = producto.querySelector('.producto__descripcion').textContent.toLowerCase();
        const terminoBusqueda = termino.toLowerCase();
        
        if (titulo.includes(terminoBusqueda) || descripcion.includes(terminoBusqueda)) {
            mostrarProducto(producto);
        } else {
            ocultarProducto(producto);
        }
    });
}

// ===== FUNCIONES DE RESPONSIVE =====

// Función para manejar cambios de tamaño de ventana
function manejarResize() {
    const ancho = window.innerWidth;
    
    if (ancho <= 768) {
        console.log('📱 Modo móvil activado');
        // Ajustes específicos para móvil
    } else if (ancho <= 1024) {
        console.log('📱 Modo tablet activado');
        // Ajustes específicos para tablet
    } else {
        console.log('💻 Modo desktop activado');
        // Ajustes específicos para desktop
    }
}

// ===== EVENT LISTENERS GLOBALES =====

// Event listener para resize
window.addEventListener('resize', manejarResize);

// Event listener para scroll (para efectos de parallax futuros)
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const parallax = document.querySelector('.header');
    
    if (parallax) {
        const speed = scrolled * 0.5;
        parallax.style.transform = `translateY(${speed}px)`;
    }
});

// ===== INICIALIZACIÓN =====

// Esperar a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', inicializarPagina);

// Función para limpiar carrito (útil para testing)
function limpiarCarrito() {
    carrito = [];
    guardarCarrito();
    actualizarContadorCarrito();
    mostrarMensaje('Carrito limpiado', 'info');
}

// Función para mostrar carrito (futura implementación)
function mostrarCarrito() {
    if (carrito.length === 0) {
        mostrarMensaje('Tu carrito está vacío', 'warning');
        return;
    }
    
    const total = calcularTotalCarrito();
    const mensaje = `Carrito: ${carrito.length} productos - Total: ${formatearPrecio(total)}`;
    mostrarMensaje(mensaje, 'info');
}

// Exportar funciones para uso global (si es necesario)
window.ColeccionApp = {
    agregarAlCarrito,
    filtrarProductos,
    buscarProductos,
    limpiarCarrito,
    mostrarCarrito,
    carrito
};

console.log('🎯 Script de colección cargado correctamente'); 