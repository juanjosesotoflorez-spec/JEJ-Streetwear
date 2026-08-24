let selectedItem = null;
        let currentDesign = {
            hat: null,
            shirt: null,
            pants: null,
            shoes: null,
            colors: {}
        };

        function seleccionarItem(tipo, item) {
            // Remover selección previa
            document.querySelectorAll('.item-option').forEach(opt => opt.classList.remove('selected'));
            
            // Seleccionar nuevo item
            event.currentTarget.classList.add('selected');
            
            // Actualizar preview
            const elemento = document.getElementById(tipo);
            elemento.style.backgroundImage = `url(imagenes/${tipo}s/${item}.png)`;
            selectedItem = tipo;
            
            currentDesign[tipo] = item;
        }

        function cambiarColor(color) {
            if (!selectedItem) return;
            
            // Remover selección previa
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
            
            // Seleccionar nuevo color
            event.currentTarget.classList.add('selected');
            
            // Aplicar color
            const elemento = document.getElementById(selectedItem);
            elemento.style.filter = `hue-rotate(${getHueRotation(color)}deg)`;
            
            currentDesign.colors[selectedItem] = color;
        }

        function getHueRotation(color) {
            // Convertir color a HSL y calcular rotación
            // Esta es una implementación simplificada
            return Math.random() * 360; // Para demostración
        }

        function guardarDiseno() {
            // Aquí puedes implementar la lógica para guardar el diseño
            console.log('Diseño guardado:', currentDesign);
            alert('¡Diseño guardado con éxito!');
        }

        function resetearDiseno() {
            // Limpiar todas las selecciones
            document.querySelectorAll('.clothing-item').forEach(item => {
                item.style.backgroundImage = '';
                item.style.filter = '';
            });
            
            document.querySelectorAll('.item-option, .color-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            selectedItem = null;
            currentDesign = {
                hat: null,
                shirt: null,
                pants: null,
                shoes: null,
                colors: {}
            };
        }