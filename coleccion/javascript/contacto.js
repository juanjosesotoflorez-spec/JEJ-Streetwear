// ===== VARIABLES GLOBALES =====
let formularioEnviado = false;

// ===== FUNCIONES PRINCIPALES =====

// Función para inicializar la página
function inicializarPagina() {
    console.log('📞 Inicializando página de contacto...');
    
    // Configurar event listeners
    configurarEventListeners();
    
    // Mostrar mensaje de bienvenida
    mostrarMensaje('¡Bienvenido! Estamos aquí para ayudarte', 'success');
    
    // Inicializar animaciones
    inicializarAnimaciones();
}

// Función para configurar event listeners
function configurarEventListeners() {
    // Formulario de contacto
    const formulario = document.getElementById('contactForm');
    if (formulario) {
        formulario.addEventListener('submit', manejarEnvioFormulario);
    }
    
    // Validación en tiempo real
    const inputs = document.querySelectorAll('.formulario__input, .formulario__textarea, .formulario__select');
    inputs.forEach(input => {
        input.addEventListener('blur', validarCampo);
        input.addEventListener('input', limpiarError);
    });
    
    // Enlaces de redes sociales
    const enlacesRedes = document.querySelectorAll('.red-social__enlace');
    enlacesRedes.forEach(enlace => {
        enlace.addEventListener('click', (e) => {
            console.log(`🔗 Abriendo: ${enlace.href}`);
        });
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

// ===== FUNCIONES DEL FORMULARIO =====

// Función para manejar el envío del formulario
function manejarEnvioFormulario(e) {
    e.preventDefault();
    
    if (formularioEnviado) {
        mostrarMensaje('Ya has enviado un mensaje. Te responderemos pronto.', 'warning');
        return;
    }
    
    console.log('📝 Procesando formulario de contacto...');
    
    // Validar todos los campos
    const camposValidos = validarFormulario();
    
    if (!camposValidos) {
        mostrarMensaje('Por favor, completa todos los campos requeridos correctamente.', 'error');
        return;
    }
    
    // Obtener datos del formulario
    const datosFormulario = obtenerDatosFormulario();
    
    // Enviar al servidor via fetch/FormData
    enviarFormularioServidor(datosFormulario, e.target);
}

// Función para validar el formulario completo
function validarFormulario() {
    const campos = [
        { id: 'nombre', tipo: 'texto' },
        { id: 'email', tipo: 'email' },
        { id: 'asunto', tipo: 'select' },
        { id: 'mensaje', tipo: 'texto' }
    ];
    
    let valido = true;
    
    campos.forEach(campo => {
        const elemento = document.getElementById(campo.id);
        if (!validarCampoEspecifico(elemento, campo.tipo)) {
            valido = false;
        }
    });
    
    return valido;
}

// Función para validar un campo específico
function validarCampoEspecifico(elemento, tipo) {
    const valor = elemento.value.trim();
    
    switch (tipo) {
        case 'texto':
            if (valor.length < 3) {
                mostrarError(elemento, 'Este campo debe tener al menos 3 caracteres');
                return false;
            }
            break;
            
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(valor)) {
                mostrarError(elemento, 'Por favor, ingresa un email válido');
                return false;
            }
            break;
            
        case 'select':
            if (valor === '') {
                mostrarError(elemento, 'Por favor, selecciona una opción');
                return false;
            }
            break;
    }
    
    limpiarError(elemento);
    return true;
}

// Función para validar campo individual
function validarCampo(e) {
    const elemento = e.target;
    const tipo = elemento.type || 'texto';
    
    validarCampoEspecifico(elemento, tipo);
}

// Función para mostrar error en campo
function mostrarError(elemento, mensaje) {
    // Limpiar errores previos
    limpiarError(elemento);
    
    // Crear elemento de error
    const errorElement = document.createElement('div');
    errorElement.className = 'formulario__error';
    errorElement.textContent = mensaje;
    errorElement.style.cssText = `
        color: #f44336;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        animation: fadeIn 0.3s ease-out;
    `;
    
    // Agregar clase de error al input
    elemento.classList.add('formulario__input--error');
    elemento.style.borderColor = '#f44336';
    
    // Insertar mensaje de error
    elemento.parentNode.appendChild(errorElement);
}

// Función para limpiar error
function limpiarError(elemento) {
    const errorElement = elemento.parentNode.querySelector('.formulario__error');
    if (errorElement) {
        errorElement.remove();
    }
    
    elemento.classList.remove('formulario__input--error');
    elemento.style.borderColor = '';
}

// Función para obtener datos del formulario
function obtenerDatosFormulario() {
    const formData = new FormData(document.getElementById('contactForm'));
    const datos = {};
    
    for (let [key, value] of formData.entries()) {
        datos[key] = value;
    }
    
    // Agregar timestamp
    datos.timestamp = new Date().toISOString();
    datos.userAgent = navigator.userAgent;
    
    return datos;
}

// Función para simular envío
async function enviarFormularioServidor(datos, formEl) {
    const botonEnviar = formEl.querySelector('.btn--enviar');
    const textoOriginal = botonEnviar ? botonEnviar.innerHTML : 'Enviando...';
    if (botonEnviar) { botonEnviar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...'; botonEnviar.disabled = true; }

    try {
+        const fd = new FormData(formEl);
+        // Asegurar campo asunto si no existe (algunas versiones usan 'asunto' fijo)
+        if (!fd.has('asunto')) fd.append('asunto', 'contacto_web');
+
            const resp = await fetch('/PROYECTO_2/php/contacto_submit.php', { method: 'POST', body: fd });
            const text = await resp.text();
            if (resp.ok && text.trim() === 'OK') {
                formularioEnviado = true;
                mostrarMensaje('¡Mensaje enviado exitosamente! Te responderemos pronto.', 'success');
                formEl.reset();
                localStorage.setItem('formularioEnviado', 'true');
                localStorage.setItem('timestampEnvio', Date.now().toString());
                // Notificar otras pestañas
                try { new BroadcastChannel('contactos').postMessage({ type: 'nuevo_contacto' }); } catch(e) { localStorage.setItem('contactos_nuevo', Date.now().toString()); }

                // Verificar si el mensaje ya aparece en el dashboard del usuario
                try {
                    const r2 = await fetch('/PROYECTO_2/php/user/get_contactos.php');
                    if (r2.status === 401) {
                        if (confirm('Para que el mensaje aparezca en tu dashboard debes iniciar sesión. ¿Quieres abrir la página de login ahora?')) {
                            window.open('/PROYECTO_2/php/auth/login.php', '_blank');
                        }
                    } else if (r2.ok) {
                        const data = await r2.json();
                        const sentEmail = fd.get('email') || '';
                        const sentMsg = fd.get('mensaje') || '';
                        const found = Array.isArray(data) && data.some(c => c.email === sentEmail && c.mensaje && c.mensaje.includes(sentMsg.substring(0,20)) );
                        if (found) console.info('Tu mensaje ya está en tu dashboard.');
                        else console.info('Mensaje enviado, pero no aparece aún en tu dashboard. Si estás logueado, recarga sugerencias.php.');
                    }
                } catch(e) { /* ignore */ }

            } else {
                console.error('Error al enviar contacto:', text);
                mostrarMensaje('Error al enviar el mensaje. Intenta de nuevo más tarde.', 'error');
            }
        } catch (err) {
            console.error('Network error sending contact:', err);
            mostrarMensaje('Error de red al enviar el mensaje.', 'error');
        } finally {
            if (botonEnviar) { botonEnviar.innerHTML = textoOriginal; botonEnviar.disabled = false; }
        }
+}
+

// ===== FUNCIONES DE ANIMACIÓN =====

// Función para inicializar animaciones
function inicializarAnimaciones() {
    // Animación de entrada para elementos
    const elementos = document.querySelectorAll('.red-social, .info__card, .formulario');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease-out';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    elementos.forEach(elemento => {
        observer.observe(elemento);
    });
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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
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
    
    // Remover después de 4 segundos
    setTimeout(() => {
        mensaje.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (mensaje.parentNode) {
                mensaje.parentNode.removeChild(mensaje);
            }
        }, 300);
    }, 4000);
}

