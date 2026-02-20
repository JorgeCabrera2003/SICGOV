# 📋 **README - SICGOV (Sistema de Información Complementario Good Vibes)**

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
12. [Estrategia de Ramas](#-estrategia-de-ramas)
13. [Pull Requests y Revisión de Código](#-pull-requests-y-revisión-de-código)
14. [Módulos del Sistema](#-módulos-del-sistema)
15. [Guía de Estilos](#-guía-de-estilos)
16. [Solución de Problemas Comunes](#-solución-de-problemas-comunes)
17. [Contribución](#-contribución)

---

## 🎯 Descripción del Proyecto

**SICGOV (Sistema de Información Complementario Good Vibes)** es un sistema diseñado para optimizar la administración del restaurante temático **Good Vibes Tapas & Bar** (años 80s y 90s). El sistema complementa las funciones administrativas faltantes del sistema principal de ventas (Eco Logical), ofreciendo una solución completa y profesional.

### Objetivos Principales:
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
│       ├── productos/
│       │   ├── index.php
│       │   ├── modales/
│       │   │   ├── modal_producto.php
│       │   │   └── modal_categorias.php
│       │   ├── js/
│       │   │   └── productos.js
│       │   └── css/
│       │       └── productos.css
│       └── pedidos/           # (Estructura propuesta)
│           ├── index.php
│           ├── modales/
│           ├── js/
│           └── css/
├── vendor/                     # Dependencias Composer
├── .env                        # Variables de entorno
├── .gitignore
├── composer.json               # Dependencias PHP
├── CONTRIBUTING.md             # Guía de contribución
└── README.md                   # Este archivo
```

---

## 📋 Requisitos Previos

- **XAMPP** 8.2+ (Apache + MySQL + PHP)
- **Composer** 2.5+
- **Git** 2.40+
- **Navegador** moderno (Chrome, Firefox, Edge)
- **Editor de código** (VS Code, PHPStorm, Sublime)
- **Cuenta en GitHub** (para colaborar)

---

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-repo/sicgov.git
cd sicgov
```

### 2. Configurar ramas (primera vez)
```bash
# Ver ramas disponibles
git branch -a

# Crear y cambiarte a tu rama personal (ej: jorge-dev)
git checkout -b jorge-dev origin/jorge-dev
```

### 3. Instalar dependencias
```bash
composer install
```

### 4. Configurar variables de entorno
Crear archivo `.env` en la raíz:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME_SYSTEM=goobv-sistema
DB_NAME_USER=goobv-usuarios
```

### 5. Configurar base de datos
Ejecutar el instalador automático:
```bash
php database/setup.php
```

### 6. Configurar Apache
Asegurar que `mod_rewrite` está activado en XAMPP:
- Abrir `httpd.conf`
- Descomentar: `LoadModule rewrite_module modules/mod_rewrite.so`

### 7. Acceder al sistema
```
http://localhost/sicgov/public/
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

### Estructura de Commits (OBLIGATORIA)
```
[TIPO]: Descripción breve (≤50 caracteres)

Cuerpo opcional con explicación detallada
- Qué se cambió
- Por qué se cambió
- Impacto en otros módulos

Referencias: #issue, PR relacionado
```

### Tipos de Commit (Tags)
| Tag | Descripción | Ejemplo |
|-----|-------------|---------|
| `FEAT` | Nueva funcionalidad | `FEAT: Agregar módulo de pedidos` |
| `FIX` | Corrección de bug | `FIX: Error al guardar producto sin imagen` |
| `DOCS` | Documentación | `DOCS: Actualizar README con nuevo módulo` |
| `STYLE` | Estilos/CSS | `STYLE: Ajustar padding del buscador` |
| `REFACTOR` | Refactorización | `REFACTOR: Mover lógica de bitácora a Helper` |
| `PERF` | Mejora de rendimiento | `PERF: Optimizar consultas SQL de productos` |
| `TEST` | Pruebas | `TEST: Agregar tests para modelo Producto` |
| `CONFIG` | Configuraciones | `CONFIG: Actualizar dependencias en composer.json` |
| `CHORE` | Mantenimiento | `CHORE: Limpiar archivos temporales` |
| `MERGE` | Fusiones | `MERGE: jorge-dev → dev` |

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
```

### Git (Básico)
```bash
# Ver en qué rama estás
git branch

# Ver estado de cambios
git status

# Agregar cambios
git add .
git commit -m "FEAT: Descripción del cambio"
git push origin tu-rama
```

---

## 🔄 Flujo de Trabajo con Git

### Estructura de Ramas
```
main (producción - solo Jorge mergea aquí)
  ↑
dev (integración - todos via Pull Request)
  ├── jorge-dev (Jorge)
  ├── leizer-dev
  ├── mariangel-dev
  ├── santiago-dev
  └── abrahan-dev
```

### Comandos Diarios

#### 1. Comenzar el día - Sincronizar
```bash
# Ir a tu rama
git checkout jorge-dev  # (o tu rama)

# Traer cambios de dev
git checkout dev
git pull origin dev
git checkout jorge-dev
git merge dev

# Si hay conflictos, resolverlos y commitear
git add .
git commit -m "MERGE: Resolver conflictos con dev"

# Subir tu rama (opcional)
git push origin jorge-dev
```

#### 2. Trabajar en features
```bash
# Hacer cambios pequeños y commits frecuentes
git add .
git commit -m "FEAT: Agregar modal de pedidos"
git commit -m "STYLE: Diseño responsive del modal"
git commit -m "FIX: Validar campos vacíos"
```

#### 3. Subir cambios al final del día
```bash
git push origin jorge-dev
```

---

## 📥 Pull Requests y Revisión de Código

### Cómo crear un Pull Request

1. **Subir tu rama** (si no lo has hecho)
   ```bash
   git push origin tu-rama
   ```

2. **En GitHub:**
   - Ir al repositorio
   - Verás un banner: "tu-rama had recent pushes"
   - Click "Compare & pull request"

3. **Completar la plantilla:**
   ```markdown
   ## 📋 Descripción
   [Explica qué hace este PR]

   ## 🎯 Cambios realizados
   - [ ] Feature 1
   - [ ] Bug fix 2

   ## ✅ Cómo probar
   1. Paso 1
   2. Paso 2

   ## 🔗 Issues relacionados
   Closes #[número]

   ## 👥 Revisores
   @jorge
   @otro-compañero
   ```

### Reglas de Pull Request
- ✅ Mínimo **1 revisor** (cualquier miembro del equipo)
- ✅ Jorge debe aprobar para mergear a `dev`
- ✅ Todos los checks deben pasar
- ✅ No mergear código que rompa funcionalidad existente

---

## 👥 Roles del Equipo

| Rol | Persona | Responsabilidades |
|-----|---------|-------------------|
| **Líder Técnico** | Jorge Cabrera | • Aprobar Pull Requests a `dev`<br>• Mantener la visión del proyecto<br>• Resolver conflictos grandes<br>• Asegurar calidad del código<br>• Hacer merge a `main` (producción) |
| **Desarrolladores** | Leizer Torrealba<br>Mariangel Bokor<br>Santiago Coello<br>Abrahan Rodriguez | • Trabajar en sus ramas<br>• Crear Pull Requests<br>• Revisar PRs de compañeros<br>• Seguir las reglas de commits |

---

## 🧩 Módulos del Sistema

### 1. Autenticación y Seguridad (✅ Implementado)
- Login con reCAPTCHA
- Registro de bitácora
- Cierre de sesión seguro
- Roles y permisos

### 2. Gestión de Productos (✅ Implementado)
- CRUD completo de productos
- Gestión de categorías
- Subida de imágenes
- DataTables con búsqueda

### 3. Gestión de Pedidos/Mesas (🔄 En desarrollo)
- Plano del salón interactivo
- Estados de mesa (Libre, Ocupada, Reservada)
- Toma de pedidos con modificadores
- Visualización en cocina (KDS)
- Cierre y pago

### 4. Gestión de Personal (📅 Pendiente)
- Registro de empleados
- Marcación de asistencia
- Control de horarios y turnos
- Reportes de personal

### 5. Gestión de Inventario (📅 Pendiente)
- Control de existencias
- Historial de movimientos
- Alertas de reposición
- Cálculo automático de compras

### 6. Menú Inteligente (📅 Pendiente)
- Catálogo digital de platillos
- Filtros por categoría
- Personalización de ingredientes
- QR por mesa

### 7. Control Financiero (📅 Pendiente)
- Ingresos y egresos
- Cálculo de ganancias
- Reportes detallados

### 8. Gestión de Clientes (📅 Pendiente)
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

### Reglas de Oro

1. **Nunca hacer push directo a `main` o `dev`** ❌
2. **Siempre trabajar en tu rama personal** (`jorge-dev`, etc.) ✅
3. **Hacer commits pequeños y frecuentes** (no un commit gigante al final)
4. **Usar los tags correctamente** (FEAT, FIX, STYLE, etc.)
5. **Pull Request obligatorio** para mergear a `dev`
6. **Mínimo 1 revisor** antes de mergear
7. **Sincronizar con `dev` diariamente** para evitar conflictos grandes
8. **Nunca dejar código comentado** (eso es para lo que está Git)

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

### Ejemplo de Commit Bueno
```
FEAT: Sistema de alertas para reposición de inventario

- Implementar notificaciones por stock bajo
- Configurar umbrales personalizados por producto
- Agregar panel de alertas pendientes

Closes #123
```

### Ejemplo de Commit Malo (NO USAR)
```
cambios varios
fix cosas
actualizaciones
```

### Ejemplo de Pull Request
```markdown
## 📋 Descripción
Agrega módulo de inventario con alertas automáticas

## 🎯 Cambios realizados
- [x] Modelo Inventario con PDO
- [x] Controlador con listado JSON
- [x] Vista con DataTables
- [x] Sistema de alertas por stock mínimo

## ✅ Cómo probar
1. Ejecutar `php database/setup.php`
2. Ir a `/inventario`
3. Modificar stock por debajo del mínimo

## 🔗 Issues relacionados
Closes #123, Relates to #45

## 👥 Revisores
@jorge @leizer
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
- **Jorge Cabrera** (Líder Técnico) - 31.843.937
- Leizer Torrealba - 30.266.398
- Mariangel Bokor - 30.587.785
- Santiago Coello - 28.165.452
- Abrahan Rodriguez - 30.405.566

**Repositorio:** [https://github.com/tu-repo/sicgov](https://github.com/tu-repo/sicgov)

---

## 📅 Versión

**Versión Actual:** 1.0.0  
**Fecha:** Febrero 2026  
**Estado:** Desarrollo Activo

---

## 🏆 Reconocimientos

Este proyecto es desarrollado como parte de un trabajo estudiantil con visión profesional, con el objetivo de crear una solución que pueda ser utilizada en un entorno real de restaurante.

---

*¡SICGOV - Sistema de Información Complementario Good Vibes! 🌟  
Un sistema con onda retro para un restaurante con mucha energía.*