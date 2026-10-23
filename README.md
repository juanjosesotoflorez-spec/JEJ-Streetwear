# JEJ-STREETWEAR

<p align="center">
  <img src="https://capsule-render.vercel.app/api?type=waving&color=0:0F0F12,50:8B0000,100:FF2D2D&height=220&section=header&text=JEJ-STREETWEAR&fontSize=52&fontColor=ffffff&animation=fadeIn&fontAlignY=38&desc=URBAN%20FASHION%20%7C%20STYLE%20%7C%20IDENTITY&descAlignY=60&descSize=16" width="100%" alt="JEJ STREETWEAR banner" />
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Proyecto-Jej%20Streetwear-111111?style=for-the-badge&logo=github&logoColor=white" alt="Proyecto" />
  <img src="https://img.shields.io/badge/Estado-En%20desarrollo-red?style=for-the-badge" alt="Estado" />
  <img src="https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-8%2FMariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
</p>

## 🌐 Sitio web

> Enlace de la demo o página web: https://tu-sitio-web.com

JEJ-STREETWEAR es una tienda virtual de moda urbana inspirada en la estética streetwear, diseñada para mostrar productos, gestionar inventario básico y ofrecer una experiencia de compra demo con autenticación, carrito y administración.

## ✨ Características principales

- Catálogo de productos con filtros, búsqueda y paginación.
- Registro e inicio de sesión para clientes y administradores.
- Carrito de compras y flujo de checkout de demostración.
- Panel administrativo para gestión de productos y pedidos.
- Secciones de contacto, catálogo, detalle de producto y cuenta de usuario.
- Base de datos MySQL con esquema listo para XAMPP o hosting compatible.
- Diseño responsive con Bootstrap y estilos propios.
- Protección básica de sesiones, consultas preparadas y control de acceso.

## 🧩 Stack tecnológico

- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- PHP 8+
- MySQL / MariaDB
- XAMPP
- Git & GitHub

## 🗂️ Estructura del proyecto

```text
jej-streetwear_nuevo/
├── admin/                 # Panel administrativo
├── assets/                # CSS, imágenes, uploads y recursos front-end
│   ├── css/
│   ├── img/
│   ├── js/
│   └── uploads/
├── auth/                  # Login, registro y cierre de sesión
├── config/                # Configuración de base de datos
├── database/              # SQL de inicialización
├── docs/                  # Documentación adicional
├── includes/              # Header, footer, funciones y seguridad
├── pages/                 # Catálogo, carrito, checkout y contacto
├── scripts/               # Utilidades y tareas de mantenimiento
├── .env.example           # Variables de entorno de ejemplo
├── index.html             # Página principal
├── README.md              # Documentación del proyecto
└── ...
```

## 🚀 Requisitos

- Windows + XAMPP
- PHP 8.1 o superior
- MySQL 8 / MariaDB
- Navegador web moderno

## 🛠️ Instalación local

1. Clona el repositorio:

```bash
git clone https://github.com/TU_USUARIO/JEJ-STREETWEAR.git
cd JEJ-STREETWEAR
```

2. Crea la base de datos en phpMyAdmin con el nombre `jej_streetwear`.

3. Importa el archivo SQL ubicado en `database/jej_streetwear.sql`.

4. Configura las variables de entorno del proyecto. Puedes basarte en el archivo `.env.example`:

```bash
copy .env.example .env
```

Luego ajusta los valores:

```env
JEJ_DB_HOST=127.0.0.1
JEJ_DB_NAME=jej_streetwear
JEJ_DB_USER=root
JEJ_DB_PASSWORD=
```

5. Copia la carpeta dentro de `C:\xampp\htdocs\` y accede a:

```text
http://localhost/jej-streetwear_nuevo/
```

6. Si se requiere crear el primer administrador, ejecuta:

```powershell
C:\xampp\php\php.exe scripts\create_admin.php
```

## 🔐 Seguridad importante

- No subas credenciales reales a GitHub.
- Usa variables de entorno o un archivo `.env` local no versionado.
- Mantén `assets/uploads/` protegido con `.htaccess`.
- Los datos de conexión remota deben configurarse en el entorno del hosting, no en el código fuente.

## 📦 Publicación en GitHub

1. Inicializa el repositorio si aún no lo has hecho:

```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/JEJ-STREETWEAR.git
git push -u origin main
```

2. Asegúrate de que archivos sensibles estén ignorados en `.gitignore`.
3. Mantén el proyecto con una estructura ordenada, documentación clara y un README profesional.

## 📌 Nota

Este proyecto está orientado a fines educativos y demostrativos. Las funciones de pago son simuladas y no procesan pagos reales.

## 🙌 Autor

Desarrollado como proyecto de aprendizaje y práctica de desarrollo web con PHP y MySQL.

---

<p align="center">
  <strong>JEJ-STREETWEAR</strong>
  <br />
  <em>Streetwear with attitude · Style with identity</em>
</p>