// Función para copiar al portapapeles
function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        mostrarMensaje('Copiado al portapapeles', 'success');
    }).catch(() => {
        mostrarMensaje('Error al copiar', 'error');
    });
}

// Función para formatear teléfono
function formatearTelefono(telefono) {
    return telefono.replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3');
}

// ===== FUNCIONES DE REDES SOCIALES =====

// Función para abrir WhatsApp
function abrirWhatsApp() {
    const numero = '573001234567';
    const mensaje = 'Hola, me gustaría obtener más información sobre sus productos.';
    const url = `https://wa.me/${numero}?text=${encodeURIComponent(mensaje)}`;
    window.open(url, '_blank');
}

// Función para abrir email
function abrirEmail() {
    const email = 'info@lusso.com';
    const asunto = 'Consulta desde la web';
    const mensaje = 'Hola, me gustaría obtener más información.';
    const url = `mailto:${email}?subject=${encodeURIComponent(asunto)}&body=${encodeURIComponent(mensaje)}`;
    window.open(url);
}

// ===== FUNCIONES DE VALIDACIÓN DE SESIÓN =====

// Función para verificar si ya se envió un formulario recientemente
function verificarEnvioReciente() {
    const enviado = localStorage.getItem('formularioEnviado');
    const timestamp = localStorage.getItem('timestampEnvio');
    
    if (enviado && timestamp) {
        const tiempoTranscurrido = Date.now() - parseInt(timestamp);
        const unaHora = 60 * 60 * 1000; // 1 hora en milisegundos
        
        if (tiempoTranscurrido < unaHora) {
            formularioEnviado = true;
            mostrarMensaje('Ya has enviado un mensaje recientemente. Te responderemos pronto.', 'info');
        } else {
            // Limpiar datos antiguos
            localStorage.removeItem('formularioEnviado');
            localStorage.removeItem('timestampEnvio');
        }
    }
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

// Event listener para scroll (para efectos de parallax)
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
document.addEventListener('DOMContentLoaded', () => {
    inicializarPagina();
    verificarEnvioReciente();
});

// Función para limpiar datos de formulario (útil para testing)
function limpiarDatosFormulario() {
    localStorage.removeItem('formularioEnviado');
    localStorage.removeItem('timestampEnvio');
    formularioEnviado = false;
    mostrarMensaje('Datos de formulario limpiados', 'info');
}

// Función para mostrar información de contacto
function mostrarInfoContacto() {
    const info = `
        📧 Email: info@lusso.com
        📞 Teléfono: +57 300 123 4567
        🕒 Horarios: Lunes a Sábado 9AM - 8PM
        🌐 Tienda online 24/7
    `;
    console.log('📋 Información de contacto:', info);
    mostrarMensaje('Información mostrada en consola', 'info');
}

// Exportar funciones para uso global
window.ContactoApp = {
    abrirWhatsApp,
    abrirEmail,
    copiarAlPortapapeles,
    limpiarDatosFormulario,
    mostrarInfoContacto,
    formularioEnviado
};

console.log('📞 Script de contacto cargado correctamente'); 