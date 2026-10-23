# JEJ-STREETWEAR

Tienda virtual demostrativa de ropa urbana y deportiva. El proyecto está pensado para ejecutarse con Apache, PHP y MySQL; el pago es una simulación educativa y no procesa dinero.

## Funcionalidades

- Portada responsive, catálogo con filtros, búsqueda, ordenamiento y paginación.
- Registro, inicio/cierre de sesión y roles cliente/administrador.
- Productos con categorías, imágenes validadas y existencias por talla.
- Carrito respaldado por sesión y precios consultados siempre desde MySQL.
- Checkout transaccional con resultados de pago de demostración aprobado, rechazado o pendiente.
- Panel administrativo con métricas del negocio, gráficos de ventas y estados de pedidos, productos más vendidos y actividad reciente.
- Cuenta personal para cada cliente con sus propios pedidos, compras y accesos a la tienda.
- Gestión administrativa de productos, pedidos y pagos simulados.
- Formulario de contacto que guarda mensajes en la base de datos.
- Protección CSRF, consultas preparadas, salida HTML escapada y control de acceso.

## Tecnologías

HTML5, CSS3, Bootstrap 5 (CDN), JavaScript nativo, PHP con MySQLi y MySQL. No se requieren Node.js, Composer ni otros gestores de dependencias. Bootstrap y la tipografía externa requieren acceso a Internet; la interfaz propia sigue cargando si esos servicios no están disponibles, aunque con apariencia reducida.

## Estructura

```text
admin/                  Módulos privados de administración
assets/css/             Estilos
assets/img/             Recursos gráficos propios
assets/js/              JavaScript del navegador
assets/uploads/         Imágenes subidas (no se versionan)
auth/                   Registro, acceso y salida
config/                 Conexión MySQLi
database/               Esquema SQL y datos de muestra
docs/                  Guías del proyecto; no se suben a htdocs
includes/               Seguridad, funciones y layout compartido
pages/                  Catálogo, carrito, checkout y contacto
scripts/create_admin.php Creación inicial del administrador desde CLI
```

## Requisitos locales

- XAMPP para Windows (Apache y MySQL/MariaDB).
- PHP 8.1 o superior recomendado, con extensiones `mysqli`, `fileinfo` y `mbstring`.
- MySQL 8 o MariaDB compatible con `CHECK`, InnoDB y `utf8mb4`.

## Instalar en XAMPP

1. Copia la carpeta del proyecto dentro de `C:\xampp\htdocs\jej-streetwear_nuevo` (o elige otro nombre y conserva esa ruta al abrir el sitio).
2. Inicia Apache y MySQL desde el panel XAMPP.
3. Abre phpMyAdmin en `http://localhost/phpmyadmin` e importa `database/jej_streetwear.sql`. El script crea `jej_streetwear`, las tablas, categorías, tallas y productos de demostración.
4. La conexión local predeterminada usa `127.0.0.1`, base `jej_streetwear`, usuario `root` y contraseña vacía. Si tu instalación es distinta, configura antes de iniciar Apache las variables `JEJ_DB_HOST`, `JEJ_DB_NAME`, `JEJ_DB_USER` y `JEJ_DB_PASSWORD`, o cambia los valores de desarrollo de `config/db.php`. No subas credenciales privadas al repositorio.
5. Crea el primer administrador desde PowerShell o CMD, en la carpeta del proyecto:

   ```powershell
   C:\xampp\php\php.exe scripts\create_admin.php
   ```

   Introduce correo, nombre y una contraseña de al menos 10 caracteres. El administrador se guarda con `password_hash()`; no existe una cuenta ni contraseña predeterminada.
6. Abre `http://localhost/jej-streetwear_nuevo/` en el navegador. Para probar las funciones PHP usa Apache, no abras los archivos con `file://`.
7. Registra una cuenta cliente para probar el carrito y el checkout. Los resultados aprobado/rechazado/pendiente son ficticios. No introduzcas información de tarjetas.

## Configuración de imágenes

Las imágenes de productos son opcionales. El sistema admite JPG, PNG y WebP con límite de 3 MB, verifica MIME en el servidor y asigna un nombre aleatorio. La carpeta `assets/uploads/` no debe permitir ejecutar scripts; el `.htaccess` incluido lo bloquea en Apache. No retires esa protección al publicar.

## Publicar en hosting compatible

