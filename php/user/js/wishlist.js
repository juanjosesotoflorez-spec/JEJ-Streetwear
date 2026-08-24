// wishlist.js
async function agregarAWishlist(producto) {
  try{
    // Enviar como form-data simple (sin JSON)
  const fd = new FormData();
  fd.append('product_id', producto.product_id || producto.id || 0);
  fd.append('nombre', producto.nombre || producto.name || '');

    const resp = await fetch('/PROYECTO_2/php/user/wishlist_agregar_simple.php', {
      method: 'POST',
      body: fd
    });

    const text = (await resp.text()).trim();
    // Respuestas esperadas: OK | EXISTS | AUTH | BADID | ERROR:...
    if (resp.status === 401 || text === 'AUTH'){
      showToast('Debes iniciar sesión para usar la wishlist', 'warn');
      setTimeout(()=> window.location.href = '/PROYECTO_2/php/auth/login.php', 900);
      return;
    }

    if (text === 'OK') {
      showToast('✅ Producto agregado a tu wishlist.', 'success');
      // incrementar badge
      try{
        const anchor = document.querySelector('a[href="wishlist.php"], a[href$="/wishlist.php"]');
        if(anchor){
          let badge = anchor.querySelector('.badge');
          if(!badge){ badge = document.createElement('span'); badge.className = 'badge'; anchor.appendChild(badge); }
          const current = parseInt(badge.textContent) || 0; badge.textContent = current + 1;
        }
  }catch(e){ console.warn('badge update failed', e); }

      // notificar otras pestañas
      try{
        if ('BroadcastChannel' in window){
          const ch = new BroadcastChannel('wishlist_channel');
          ch.postMessage({ type: 'added', product_id: producto.product_id || producto.id || null, nombre: producto.nombre || '' });
          ch.close();
        } else {
          localStorage.setItem('wishlist_added', JSON.stringify({ ts: Date.now(), product_id: producto.product_id || producto.id || null, nombre: producto.nombre || '' }));
        }
      }catch(e){ console.warn('notify failed', e); }

    } else if (text === 'EXISTS') {
      showToast('⚠️ Este producto ya está en tu wishlist.', 'info');
    } else if (text === 'BADID') {
      showToast('⚠️ ID de producto inválido.', 'warn');
    } else if (text === 'NOPRODUCT') {
      showToast('⚠️ Producto no encontrado en la base de datos (ID inválido).', 'warn');
    } else if (text.startsWith('ERROR')) {
      showToast('❌ Error interno: ' + text, 'error');
      console.error('Server error:', text);
    } else {
      showToast('❌ Respuesta inesperada del servidor: ' + text, 'error');
      console.warn('Unexpected response:', resp.status, text);
    }

  }catch(err){
    console.error('Error al agregar a wishlist:', err);
    showToast('❌ Error al agregar a wishlist', 'error');
  }
}

// --- Toasts simples in-page (se inyectan estilos la primera vez) ---
function ensureToastContainer(){
  if(document.getElementById('wj-toasts')) return;
  const style = document.createElement('style');
  style.textContent = `
    #wj-toasts{position:fixed;right:16px;bottom:16px;z-index:99999;display:flex;flex-direction:column;gap:10px;}
    .wj-toast{min-width:220px;padding:10px 14px;border-radius:10px;color:#fff;font-weight:600;box-shadow:0 8px 20px rgba(0,0,0,0.4);opacity:0;transform:translateY(6px);transition:all .28s ease}
    .wj-toast.show{opacity:1;transform:translateY(0)}
    .wj-toast.success{background:linear-gradient(90deg,#28a745,#2ecc71)}
    .wj-toast.info{background:linear-gradient(90deg,#17a2b8,#3ac5d6)}
    .wj-toast.warn{background:linear-gradient(90deg,#ffb347,#ff7a00);color:#111}
    .wj-toast.error{background:linear-gradient(90deg,#ff5e62,#ff1e1e)}
  `;
  document.head.appendChild(style);
  const container = document.createElement('div'); container.id='wj-toasts'; document.body.appendChild(container);
}

function showToast(msg, type='info', ttl=3000){
  ensureToastContainer();
  const c = document.getElementById('wj-toasts');
  const d = document.createElement('div'); d.className = 'wj-toast ' + type; d.textContent = msg;
  c.appendChild(d);
  // force layout
  void d.offsetWidth; d.classList.add('show');
  setTimeout(()=>{ d.classList.remove('show'); setTimeout(()=>d.remove(),300); }, ttl);
}
