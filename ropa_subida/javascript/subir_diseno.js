let canvas = document.getElementById('designCanvas');
        let ctx = canvas.getContext('2d');
        let currentColor = '#000000';
        let selectedElement = null;

        // Configurar canvas
        function initCanvas() {
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }

        // Cargar imagen
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            let reader = new FileReader();
            reader.onload = function(event) {
                let img = new Image();
                img.onload = function() {
                    initCanvas();
                    // Calcular proporciones para mantener aspecto
                    let ratio = Math.min(canvas.width / img.width, canvas.height / img.height);
                    let newWidth = img.width * ratio;
                    let newHeight = img.height * ratio;
                    let x = (canvas.width - newWidth) / 2;
                    let y = (canvas.height - newHeight) / 2;
                    
                    ctx.drawImage(img, x, y, newWidth, newHeight);
                }
                img.src = event.target.result;
            }
            reader.readAsDataURL(e.target.files[0]);
        });

        function cambiarColor(color) {
            currentColor = color;
            if (selectedElement) {
                // Aplicar color al elemento seleccionado
            }
        }

        function agregarTexto() {
            let texto = document.querySelector('.text-input').value;
            let fuente = document.querySelector('.font-selector').value;
            let tamaño = document.querySelector('.size-input').value;
            
            ctx.font = `${tamaño}px ${fuente}`;
            ctx.fillStyle = currentColor;
            ctx.fillText(texto, 50, 50);
        }

        function aplicarPatron(patron) {
            let img = new Image();
            img.onload = function() {
                let pattern = ctx.createPattern(img, 'repeat');
                ctx.fillStyle = pattern;
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
            img.src = `patrones/${patron}.png`;
        }

        function guardarDiseno() {
            let imagen = canvas.toDataURL('image/png');
            // Aquí puedes implementar la lógica para guardar el diseño
            alert('¡Diseño guardado con éxito!');
        }

        function previsualizarDiseno() {
            let ventanaPreview = window.open();
            ventanaPreview.document.write('<img src="' + canvas.toDataURL() + '" style="max-width: 100%;">');
        }

        // Inicializar canvas al cargar
        window.onload = initCanvas;