-- =====================================================================
-- JEJ-STREETWEAR: Base de datos para Hosting Remoto (InfinityFree / cPanel)
-- NOTA: Sin 'CREATE DATABASE' ni 'USE' para compatibilidad con hosting gratuito.
-- =====================================================================

CREATE TABLE IF NOT EXISTS usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(190) NOT NULL UNIQUE,
  telefono VARCHAR(30) NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  activa TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS productos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  categoria_id INT UNSIGNED NOT NULL,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NOT NULL,
  precio DECIMAL(12,2) NOT NULL,
  imagen VARCHAR(255) NOT NULL DEFAULT 'assets/img/product-placeholder.svg',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_productos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id),
  INDEX idx_productos_activo_categoria (activo, categoria_id),
  INDEX idx_productos_nombre (nombre),
  CONSTRAINT chk_producto_precio CHECK (precio >= 0)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tallas (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS producto_tallas (
  producto_id BIGINT UNSIGNED NOT NULL,
  talla_id INT UNSIGNED NOT NULL,
  existencias INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (producto_id, talla_id),
  CONSTRAINT fk_producto_tallas_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
  CONSTRAINT fk_producto_tallas_talla FOREIGN KEY (talla_id) REFERENCES tallas(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pedidos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id BIGINT UNSIGNED NOT NULL,
  nombre_envio VARCHAR(120) NOT NULL,
  correo_envio VARCHAR(190) NOT NULL,
  telefono_envio VARCHAR(30) NULL,
  direccion VARCHAR(255) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  total DECIMAL(12,2) NOT NULL,
  estado ENUM('pendiente','pagado','rechazado','preparando','enviado','cancelado') NOT NULL DEFAULT 'pendiente',
  inventario_descontado TINYINT(1) NOT NULL DEFAULT 0,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
  INDEX idx_pedidos_usuario (usuario_id, creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS detalle_pedido (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  producto_id BIGINT UNSIGNED NOT NULL,
  talla_id INT UNSIGNED NOT NULL,
  nombre_producto VARCHAR(150) NOT NULL,
  nombre_talla VARCHAR(20) NOT NULL,
  cantidad INT UNSIGNED NOT NULL,
  precio_unitario DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id),
  CONSTRAINT fk_detalle_talla FOREIGN KEY (talla_id) REFERENCES tallas(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS pagos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  metodo VARCHAR(40) NOT NULL DEFAULT 'simulacion_educativa',
  resultado ENUM('aprobado','rechazado','pendiente') NOT NULL,
  referencia VARCHAR(40) NOT NULL UNIQUE,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_pagos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  INDEX idx_pagos_resultado (resultado, creado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mensajes_contacto (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  correo VARCHAR(190) NOT NULL,
  asunto VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categorías oficiales
INSERT IGNORE INTO categorias (nombre) VALUES
  ('Camisetas'), ('Sudaderas'), ('Pantalones'), ('Zapatillas'), ('Chaquetas'), ('Accesorios');

-- Tallas oficiales
INSERT IGNORE INTO tallas (nombre) VALUES
  ('XS'), ('S'), ('M'), ('L'), ('XL'), ('XXL'), ('Única'), ('38'), ('39'), ('40'), ('41'), ('42'), ('43');

-- Inserción de productos del Catálogo Jordan (si no existen)
INSERT INTO productos (categoria_id, nombre, descripcion, precio, imagen, activo)
SELECT c.id, p.nombre, p.descripcion, p.precio, p.imagen, p.activo
FROM (
  SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Brooklyn Sneaker Graphic' AS nombre, 'Camiseta urbana de corte holgado en algodon premium de densidad media con grafico serigrafiado inspirado en la cultura sneaker.' AS descripcion, 149900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-brooklyn-sneaker-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Brooklyn ''85 Classic' AS nombre, 'Rinde homenaje a la herencia del 85 con esta camiseta de tejido suave, cuello acanalado y estetica retro de Chicago.' AS descripcion, 139900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-brooklyn-85-classic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Dri-FIT Sport Graphic' AS nombre, 'Disenada para entrenar en la cancha o salir a la calle, con tecnologia absorbente de sudor Nike Dri-FIT y ajuste atletico.' AS descripcion, 159900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-dri-fit-sport-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Dri-FIT Performance Crew' AS nombre, 'Tejido transpirable de secado rapido con corte estandar que brinda frescura durante todo el dia y graficos Jumpman iconicos.' AS descripcion, 149900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-dri-fit-performance.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Fireside Boxy Graphic' AS nombre, 'Silueta moderna de corte relajado boxy con estampado audaz en el pecho y tacto ultrasuave para tus outfits diarios.' AS descripcion, 139900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-fireside-graphic-tee.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Manga Larga Jordan Brooklyn Jersey' AS nombre, 'Camiseta manga larga inspirada en las camisetas de calentamiento de basquetbol, con estampados alusivos a MJ.' AS descripcion, 179900 AS precio, 'assets/img/products/camisetas/camiseta-manga-larga-jordan-brooklyn-jersey.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Brooklyn Seasonal Fleece' AS nombre, 'Tejido Fleece cepillado de maxima calidez en el interior, bolsillo de canguro y capucha estructurada con cordon de ajuste.' AS descripcion, 299900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-brooklyn-seasonal-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera con Capucha Jordan Fireside' AS nombre, 'Comodidad de nivel superior con detalles de costuras reforzadas, acabado afelpado y logotipo Jumpman en relieve.' AS descripcion, 319900 AS precio, 'assets/img/products/sudaderas/sudadera-capucha-jordan-fireside-hoodie.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Hoodie Jordan Fierce & Fearless' AS nombre, 'Sudadera urbana pesada de corte holgado con diseno tipografico frontal y punos elasticos acanalados de alta durabilidad.' AS descripcion, 329900 AS precio, 'assets/img/products/sudaderas/hoodie-jordan-fierce-fearless.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Brooklyn Statement Pullover' AS nombre, 'Sudadera de edicion Statement confeccionada con tejido French Terry grueso para un look sofisticado y relajado.' AS descripcion, 349900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-brooklyn-statement-pullover.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Dri-FIT Full-Zip Hoops' AS nombre, 'Chaqueta/sudadera con cierre completo, tecnologia que absorbe el sudor y bolsillos con cremallera para guardar lo esencial.' AS descripcion, 359900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-dri-fit-full-zip-hoops.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Holiday All-Over Print' AS nombre, 'Estampado completo con patrones geometricos y simbolos legendarios de Jordan, interior suave y corte espacioso.' AS descripcion, 339900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-holiday-all-over-print.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jogger Jordan Brooklyn Fleece Cuffed' AS nombre, 'Jogger clasico con punos acanalados en los tobillos, cintura elastica con cordon y bolsillos laterales profundos.' AS descripcion, 259900 AS precio, 'assets/img/products/pantalones/pantalon-jogger-jordan-brooklyn-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Deportivo Jordan Statement Cuff' AS nombre, 'Silueta conica moderna confeccionada en tejido grueso que mantiene su estructura en cualquier movimiento urbano.' AS descripcion, 279900 AS precio, 'assets/img/products/pantalones/pantalon-deportivo-jordan-statement-cuff.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jordan Flight Wings Fleece' AS nombre, 'Inspirado en los miticos trajes de vuelo de Michael Jordan, con paneles ergonomicos y parche tejido Flight.' AS descripcion, 289900 AS precio, 'assets/img/products/pantalones/pantalon-jordan-flight-wings-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jogger Jordan Brooklyn Classic' AS nombre, 'La prenda basica definitiva para tu rotacion de streetwear: comodo, resistente y con el logo Jumpman bordado.' AS descripcion, 249900 AS precio, 'assets/img/products/pantalones/pantalon-jogger-jordan-brooklyn-classic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Holgado Jordan Flight ''91 Baggy' AS nombre, 'Corte amplio de estilo noventero con caida fluida en el calzado y cintura elastica regulable.' AS descripcion, 299900 AS precio, 'assets/img/products/pantalones/pantalon-holgado-jordan-flight-91-baggy.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jordan Relaxed Open-Hem Fleece' AS nombre, 'Dobladillo abierto sin elastico en la bota para lucir tus zapatillas favoritas sin interrupciones.' AS descripcion, 269900 AS precio, 'assets/img/products/pantalones/pantalon-jordan-relaxed-open-hem-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 1 Retro High OG' AS nombre, 'La silueta legendaria que comenzo todo en 1985. Cuero autentico de primera calidad, amortiguacion Air encapsulada y corte alto iconico.' AS descripcion, 799900 AS precio, 'assets/img/products/zapatillas/air-jordan-1-retro-high-og.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 12 Retro' AS nombre, 'Inspirada en la bandera japonesa del sol naciente, con placa de fibra de carbono completa y unidad Zoom Air ultra reactiva.' AS descripcion, 849900 AS precio, 'assets/img/products/zapatillas/air-jordan-12-retro.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Jordan Spizike Low' AS nombre, 'Homenaje a Spike Lee combinando elementos de los AJ3, AJ4, AJ5 y AJ6 con estampado de elefante y perfil bajo.' AS descripcion, 699900 AS precio, 'assets/img/products/zapatillas/jordan-spizike-low.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 5 Retro OG' AS nombre, 'Silueta de los 90 con dientes de tiburon en la entresuela inspirados en aviones de combate de la Segunda Guerra Mundial.' AS descripcion, 829900 AS precio, 'assets/img/products/zapatillas/air-jordan-5-retro-og.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 1 Low Special Edition' AS nombre, 'La version low de corte limpio con combinacion de materiales texturizados y estetica versatil para uso diario.' AS descripcion, 599900 AS precio, 'assets/img/products/zapatillas/air-jordan-1-low-se.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 9 Retro Boot NRG' AS nombre, 'Construccion todoterreno resistente al agua con suela de traccion agresiva y acabados metalicos premium.' AS descripcion, 869900 AS precio, 'assets/img/products/zapatillas/air-jordan-9-retro-boot-nrg.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Brooklyn Relaxed Jacket' AS nombre, 'Chaqueta ligera con cremallera completa, tejido de tafetan suave repelente al agua y forro interior transpirable.' AS descripcion, 389900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-brooklyn-relaxed.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Acolchada Jordan Brooklyn Puffer' AS nombre, 'Aislamiento termico sintetico de alto rendimiento que mantiene el calor sin anadir peso excesivo en climas frios.' AS descripcion, 529900 AS precio, 'assets/img/products/chaquetas/chaqueta-acolchada-jordan-brooklyn-puffer.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaleco Acolchado Jordan Flight Mountainside' AS nombre, 'Chaleco versatil para superposicion de capas, con cuello alto protector y multiples bolsillos seguros con cremallera.' AS descripcion, 349900 AS precio, 'assets/img/products/chaquetas/chaleco-acolchado-jordan-flight-mountainside.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Brooklyn Chore Laser' AS nombre, 'Silueta estilo chaqueta de trabajo en loneta resistente con grabado laser de motivos Jumpman iconicos.' AS descripcion, 459900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-brooklyn-chore-laser.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Cortavientos Jordan Brooklyn Graphic' AS nombre, 'Bloqueador de viento con capucha ajustable, elasticos en punos y dobladillo y detalles reflectantes.' AS descripcion, 369900 AS precio, 'assets/img/products/chaquetas/chaqueta-cortavientos-jordan-brooklyn-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Sport Jam Retro Warmup' AS nombre, 'Inspirada en las chaquetas de calentamiento de los campeonatos de los 90 con franjas en contraste.' AS descripcion, 399900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-sport-jam-retro-warmup.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Maletín Deportivo Jordan Gym Duffel' AS nombre, 'Compartimento principal espacioso de facil acceso, bolsillo lateral ventilado para zapatillas y correa acolchada ajustable.' AS descripcion, 219900 AS precio, 'assets/img/products/accesorios/maletin-deportivo-jordan-collectors-gym-duffel.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Mochila Morral Jordan Collectors Daypack' AS nombre, 'Capacidad optimizada con funda interior acolchada para laptop de hasta 15", bolsillo organizador y espaldar ergonomico.' AS descripcion, 249900 AS precio, 'assets/img/products/accesorios/mochila-morral-jordan-collectors-daypack.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Gorro Jordan Peak Beanie Cuffed' AS nombre, 'Tejido de punto acrilico suave de tacto calido con dobladillo vuelto y parche metalico Jumpman en el frente.' AS descripcion, 99900 AS precio, 'assets/img/products/accesorios/gorro-jordan-peak-beanie-cuffed.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Guantes Térmicos Jordan Fleece 2.0' AS nombre, 'Confeccionados en fleece termico con tecnologia tactil en la punta de los dedos para usar el smartphone sin quitartelos.' AS descripcion, 119900 AS precio, 'assets/img/products/accesorios/guantes-termicos-jordan-fleece-2.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Mochila Jordan Real Tree Outdoor' AS nombre, 'Diseno de camuflaje de alto impacto con cordones elasticos frontales para sujetar chaquetas o accesorios adicionales.' AS descripcion, 259900 AS precio, 'assets/img/products/accesorios/mochila-jordan-real-tree-outdoor-daypack.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Medias Jordan Everyday Elevated (Pack x6)' AS nombre, 'Seis pares de calcetines con soporte en el arco del pie, amortiguacion estrategica y tejido Dri-FIT antitranspirante.' AS descripcion, 109900 AS precio, 'assets/img/products/accesorios/medias-jordan-everyday-elevated-pack-x6.jpg' AS imagen, 1 AS activo
) p
JOIN categorias c ON c.nombre = p.categoria
WHERE NOT EXISTS (SELECT 1 FROM productos existing WHERE existing.nombre = p.nombre);

-- Actualización de productos existentes (por si ya estaban registrados)
UPDATE productos p
JOIN (
  SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Brooklyn Sneaker Graphic' AS nombre, 'Camiseta urbana de corte holgado en algodon premium de densidad media con grafico serigrafiado inspirado en la cultura sneaker.' AS descripcion, 149900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-brooklyn-sneaker-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Brooklyn ''85 Classic' AS nombre, 'Rinde homenaje a la herencia del 85 con esta camiseta de tejido suave, cuello acanalado y estetica retro de Chicago.' AS descripcion, 139900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-brooklyn-85-classic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Dri-FIT Sport Graphic' AS nombre, 'Disenada para entrenar en la cancha o salir a la calle, con tecnologia absorbente de sudor Nike Dri-FIT y ajuste atletico.' AS descripcion, 159900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-dri-fit-sport-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Dri-FIT Performance Crew' AS nombre, 'Tejido transpirable de secado rapido con corte estandar que brinda frescura durante todo el dia y graficos Jumpman iconicos.' AS descripcion, 149900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-dri-fit-performance.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Jordan Fireside Boxy Graphic' AS nombre, 'Silueta moderna de corte relajado boxy con estampado audaz en el pecho y tacto ultrasuave para tus outfits diarios.' AS descripcion, 139900 AS precio, 'assets/img/products/camisetas/camiseta-jordan-fireside-graphic-tee.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Camisetas' AS categoria, 'Camiseta Manga Larga Jordan Brooklyn Jersey' AS nombre, 'Camiseta manga larga inspirada en las camisetas de calentamiento de basquetbol, con estampados alusivos a MJ.' AS descripcion, 179900 AS precio, 'assets/img/products/camisetas/camiseta-manga-larga-jordan-brooklyn-jersey.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Brooklyn Seasonal Fleece' AS nombre, 'Tejido Fleece cepillado de maxima calidez en el interior, bolsillo de canguro y capucha estructurada con cordon de ajuste.' AS descripcion, 299900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-brooklyn-seasonal-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera con Capucha Jordan Fireside' AS nombre, 'Comodidad de nivel superior con detalles de costuras reforzadas, acabado afelpado y logotipo Jumpman en relieve.' AS descripcion, 319900 AS precio, 'assets/img/products/sudaderas/sudadera-capucha-jordan-fireside-hoodie.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Hoodie Jordan Fierce & Fearless' AS nombre, 'Sudadera urbana pesada de corte holgado con diseno tipografico frontal y punos elasticos acanalados de alta durabilidad.' AS descripcion, 329900 AS precio, 'assets/img/products/sudaderas/hoodie-jordan-fierce-fearless.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Brooklyn Statement Pullover' AS nombre, 'Sudadera de edicion Statement confeccionada con tejido French Terry grueso para un look sofisticado y relajado.' AS descripcion, 349900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-brooklyn-statement-pullover.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Dri-FIT Full-Zip Hoops' AS nombre, 'Chaqueta/sudadera con cierre completo, tecnologia que absorbe el sudor y bolsillos con cremallera para guardar lo esencial.' AS descripcion, 359900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-dri-fit-full-zip-hoops.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Sudaderas' AS categoria, 'Sudadera Jordan Holiday All-Over Print' AS nombre, 'Estampado completo con patrones geometricos y simbolos legendarios de Jordan, interior suave y corte espacioso.' AS descripcion, 339900 AS precio, 'assets/img/products/sudaderas/sudadera-jordan-holiday-all-over-print.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jogger Jordan Brooklyn Fleece Cuffed' AS nombre, 'Jogger clasico con punos acanalados en los tobillos, cintura elastica con cordon y bolsillos laterales profundos.' AS descripcion, 259900 AS precio, 'assets/img/products/pantalones/pantalon-jogger-jordan-brooklyn-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Deportivo Jordan Statement Cuff' AS nombre, 'Silueta conica moderna confeccionada en tejido grueso que mantiene su estructura en cualquier movimiento urbano.' AS descripcion, 279900 AS precio, 'assets/img/products/pantalones/pantalon-deportivo-jordan-statement-cuff.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jordan Flight Wings Fleece' AS nombre, 'Inspirado en los miticos trajes de vuelo de Michael Jordan, con paneles ergonomicos y parche tejido Flight.' AS descripcion, 289900 AS precio, 'assets/img/products/pantalones/pantalon-jordan-flight-wings-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jogger Jordan Brooklyn Classic' AS nombre, 'La prenda basica definitiva para tu rotacion de streetwear: comodo, resistente y con el logo Jumpman bordado.' AS descripcion, 249900 AS precio, 'assets/img/products/pantalones/pantalon-jogger-jordan-brooklyn-classic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Holgado Jordan Flight ''91 Baggy' AS nombre, 'Corte amplio de estilo noventero con caida fluida en el calzado y cintura elastica regulable.' AS descripcion, 299900 AS precio, 'assets/img/products/pantalones/pantalon-holgado-jordan-flight-91-baggy.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Pantalones' AS categoria, 'Pantalón Jordan Relaxed Open-Hem Fleece' AS nombre, 'Dobladillo abierto sin elastico en la bota para lucir tus zapatillas favoritas sin interrupciones.' AS descripcion, 269900 AS precio, 'assets/img/products/pantalones/pantalon-jordan-relaxed-open-hem-fleece.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 1 Retro High OG' AS nombre, 'La silueta legendaria que comenzo todo en 1985. Cuero autentico de primera calidad, amortiguacion Air encapsulada y corte alto iconico.' AS descripcion, 799900 AS precio, 'assets/img/products/zapatillas/air-jordan-1-retro-high-og.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 12 Retro' AS nombre, 'Inspirada en la bandera japonesa del sol naciente, con placa de fibra de carbono completa y unidad Zoom Air ultra reactiva.' AS descripcion, 849900 AS precio, 'assets/img/products/zapatillas/air-jordan-12-retro.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Jordan Spizike Low' AS nombre, 'Homenaje a Spike Lee combinando elementos de los AJ3, AJ4, AJ5 y AJ6 con estampado de elefante y perfil bajo.' AS descripcion, 699900 AS precio, 'assets/img/products/zapatillas/jordan-spizike-low.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 5 Retro OG' AS nombre, 'Silueta de los 90 con dientes de tiburon en la entresuela inspirados en aviones de combate de la Segunda Guerra Mundial.' AS descripcion, 829900 AS precio, 'assets/img/products/zapatillas/air-jordan-5-retro-og.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 1 Low Special Edition' AS nombre, 'La version low de corte limpio con combinacion de materiales texturizados y estetica versatil para uso diario.' AS descripcion, 599900 AS precio, 'assets/img/products/zapatillas/air-jordan-1-low-se.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Zapatillas' AS categoria, 'Air Jordan 9 Retro Boot NRG' AS nombre, 'Construccion todoterreno resistente al agua con suela de traccion agresiva y acabados metalicos premium.' AS descripcion, 869900 AS precio, 'assets/img/products/zapatillas/air-jordan-9-retro-boot-nrg.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Brooklyn Relaxed Jacket' AS nombre, 'Chaqueta ligera con cremallera completa, tejido de tafetan suave repelente al agua y forro interior transpirable.' AS descripcion, 389900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-brooklyn-relaxed.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Acolchada Jordan Brooklyn Puffer' AS nombre, 'Aislamiento termico sintetico de alto rendimiento que mantiene el calor sin anadir peso excesivo en climas frios.' AS descripcion, 529900 AS precio, 'assets/img/products/chaquetas/chaqueta-acolchada-jordan-brooklyn-puffer.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaleco Acolchado Jordan Flight Mountainside' AS nombre, 'Chaleco versatil para superposicion de capas, con cuello alto protector y multiples bolsillos seguros con cremallera.' AS descripcion, 349900 AS precio, 'assets/img/products/chaquetas/chaleco-acolchado-jordan-flight-mountainside.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Brooklyn Chore Laser' AS nombre, 'Silueta estilo chaqueta de trabajo en loneta resistente con grabado laser de motivos Jumpman iconicos.' AS descripcion, 459900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-brooklyn-chore-laser.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Cortavientos Jordan Brooklyn Graphic' AS nombre, 'Bloqueador de viento con capucha ajustable, elasticos en punos y dobladillo y detalles reflectantes.' AS descripcion, 369900 AS precio, 'assets/img/products/chaquetas/chaqueta-cortavientos-jordan-brooklyn-graphic.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Chaquetas' AS categoria, 'Chaqueta Jordan Sport Jam Retro Warmup' AS nombre, 'Inspirada en las chaquetas de calentamiento de los campeonatos de los 90 con franjas en contraste.' AS descripcion, 399900 AS precio, 'assets/img/products/chaquetas/chaqueta-jordan-sport-jam-retro-warmup.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Maletín Deportivo Jordan Gym Duffel' AS nombre, 'Compartimento principal espacioso de facil acceso, bolsillo lateral ventilado para zapatillas y correa acolchada ajustable.' AS descripcion, 219900 AS precio, 'assets/img/products/accesorios/maletin-deportivo-jordan-collectors-gym-duffel.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Mochila Morral Jordan Collectors Daypack' AS nombre, 'Capacidad optimizada con funda interior acolchada para laptop de hasta 15", bolsillo organizador y espaldar ergonomico.' AS descripcion, 249900 AS precio, 'assets/img/products/accesorios/mochila-morral-jordan-collectors-daypack.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Gorro Jordan Peak Beanie Cuffed' AS nombre, 'Tejido de punto acrilico suave de tacto calido con dobladillo vuelto y parche metalico Jumpman en el frente.' AS descripcion, 99900 AS precio, 'assets/img/products/accesorios/gorro-jordan-peak-beanie-cuffed.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Guantes Térmicos Jordan Fleece 2.0' AS nombre, 'Confeccionados en fleece termico con tecnologia tactil en la punta de los dedos para usar el smartphone sin quitartelos.' AS descripcion, 119900 AS precio, 'assets/img/products/accesorios/guantes-termicos-jordan-fleece-2.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Mochila Jordan Real Tree Outdoor' AS nombre, 'Diseno de camuflaje de alto impacto con cordones elasticos frontales para sujetar chaquetas o accesorios adicionales.' AS descripcion, 259900 AS precio, 'assets/img/products/accesorios/mochila-jordan-real-tree-outdoor-daypack.jpg' AS imagen, 1 AS activo
  UNION ALL SELECT 'Accesorios' AS categoria, 'Medias Jordan Everyday Elevated (Pack x6)' AS nombre, 'Seis pares de calcetines con soporte en el arco del pie, amortiguacion estrategica y tejido Dri-FIT antitranspirante.' AS descripcion, 109900 AS precio, 'assets/img/products/accesorios/medias-jordan-everyday-elevated-pack-x6.jpg' AS imagen, 1 AS activo
) p_new ON p.nombre = p_new.nombre
SET p.descripcion = p_new.descripcion, p.precio = p_new.precio, p.imagen = p_new.imagen, p.activo = 1;

-- Ocultar productos placeholder iniciales para que solo se muestren productos reales
UPDATE productos SET activo = 0 WHERE imagen LIKE '%placeholder%';

-- Inventario por tallas para los productos Jordan
-- 1. Zapatillas: tallas 38 a 43
INSERT IGNORE INTO producto_tallas (producto_id, talla_id, existencias)
SELECT p.id, t.id, 10
FROM productos p
JOIN categorias c ON c.id = p.categoria_id AND c.nombre = 'Zapatillas'
JOIN tallas t ON t.nombre IN ('38', '39', '40', '41', '42', '43');

-- 2. Accesorios: talla Única
INSERT IGNORE INTO producto_tallas (producto_id, talla_id, existencias)
SELECT p.id, t.id, 15
FROM productos p
JOIN categorias c ON c.id = p.categoria_id AND c.nombre = 'Accesorios'
JOIN tallas t ON t.nombre = 'Única';

-- 3. Ropa (Camisetas, Sudaderas, Pantalones, Chaquetas): tallas S, M, L, XL
INSERT IGNORE INTO producto_tallas (producto_id, talla_id, existencias)
SELECT p.id, t.id, 8
FROM productos p
JOIN categorias c ON c.id = p.categoria_id AND c.nombre IN ('Camisetas', 'Sudaderas', 'Pantalones', 'Chaquetas')
JOIN tallas t ON t.nombre IN ('S', 'M', 'L', 'XL');

-- Cuentas de acceso inicial
INSERT INTO usuarios (id, nombre, correo, password_hash, rol) VALUES
  (1, 'Administrador JEJ', 'admin@jej.com', '$2y$10$LDqB0bJX5ooNZvQ8YJvHSulkQ7LAR0yYPFvlH9bG2oArOxnTWsPey', 'admin'),
  (2, 'Cliente de Prueba', 'cliente@jej.com', '$2y$10$ruKVqHHvpSBKW6SN/VtNLOrJRCNb6RQbmFOtdeAbnJ1VstZuxCH3i', 'cliente')
ON DUPLICATE KEY UPDATE rol = VALUES(rol);
