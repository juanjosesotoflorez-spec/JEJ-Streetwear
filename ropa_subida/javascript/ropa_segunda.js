// Vista previa de imágenes
const fileInput = document.querySelector('input[type="file"]');
const preview = document.getElementById('imagePreview');

fileInput.addEventListener('change', () => {
  preview.innerHTML = "";
  [...fileInput.files].forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const img = document.createElement("img");
      img.src = e.target.result;
      preview.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});

// Función para publicar la prenda
function subirPrenda() {
  const inputs = document.querySelectorAll('.form-input');
  const titulo = inputs[0].value;
  const precio = inputs[1].value;
  const estado = inputs[2].value;
  const descripcion = inputs[3].value;
  const categoria = inputs[4].value;
  const imagenes = fileInput.files;

  if (!titulo || !precio || !descripcion || imagenes.length === 0) {
    alert("Por favor, completa todos los campos.");
    return;
  }

  // Crear tarjeta del producto
  const card = document.createElement("div");
  card.classList.add("product-card");

  const reader = new FileReader();
  reader.onload = e => {
    card.innerHTML = `
      <img src="${e.target.result}" alt="${titulo}" class="product-image">
      <div class="product-info">
        <h3 class="product-title">${titulo}</h3>
        <p class="product-price">${precio} €</p>
        <p class="product-description">${descripcion}</p>
        <div class="product-meta">
          <span><i class="fas fa-tag"></i> ${categoria}</span>
          <span><i class="fas fa-star"></i> ${estado}</span>
        </div>
      </div>
    `;
    document.querySelector(".products-grid").appendChild(card);
  };
  reader.readAsDataURL(imagenes[0]);

  // Resetear formulario
  document.getElementById("uploadForm").reset();
  preview.innerHTML = "";
}
