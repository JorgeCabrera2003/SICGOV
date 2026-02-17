# 📋 README - SISTEMA GOOD VIBES TAPAS & BAR

## 📌 Tabla de Contenidos
1. [Descripción del Proyecto](#-descripción-del-proyecto)
2. [Tecnologías Utilizadas](#-tecnologías-utilizadas)
3. [Estructura del Proyecto](#-estructura-del-proyecto)
4. [Requisitos Previos](#-requisitos-previos)
5. [Instalación](#-instalación)
6. [Configuración](#-configuración)
7. [Base de Datos](#-base-de-datos)
8. [Estructura de Carpetas](#-estructura-de-carpetas)
9. [Nomenclatura y Convenciones](#-nomenclatura-y-convenciones)
10. [Comandos Útiles](#-comandos-útiles)
11. [Flujo de Trabajo con Git](#-flujo-de-trabajo-con-git)
12. [Módulos del Sistema](#-módulos-del-sistema)
13. [Guía de Estilos](#-guía-de-estilos)
14. [Solución de Problemas Comunes](#-solución-de-problemas-comunes)
15. [Contribución](#-contribución)

---

## 🎯 Descripción del Proyecto

**Good Vibes Tapas & Bar** es un sistema de información complementario diseñado para optimizar la administración de un restaurante temático de los años 80s y 90s. El sistema complementa las funciones administrativas faltantes del sistema principal de ventas (Eco Logical), ofreciendo:

- **Gestión de Personal**: Control de asistencia, horarios, turnos y permisos
- **Gestión de Inventario**: Control de existencias, alertas de reposición
- **Menú Inteligente**: Catálogo digital, QR por mesa, personalización de platillos
- **Control Financiero**: Registro de ingresos/egresos, cálculo de ganancias
- **Gestión de Clientes**: Historial, sistema de puntos por lealtad
- **Reservaciones**: Control de mesas y espacios

---

## 🛠 Tecnologías Utilizadas

### Backend
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| PHP | 8.2+ | Lenguaje principal (tipado estricto) |
| MySQL/MariaDB | 8.0/10.6 | Base de datos relacional |
| PDO | - | Extensión para conexiones seguras |

### Frontend
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| HTML5 | - | Estructura semántica |
| CSS3 | - | Estilos y diseño responsivo |
| JavaScript | ES6+ | Lógica del lado del cliente |
| Bootstrap | 5.3 | Framework UI |
| Font Awesome | 6.4 | Iconografía |
| Chart.js | 4.0 | Gráficos y reportes |
| jQuery | 3.6 | Peticiones AJAX |
| SweetAlert2 | 11 | Alertas personalizadas |
| DataTables | 1.13 | Tablas dinámicas |
| Select2 | 4.1 | Selectores avanzados |

### Herramientas de Desarrollo
| Herramienta | Versión | Uso |
|-------------|---------|-----|
| XAMPP | 8.2+ | Entorno de desarrollo local |
| Composer | 2.5+ | Gestor de dependencias PHP |
| Git | 2.40+ | Control de versiones |
| FakerPHP | 1.23+ | Generación de datos de prueba |

---

## 📁 Estructura del Proyecto

```
good-vibes/
├── app/
│   ├── Controllers/          # Controladores MVC
│   │   ├── LoginController.php
│   │   ├── DashboardController.php
│   │   ├── ProductoController.php
│   │   └── CategoriaController.php
│   ├── Models/
│   │   ├── Security/         # Modelos BD Seguridad
│   │   │   ├── Usuario.php
│   │   │   ├── Bitacora.php
│   │   │   └── LoginSettings.php
│   │   └── System/            # Modelos BD Sistema
│   │       ├── Producto.php
│   │       ├── CategoriaProducto.php
│   │       └── Empleado.php
│   ├── Core/
│   │   ├── Database.php       # Singleton multi-conexión
│   │   └── Router.php         # Enrutador (opcional)
│   └── Helpers/
│       └── Helper.php         # Funciones auxiliares
├── config/
│   └── database.php           # Configuración de BD
├── database/
│   ├── migrations/            # Archivos SQL de estructura
│   │   ├── goobv-usuarios.sql
│   │   └── goobv-sistema.sql
│   ├── seeders/               # Pobladores de datos
│   │   ├── SecuritySeeder.php
│   │   └── BusinessSeeder.php
│   └── setup.php              # Instalador automático
├── public/
│   ├── index.php              # Punto de entrada único
│   ├── logout.php             # Cierre de sesión
│   ├── .htaccess              # Reescribir URLs
│   └── assets/
│       ├── css/
│       │   ├── main.css
│       │   ├── style.css
│       │   └── temas/
│       ├── js/
│       │   ├── main.js
│       │   └── utils.js
│       ├── img/
│       ├── bootstrap/
│       ├── DataTables/
│       └── Select2/
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── head.php
│       │   ├── menu.php
│       │   └── footer.php
│       ├── auth/
│       │   └── login.php
│       └── productos/
│           └── index.php
├── vendor/                     # Dependencias Composer
├── .env                        # Variables de entorno
├── .gitignore
├── composer.json               # Dependencias PHP
└── README.md                   # Este archivo
```

---

## 📋 Requisitos Previos

- **XAMPP** 8.2+ (Apache + MySQL + PHP)
- **Composer** 2.5+
- **Git** 2.40+
- **Navegador** moderno (Chrome, Firefox, Edge)
- **Editor de código** (VS Code, PHPStorm, Sublime)

---

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-repo/good-vibes.git
cd good-vibes
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
Crear archivo `.env` en la raíz:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME_SYSTEM=goobv-sistema
DB_NAME_USER=goobv-usuarios
```

### 4. Configurar base de datos
Ejecutar el instalador automático:
```bash
php database/setup.php
```

### 5. Configurar Apache
Asegurar que `mod_rewrite` está activado en XAMPP:
- Abrir `httpd.conf`
- Descomentar: `LoadModule rewrite_module modules/mod_rewrite.so`

### 6. Acceder al sistema
```
http://localhost/good-vibes/public/
```

**Credenciales por defecto:**
- Usuario: `admin_root` o cédula del SQL original
- Contraseña: `1234`

---

## ⚙️ Configuración

### Archivo `.env`
| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| DB_HOST | Host de la base de datos | localhost |
| DB_USER | Usuario MySQL | root |
| DB_PASS | Contraseña MySQL | (vacío) |
| DB_NAME_SYSTEM | BD del negocio | goobv-sistema |
| DB_NAME_USER | BD de seguridad | goobv-usuarios |

### Archivo `config/database.php`
```php
<?php
return [
    'security' => [
        'host' => $_ENV['DB_HOST'],
        'db' => $_ENV['DB_NAME_USER'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS']
    ],
    'business' => [
        'host' => $_ENV['DB_HOST'],
        'db' => $_ENV['DB_NAME_SYSTEM'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS']
    ]
];
```

---

## 💾 Base de Datos

### Conexiones (Singleton Multi-BD)
```php
// BD de seguridad (usuarios, roles, bitácora)
$dbSecurity = Database::getConnection('security');

// BD del negocio (productos, pedidos, inventario)
$dbBusiness = Database::getConnection('business');
```

### Migraciones
Los archivos SQL deben:
- ❌ **NO** incluir `CREATE DATABASE` ni `USE`
- ✅ Empezar directamente con `CREATE TABLE`
- ✅ Incluir `INSERT` para datos base
- ✅ Usar `IF NOT EXISTS` para evitar errores

### Seeders
Los seeders usan **FakerPHP** para datos de prueba:
```bash
# Ejecutar instalación completa
php database/setup.php
```

---

## 📂 Estructura de Carpetas

### Reglas PSR-4
```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "App\\Database\\Seeders\\": "database/seeders/"
        }
    }
}
```

### Namespaces
- Controladores: `namespace App\Controllers;`
- Modelos (Seguridad): `namespace App\Models\Security;`
- Modelos (Sistema): `namespace App\Models\System;`
- Helpers: `namespace App\Helpers;`
- Seeders: `namespace App\Database\Seeders;`

---

## 📝 Nomenclatura y Convenciones

### Archivos y Carpetas
| Tipo | Convención | Ejemplo |
|------|------------|---------|
| Clases | PascalCase | `ProductoController.php` |
| Modelos | PascalCase (singular) | `Usuario.php` |
| Vistas | snake_case | `perfil_usuario.php` |
| Tablas | minúsculas, plural | `usuarios`, `productos` |
| Métodos | camelCase | `obtenerMenu()` |
| Variables | camelCase | `$nombreProducto` |
| Constantes | MAYÚSCULAS | `BASE_URL` |
| IDs BD | prefijo + timestamp | `PROD20250217123456` |

### Estructura de Commits
```
[TIPO]: Descripción breve (≤50 caracteres)

Cuerpo opcional con explicación detallada
- Qué se cambió
- Por qué se cambió
- Impacto en otros módulos

Referencias: #issue, PR relacionado
```

### Tipos de Commit
| Tag | Descripción |
|-----|-------------|
| FEAT | Nueva funcionalidad |
| FIX | Corrección de bug |
| DOCS | Documentación |
| STYLE | Estilos/CSS |
| REFACTOR | Refactorización |
| PERF | Mejora de rendimiento |
| TEST | Pruebas |
| CONFIG | Configuraciones |
| CHORE | Mantenimiento |
| REMOVE | Eliminación de código |
| MOVE | Movimiento/renombrado |
| DEPS | Dependencias |
| MERGE | Fusiones |

### Ramas Git
| Rama | Propósito |
|------|-----------|
| `main` | Código estable para producción |
| `develop` | Integración de funcionalidades |
| `feature/[modulo]-[desc]` | Nueva característica |
| `bugfix/[issue]-[desc]` | Corrección de bugs |
| `hotfix/[desc]` | Corrección urgente |
| `release/v*.*.*` | Preparación de versión |

---

## 🖥 Comandos Útiles

### Composer
```bash
# Instalar dependencias
composer install

# Actualizar autoload
composer dump-autoload

# Agregar nueva dependencia
composer require vendor/paquete

# Actualizar dependencias
composer update
```

### Base de Datos
```bash
# Instalar/Resetear base de datos
php database/setup.php

# (Recomendado) Limpiar todo y reinstalar
# El script ya incluye DROP DATABASE IF EXISTS
```

### Git
```bash
# Crear rama de feature
git checkout develop
git checkout -b feature/nuevo-modulo

# Commit con mensaje estándar
git add .
git commit -m "FEAT: Agregar módulo de inventario"

# Sincronizar con develop
git checkout develop
git pull origin develop
git checkout feature/nuevo-modulo
git rebase develop

# Merge a develop (vía Pull Request)
git push origin feature/nuevo-modulo
```

---

## 🔄 Flujo de Trabajo con Git

### 1. Desarrollo Normal
```bash
# Desde develop
git checkout develop
git pull origin develop
git checkout -b feature/mi-feature

# Trabajar y commitear
git add .
git commit -m "FEAT: Descripción"

# Sincronizar
git checkout develop
git pull origin develop
git checkout feature/mi-feature
git rebase develop

# Subir y crear Pull Request
git push origin feature/mi-feature
```

### 2. Hotfix (Urgente)
```bash
# Desde main
git checkout main
git checkout -b hotfix/error-critico

# Corregir y commitear
git add .
git commit -m "FIX: Error crítico en login"

# Merge a main
git checkout main
git merge hotfix/error-critico
git push origin main

# También a develop
git checkout develop
git merge hotfix/error-critico
git push origin develop
```

---

## 🧩 Módulos del Sistema

### 1. Autenticación y Seguridad
- Login con reCAPTCHA
- Registro de bitácora
- Cierre de sesión seguro
- Roles y permisos

### 2. Gestión de Personal
- Registro de empleados
- Marcación de asistencia
- Control de horarios y turnos
- Reportes de personal

### 3. Gestión de Inventario
- Control de existencias
- Historial de movimientos
- Alertas de reposición
- Cálculo automático de compras

### 4. Menú Inteligente
- Catálogo digital de platillos
- Filtros por categoría
- Personalización de ingredientes
- QR por mesa

### 5. Control Financiero
- Ingresos y egresos
- Cálculo de ganancias
- Reportes detallados

### 6. Gestión de Clientes
- Historial de clientes
- Sistema de puntos por lealtad
- Reservaciones de mesas

---

## 🎨 Guía de Estilos

### Paleta de Colores (60-30-10)

| Porcentaje | Uso | Color | Código |
|------------|-----|-------|--------|
| **60%** | Fondo principal | Gris muy claro | `#F4F7F6` |
| **60%** | Tarjetas | Blanco | `#FFFFFF` |
| **30%** | Sidebar/Navegación | Negro mate | `#1A1C20` |
| **10%** | Acento (botones, hover) | Amarillo vibrante | `#FFD600` |

### Tipografía
```css
font-family: 'Poppins', sans-serif;
```

### Botones
```css
.btn-primary {
    background-color: #FFD600;
    border-color: #FFD600;
    color: #1A1C20;
    font-weight: 600;
}

.btn-primary:hover {
    background-color: #E6C000;
    color: #1A1C20;
}
```

### Tarjetas
```css
.card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-3px);
}
```

### Menú Activo
```css
.menu-item.active a {
    border-left: 4px solid #FFD600;
    color: #FFD600;
}
```

---

## 🔧 Solución de Problemas Comunes

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Unknown column"
Verificar nombres de columnas en BD vs modelo SQL:
```sql
DESCRIBE nombre_tabla;
```

### Error: "Field doesn't have a default value"
Generar ID manualmente (para campos VARCHAR como PK):
```php
$this->id_producto = $this->generarIdProducto();
```

### Error: reCAPTCHA no funciona
Activar extensión GD en `php.ini`:
```ini
extension=gd
```

### Error: 404 en assets
Verificar rutas en `head.php`:
```php
<link href="<?php echo BASE_URL; ?>/assets/css/style.css">
```

### Error: "Invalid JSON response" en DataTables
Verificar que el controlador devuelve:
```php
echo json_encode(['data' => $data]);
```

### Error: Sesión no persiste
Configurar sesiones seguras:
```php
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);
```

---

## 👥 Contribución

### Buenas Prácticas

1. **Un commit = un cambio lógico**
2. **Mensajes en presente** ("Agrega" no "Agregué")
3. **Línea de asunto ≤ 50 caracteres**
4. **Cuerpo explica el QUÉ y POR QUÉ**
5. **Referenciar issues** (`Closes #123`)
6. **Commit temprano, commit frecuente**
7. **Usar Guard Clauses** (validar y salir rápido)
8. **Siempre usar PDO con prepared statements**
9. **Usar Singleton para conexiones BD**
10. **Nunca incluir variables directamente en SQL**

### Tags Específicos Sugeridos

| Tag | Ámbito |
|-----|--------|
| MENU | Cambios en menú interactivo |
| FINANCE | Módulo financiero |
| STAFF | Gestión de personal |
| INVENTORY | Control de inventario |
| KITCHEN | Optimización de cocina |
| VIBES | Elementos temáticos retro |

### Ejemplo de Commit
```
FEAT: Sistema de alertas para reposición de inventario

- Implementar notificaciones por stock bajo
- Configurar umbrales personalizados por producto
- Agregar panel de alertas pendientes

Closes #123
```

### Ejemplo de Pull Request
```
## Descripción
Agrega módulo de inventario con alertas automáticas

## Cambios realizados
- [x] Modelo Inventario con PDO
- [x] Controlador con listado JSON
- [x] Vista con DataTables
- [x] Sistema de alertas por stock mínimo

## Cómo probar
1. Ejecutar `php database/setup.php`
2. Ir a `/inventario`
3. Modificar stock por debajo del mínimo

## Issues relacionados
Closes #123, Relates to #45

## Screenshots
[imagen aquí]
```

---

## 📚 Documentación Adicional

- [PHP Manual](https://www.php.net/manual/es/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.3/)
- [DataTables Manual](https://datatables.net/manual/)
- [FakerPHP Docs](https://fakerphp.github.io/)
- [Font Awesome Icons](https://fontawesome.com/icons)

---

## 📞 Contacto

**Equipo de Desarrollo:**
- Jorge Cabrera - 31.843.937
- Leizer Torrealba - 30.266.398
- Mariangel Bokor - 30.587.785
- Santiago Coello - 28.165.452
- Abrahan Rodriguez - 30.405.566

**Repositorio:** [https://github.com/tu-repo/good-vibes](https://github.com/tu-repo/good-vibes)

---

## 📅 Versión

**Versión Actual:** 1.0.0  
**Fecha:** Febrero 2026  
**Estado:** Desarrollo Activo

---

*¡Good Vibes! 🌟 Un sistema con onda retro para un restaurante con mucha energía.*