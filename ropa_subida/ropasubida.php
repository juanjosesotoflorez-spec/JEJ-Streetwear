<?php
session_start();
require_once __DIR__ . '/../php/config/db.php';

// Obtener categoría para filtrar
$categoria = isset($_GET['categoria']) && $_GET['categoria'] !== '' ? $conn->real_escape_string($_GET['categoria']) : null;

if ($categoria) {
    $sql = "SELECT * FROM productos WHERE categoria = '" . $categoria . "' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM productos ORDER BY id DESC";
}

$result = $conn->query($sql);

// Obtener lista de categorias existentes
$catRes = $conn->query("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria <> ''");
$categorias = [];
if ($catRes) {
    while ($c = $catRes->fetch_assoc()) { $categorias[] = $c['categoria']; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JE&J Streetwear - Diseños Personalizados</title>
  <link rel="stylesheet" href="css/estilo6.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <header>
    <h1><i class="fas fa-tshirt"></i> JE&J Streetwear</h1>
    <p>Diseños personalizados y ropa de segunda mano</p>
    <div class="cart-icon" onclick="mostrarCarrito()">
      <i class="fas fa-shopping-cart"></i>
      <span class="cart-count" id="cartCount">0</span>
    </div>
  </header>

  <nav>
    <div class="nav-container">
      <a href="../index.html"><i class="fas fa-home"></i> Inicio</a>
      <a href="realidad_aumentada_2/index.html"><i class="fas fa-palette"></i> Crear Diseño</a>
      <a href="ropa_segunda.php"><i class="fas fa-recycle"></i> Segunda Mano</a>
      <a href="../contacto.html"><i class="fas fa-envelope"></i> Contacto</a>
    </div>
  </nav>

  <div class="hero">
    <div class="hero-content">
      <h2>Crea Tu Estilo Único</h2>
      <p>Diseña, personaliza y vende tu ropa con la mejor calidad</p>
      <a href="personalizar_ropa.html" class="cta-button"><i class="fas fa-palette"></i> CREAR ESTILO </a>
    </div>
  </div>

  <section class="filtros">
    <button onclick="location.href='ropasubida.php'" class="filtro-btn">Todas</button>
    <?php foreach($categorias as $cat): ?>
      <button onclick="location.href='ropasubida.php?categoria=' + encodeURIComponent('<?= htmlspecialchars($cat, ENT_QUOTES) ?>')" class="filtro-btn"><?= htmlspecialchars($cat) ?></button>
    <?php endforeach; ?>
  </section>

  <section class="productos">
    <?php if ($result && $result->num_rows): ?>
      <?php while($p = $result->fetch_assoc()): ?>
        <div class="producto" id="producto-<?= $p['id'] ?>">
          <div class="producto__imagen">
            <?php $img = !empty($p['imagen']) ? '../' . $p['imagen'] : '../imagenes/placeholder.svg'; ?>
            <img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" loading="lazy">
          </div>
          <div class="producto-info">
            <h3><?= htmlspecialchars($p['nombre']) ?></h3>
            <p class="precio">$<?= number_format($p['precio'],2) ?></p>
            <p><?= htmlspecialchars($p['descripcion']) ?></p>
          </div>
          <div class="producto-overlay">
            <div class="producto-buttons">
              <button class="producto-btn btn-detalles" onclick="mostrarDetalles('<?= htmlspecialchars(addslashes($p['nombre'])) ?>', <?= $p['precio'] ?>, '<?= htmlspecialchars(addslashes($p['descripcion'])) ?>', 'producto-<?= $p['id'] ?>')">
                <i class="fas fa-eye"></i> Ver Detalles
              </button>
              <button class="producto-btn btn-editar" onclick="editarProducto('<?= htmlspecialchars(addslashes($p['nombre'])) ?>')">
                <i class="fas fa-edit"></i> Personalizar
              </button>
              <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
                <button class="producto-btn btn-borrar" onclick="confirmDelete(<?= $p['id'] ?>)"><i class="fas fa-trash"></i> Eliminar</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No hay productos disponibles.</p>
    <?php endif; ?>
  </section>

  <div id="modal" class="modal">...existing code...</div>
  <div id="cartModal" class="modal">...existing code...</div>

  <footer>...existing code...</footer>

  <script>
    function confirmDelete(id){
      if (!confirm('¿Eliminar este producto? Esta acción no se puede deshacer.')) return;
      fetch('../php/admin/producto_borrar.php?id=' + id, { method: 'POST', credentials: 'same-origin' })
        .then(r => r.json())
        .then(j => {
          if (j.success) {
            const el = document.getElementById('producto-' + id);
            if (el) el.remove();
            alert('Producto eliminado');
          } else {
            alert('Error: ' + (j.error || 'No se pudo eliminar'));
          }
        })
        .catch(e => alert('Error en la solicitud'));
    }
  </script>
  <script src="javascript/ropasubida.js"></script>
</body>
</html>