Como alternativa gratuita para una práctica, **InfinityFree** anuncia en su [sitio oficial](https://www.infinityfree.com/) alojamiento gratuito con PHP y MySQL y subdominios gratuitos. La disponibilidad, límites, versiones y políticas del servicio pueden cambiar: consulta sus condiciones actuales antes de crear la cuenta. Este proyecto requiere PHP con MySQLi y las extensiones `fileinfo` y `mbstring`, una base MySQL/MariaDB InnoDB y permisos de escritura en `assets/uploads/`. Si un plan no ofrece esas funciones, no será compatible.

Pasos generales (los nombres de menús pueden variar según el proveedor):

1. Crea una cuenta de hosting y un subdominio; confirma que el plan permita PHP/MySQL y subir archivos por FTP o administrador de archivos.
2. En el panel crea una base de datos y un usuario. Anota host, nombre, usuario y contraseña que el proveedor entrega; normalmente el nombre y usuario incluyen un prefijo.
3. Importa `database/jej_streetwear.sql` desde phpMyAdmin del hosting. Si el panel no permite `CREATE DATABASE` o `USE`, crea la base primero y adapta esas dos primeras instrucciones del SQL antes de importarlo dentro de la base ya seleccionada.
4. Configura `JEJ_DB_HOST`, `JEJ_DB_NAME`, `JEJ_DB_USER` y `JEJ_DB_PASSWORD` según las opciones del hosting. Si no permite variables de entorno, coloca los valores privados de producción en `config/db.php` después de subir el proyecto y evita publicarlos en Git.
5. Abre la carpeta del proyecto en tu computador y sube **todo lo que está dentro**, no la carpeta `jej-streetwear_nuevo` como una carpeta adicional. `index.html` debe quedar directamente en el directorio web público del hosting (a menudo `htdocs`, `public_html` o `domains/tu-dominio/htdocs`); junto a él deben quedar las carpetas `admin`, `assets`, `auth`, `config`, `database`, `includes`, `pages` y `scripts`. Las páginas públicas están organizadas dentro de `pages/`.
6. Mantén los `.htaccess` y concede escritura a `assets/uploads/` solo si el panel lo requiere. Usa permisos mínimos; no hagas la carpeta completa escribible por todo el mundo.
7. Desde una terminal con PHP CLI disponible, ejecuta `php scripts/create_admin.php` para crear el administrador. Si el plan no permite CLI, no habilites un registro público de administradores: crea un hash local con `password_hash()` y usa una inserción única por phpMyAdmin, retirando cualquier script temporal inmediatamente.
8. Prueba la portada, CSS e imágenes y luego el catálogo, registro, acceso, carrito, checkout de prueba y contacto. No compartas credenciales ni introduzcas datos reales de pago.

### Solución de problemas al publicar

- **403:** comprueba que `index.html` esté en la raíz web y que el proveedor permita `.htaccess`; revisa permisos de directorio y archivos sin abrir la escritura públicamente.
- **404:** verifica que no se haya subido la carpeta contenedora como un nivel adicional, que los nombres y mayúsculas coincidan y que los enlaces apunten a archivos existentes.
- **Error de conexión:** contrasta host, nombre, usuario, contraseña y permisos de la base remota; `localhost` no siempre es el host MySQL de producción.
- **CSS o imágenes ausentes:** revisa rutas relativas y que se haya subido `assets/` completo. Evita cambiar `app_url()` si el proyecto vive en la raíz o una subcarpeta.
- **Error 500 al subir imágenes:** verifica que `fileinfo` esté activo, aumenta `upload_max_filesize`/`post_max_size` si el proveedor lo permite y confirma que `assets/uploads/` sea escribible. No retires la regla que bloquea la ejecución de scripts.
- **Límites del hosting:** algunos servicios gratuitos restringen conexiones salientes, tareas en segundo plano o límites de almacenamiento. Esta tienda no necesita pagos reales ni servicios externos del servidor; si faltan PHP/MySQL o las extensiones indicadas, usa otro plan que sí las incluya.

## Seguridad y alcance

- No se crea administrador predeterminado. El alta inicial es únicamente mediante el script CLI.
- Los pagos son simulados y no se solicitan ni guardan números de tarjeta, CVV o claves.
- La aplicación es una base educativa; antes de usarla comercialmente hace falta revisión de seguridad, privacidad, políticas de datos, copias de seguridad y pruebas con el hosting definitivo.
