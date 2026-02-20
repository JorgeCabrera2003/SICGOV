## 📚 **README.md - SICGOV (Sistema de Información Complementario Good Vibes)**

```markdown
# 🎸 SICGOV - Sistema de Información Complementario Good Vibes

<div align="center">
  
![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.2-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![License](https://img.shields.io/badge/license-MIT-green)
![Contributors](https://img.shields.io/badge/contributors-5-yellow)

**Sistema de gestión complementario para restaurante temático Good Vibes Tapas & Bar**
  
</div>

---

## 📋 TABLA DE CONTENIDOS
- [🎸 SICGOV - Sistema de Información Complementario Good Vibes](#-sicgov---sistema-de-información-complementario-good-vibes)
  - [📋 TABLA DE CONTENIDOS](#-tabla-de-contenidos)
  - [📌 DESCRIPCIÓN DEL PROYECTO](#-descripción-del-proyecto)
  - [🎯 VISIÓN Y PROPÓSITO](#-visión-y-propósito)
  - [👥 EQUIPO DE DESARROLLO](#-equipo-de-desarrollo)
  - [🛠 TECNOLOGÍAS UTILIZADAS](#-tecnologías-utilizadas)
  - [📁 ESTRUCTURA DEL PROYECTO](#-estructura-del-proyecto)
  - [⚙️ REQUISITOS PREVIOS](#️-requisitos-previos)
  - [🚀 INSTALACIÓN](#-instalación)
  - [🔧 CONFIGURACIÓN](#-configuración)
  - [💾 BASE DE DATOS](#-base-de-datos)
  - [🌳 ESTRATEGIA DE RAMAS (GIT FLOW)](#-estrategia-de-ramas-git-flow)
  - [📝 REGLAS DE COMMITS](#-reglas-de-commits)
  - [🔄 FLUJO DE TRABAJO DIARIO](#-flujo-de-trabajo-diario)
  - [🔍 PULL REQUESTS](#-pull-requests)
  - [🧩 MÓDULOS DEL SISTEMA](#-módulos-del-sistema)
  - [🎨 GUÍA DE ESTILOS](#-guía-de-estilos)
  - [📝 NOMENCLATURA Y CONVENCIONES](#-nomenclatura-y-convenciones)
  - [📊 DIAGRAMA DE BASE DE DATOS](#-diagrama-de-base-de-datos)
  - [🔧 SOLUCIÓN DE PROBLEMAS COMUNES](#-solución-de-problemas-comunes)
  - [📚 DOCUMENTACIÓN ADICIONAL](#-documentación-adicional)
  - [📜 LICENCIA](#-licencia)

---

## 📌 DESCRIPCIÓN DEL PROYECTO

**SICGOV** es un sistema de información complementario diseñado para optimizar la administración del restaurante temático **Good Vibes Tapas & Bar** (años 80s y 90s). El sistema complementa las funciones administrativas faltantes del sistema principal de ventas (Eco Logical), ofreciendo:

- **Gestión de Personal**: Control de asistencia, horarios, turnos y permisos
- **Gestión de Inventario**: Control de existencias, alertas de reposición
- **Menú Inteligente**: Catálogo digital, QR por mesa, personalización de platillos
- **Control Financiero**: Registro de ingresos/egresos, cálculo de ganancias
- **Gestión de Clientes**: Historial, sistema de puntos por lealtad
- **Reservaciones**: Control de mesas y espacios

---

## 🎯 VISIÓN Y PROPÓSITO

> "Desarrollar un sistema web integral de gestión que optimice todas las operaciones del restaurante, desde la administración interna hasta la experiencia del cliente, garantizando eficiencia, control y un servicio ágil."

El restaurante **Good Vibes** transporta a sus clientes a la estética vibrante de los años 80s y 90s, combinando una ambientación retro con talento en vivo. Su menú diverso ofrece desde comida rápida hasta platillos de preparación elaborada, todos con nombres icónicos que refuerzan la experiencia nostalgia.

---

## 👥 EQUIPO DE DESARROLLO

| Rol | Nombre | Rama Personal |
|-----|--------|---------------|
| **Líder Técnico / Desarrollador** | Jorge Cabrera | `jorge-dev` |
| **Desarrollador** | Leizer Torrealba | `leizer-dev` |
| **Desarrollador** | Mariangel Bokor | `mariangel-dev` |
| **Desarrollador** | Santiago Coello | `santiago-dev` |
| **Desarrollador** | Abrahan Rodriguez | `abrahan-dev` |

### **Responsabilidades del Líder:**
- ✅ Aprobar Pull Requests a `dev`
- ✅ Mantener la visión del proyecto
- ✅ Resolver conflictos grandes
- ✅ Asegurar calidad del código
- ✅ Hacer merge a `main` (producción)

---

## 🛠 TECNOLOGÍAS UTILIZADAS

### **Backend**
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| PHP | 8.2+ | Lenguaje principal (tipado estricto) |
| MySQL/MariaDB | 8.0/10.6 | Base de datos relacional |
| PDO | - | Extensión para conexiones seguras |
| Composer | 2.5+ | Gestor de dependencias |

### **Frontend**
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

### **Herramientas de Desarrollo**
| Herramienta | Versión | Uso |
|-------------|---------|-----|
| XAMPP | 8.2+ | Entorno de desarrollo local |
| Git | 2.40+ | Control de versiones |
| GitHub | - | Repositorio remoto |
| FakerPHP | 1.23+ | Generación de datos de prueba |

---

## 📁 ESTRUCTURA DEL PROYECTO

```
good-vibes/
├── app/
│   ├── Controllers/          # Controladores MVC
│   ├── Models/                # Modelos (Security/System)
│   ├── Core/                  # Clases base (Database, Router)
│   └── Helpers/               # Funciones auxiliares (Helper.php)
├── config/                    # Configuración de BD
├── database/                  # Migraciones y seeders
├── public/                    # Front Controller (index.php)
│   └── assets/                # CSS, JS, imágenes
├── resources/                 # Vistas
│   └── views/
│       ├── layouts/           # Head, menú, footer
│       ├── auth/              # Login
│       └── productos/         # Módulo de productos
├── vendor/                    # Dependencias Composer
├── .env                       # Variables de entorno
├── composer.json              # Dependencias PHP
└── README.md                  # Este archivo
```

---

## ⚙️ REQUISITOS PREVIOS

- **XAMPP** 8.2+ (Apache + MySQL + PHP)
- **Composer** 2.5+
- **Git** 2.40+
- **Navegador** moderno (Chrome, Firefox, Edge)
- **Editor de código** (VS Code, PHPStorm, Sublime)
- **Cuenta en GitHub** (para colaborar)

---

## 🚀 INSTALACIÓN

### **1. Clonar el repositorio**
```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-repo/sicgov.git good-vibes
cd good-vibes
```

### **2. Configurar rama personal (solo primera vez)**
```bash
# Traer todas las ramas
git fetch origin

# Crear y cambiarte a tu rama personal
git checkout -b jorge-dev origin/jorge-dev  # Reemplaza con tu nombre

# Configurar seguimiento
git branch --set-upstream-to=origin/jorge-dev jorge-dev
```

### **3. Instalar dependencias**
```bash
composer install
```

### **4. Configurar variables de entorno**
Crear archivo `.env` en la raíz:

```env
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME_SYSTEM=goobv-sistema
DB_NAME_USER=goobv-usuarios
```

### **5. Configurar base de datos**
```bash
php database/setup.php
```

### **6. Acceder al sistema**
```
http://localhost/good-vibes/public/
```

**Credenciales por defecto:**
- Usuario: `admin_root`
- Contraseña: `1234`

---

## 🔧 CONFIGURACIÓN

### **Archivo `.env`**
| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| DB_HOST | Host de la base de datos | localhost |
| DB_USER | Usuario MySQL | root |
| DB_PASS | Contraseña MySQL | (vacío) |
| DB_NAME_SYSTEM | BD del negocio | goobv-sistema |
| DB_NAME_USER | BD de seguridad | goobv-usuarios |

### **Archivo `config/database.php`**
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

## 💾 BASE DE DATOS

### **Conexiones (Singleton Multi-BD)**
```php
// BD de seguridad (usuarios, roles, bitácora)
$dbSecurity = Database::getConnection('security');

// BD del negocio (productos, pedidos, inventario)
$dbBusiness = Database::getConnection('business');
```

### **Migraciones**
```bash
# Instalar/Resetear base de datos
php database/setup.php
```

### **Seeders con Faker**
Los seeders generan datos de prueba realistas usando FakerPHP.

---

## 🌳 ESTRATEGIA DE RAMAS (GIT FLOW)

```
main (producción - solo Jorge hace merge aquí)
  ↑
dev (integración - todos via PR, Jorge aprueba)
  ├── jorge-dev (Jorge - también programa)
  ├── leizer-dev
  ├── mariangel-dev
  ├── santiago-dev
  └── abrahan-dev
```

### **Reglas de Oro:**
1. ✅ **Nunca hacer push directo a `main` o `dev`**
2. ✅ **Siempre trabajar en tu rama personal** (`nombre-dev`)
3. ✅ **Pull Request obligatorio** para mergear a `dev`
4. ✅ **Mínimo 1 revisor** antes de mergear
5. ✅ **Sincronizar con `dev` diariamente**
6. ✅ **Jorge aprueba los PRs a `dev`**

---

## 📝 REGLAS DE COMMITS

### **Formato obligatorio:**
```
[TIPO]: Descripción breve (max 50 caracteres)

Cuerpo opcional (explicar QUÉ y POR QUÉ, no el CÓMO)

Referencias: #issue, PR relacionado
```

### **Tipos de Commit (Tags)**
| Tag | Cuándo usarlo | Ejemplo |
|-----|---------------|---------|
| `FEAT` | Nueva funcionalidad | `FEAT: Agregar filtro de búsqueda en productos` |
| `FIX` | Corrección de bug | `FIX: Error al guardar producto sin imagen` |
| `DOCS` | Documentación | `DOCS: Actualizar README con instrucciones` |
| `STYLE` | Cambios de formato (CSS, espacios) | `STYLE: Ajustar padding del buscador` |
| `REFACTOR` | Reestructurar código (sin cambiar funcionalidad) | `REFACTOR: Mover lógica de bitácora a Helper` |
| `PERF` | Mejora de rendimiento | `PERF: Optimizar consulta de listar productos` |
| `TEST` | Agregar/modificar tests | `TEST: Pruebas para el modelo Producto` |
| `CONFIG` | Cambios en configuración | `CONFIG: Actualizar dependencias en composer.json` |
| `CHORE` | Tareas de mantenimiento | `CHORE: Limpiar archivos temporales` |
| `MERGE` | Fusiones de ramas | `MERGE: jorge-dev → dev` |

### **Ejemplo Correcto:**
```
FEAT: Agregar buscador en tiempo real de productos

- Implementar filtro por nombre y categoría
- Usar AJAX para no recargar la página
- Agregar indicador de carga

Closes #45
```

### **Ejemplo INCORRECTO (NO USAR):**
```
cambios varios
fix cosas
actualizaciones
```

---

## 🔄 FLUJO DE TRABAJO DIARIO

### **1. Comenzar el día - Sincronizar**
```bash
# Ir a tu rama
git checkout jorge-dev

# Traer cambios de dev
git checkout dev
git pull origin dev
git checkout jorge-dev
git merge dev

# Si hay conflictos, resolverlos
git add .
git commit -m "MERGE: Resolver conflictos con dev"

# Subir tu rama
git push origin jorge-dev
```

### **2. Trabajar en features**
```bash
# Commits frecuentes con TAGS
git add .
git commit -m "FEAT: Agregar modal de pedidos"
git commit -m "STYLE: Diseño responsive del modal"
git commit -m "FIX: Validar campos vacíos"
```

### **3. Sincronizar varias veces al día**
```bash
git checkout dev
git pull origin dev
git checkout jorge-dev
git merge dev
git push origin jorge-dev
```

### **4. Subir cambios al final del día**
```bash
git push origin jorge-dev
```

---

## 🔍 PULL REQUESTS

### **Plantilla de Pull Request**
```markdown
## 📋 Descripción
[Explica qué hace este PR]

## 🎯 Cambios realizados
- [ ] Feature 1
- [ ] Bug fix 2
- [ ] Tests

## ✅ Cómo probar
1. Paso 1
2. Paso 2
3. Verificar que...

## 📸 Screenshots (si aplica)

## 🔗 Issues relacionados
Closes #[número]

## 👥 Revisores
@jorge
@otro-compañero
```

### **Checklist antes de PR**
- [ ] ¿Los commits siguen el formato de tags?
- [ ] ¿El código funciona localmente?
- [ ] ¿No rompe otras funcionalidades?
- [ ] ¿Sin código comentado?
- [ ] ¿Sin console.log de depuración?

---

## 🧩 MÓDULOS DEL SISTEMA

### **1. Autenticación y Seguridad** (Jorge)
- Login con reCAPTCHA
- Registro de bitácora
- Cierre de sesión seguro
- Roles y permisos

### **2. Gestión de Productos** (✅ Completado)
- CRUD de productos
- Categorías de productos
- Subida de imágenes
- DataTables con búsqueda

### **3. Gestión de Personal** (Leizer)
- Registro de empleados
- Marcación de asistencia
- Control de horarios y turnos
- Reportes de personal

### **4. Gestión de Inventario** (Mariangel)
- Control de existencias
- Historial de movimientos
- Alertas de reposición
- Cálculo automático de compras

### **5. Menú Inteligente** (Santiago)
- Catálogo digital de platillos
- Filtros por categoría
- Personalización de ingredientes
- QR por mesa

### **6. Pedidos y Mesas** (Abrahan)
- Plano interactivo del salón
- Estados de mesa (Libre, Ocupada, Reservada)
- Toma de pedidos con modificadores
- Envío a cocina (KDS)

### **7. Control Financiero** (Jorge)
- Ingresos y egresos
- Cálculo de ganancias
- Reportes detallados

### **8. Gestión de Clientes** (Leizer/Mariangel)
- Historial de clientes
- Sistema de puntos por lealtad
- Reservaciones de mesas

---

## 🎨 GUÍA DE ESTILOS

### **Paleta de Colores (60-30-10)**

| Porcentaje | Uso | Color | Código |
|------------|-----|-------|--------|
| **60%** | Fondo principal | Gris muy claro | `#F4F7F6` |
| **60%** | Tarjetas | Blanco | `#FFFFFF` |
| **30%** | Sidebar/Navegación | Negro mate | `#1A1C20` |
| **10%** | Acento (botones, hover) | Amarillo vibrante | `#FFD600` |

### **Tipografía**
```css
font-family: 'Poppins', sans-serif;
```

### **Botones**
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

---

## 📝 NOMENCLATURA Y CONVENCIONES

### **Archivos y Carpetas**
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

### **Namespaces**
- Controladores: `namespace App\Controllers;`
- Modelos (Seguridad): `namespace App\Models\Security;`
- Modelos (Sistema): `namespace App\Models\System;`
- Helpers: `namespace App\Helpers;`

---

## 📊 DIAGRAMA DE BASE DE DATOS

### **Base de Datos `goobv-usuarios` (Seguridad)**
- `usuario`
- `rol`
- `bitacora`
- `configuracion`

### **Base de Datos `goobv-sistema` (Negocio)**
- `personal`
- `cargo`
- `asistencia`
- `categoria_producto`
- `producto`
- `ingrediente`
- `inventario`
- `mesa`
- `pedido`
- `detalle_pedido`
- `cliente`
- `pago`
- `transaccion_financiera`

---

## 🔧 SOLUCIÓN DE PROBLEMAS COMUNES

### **Error: "Class not found"**
```bash
composer dump-autoload
```

### **Error: "BASE_URL is not defined" en JS**
Asegurar que en `head.php` está:
```php
<script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
```

### **Error: "Unknown column"**
Verificar nombres de columnas en BD:
```sql
DESCRIBE nombre_tabla;
```

### **Error: "Field doesn't have a default value"**
Generar ID manualmente (para campos VARCHAR como PK):
```php
$this->id_producto = $this->generarIdProducto();
```

### **Error: Sesión no persiste**
Configurar sesiones seguras:
```php
session_start([
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);
```

### **Error: DataTables "Cannot reinitialise"**
Verificar que los scripts de DataTables se cargan UNA sola vez.

---

## 📚 DOCUMENTACIÓN ADICIONAL

- [PHP Manual](https://www.php.net/manual/es/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.3/)
- [DataTables Manual](https://datatables.net/manual/)
- [FakerPHP Docs](https://fakerphp.github.io/)
- [Git Handbook](https://guides.github.com/introduction/git-handbook/)

---

## 📜 LICENCIA

**© 2026 Equipo SICGOV - Good Vibes Tapas & Bar**

Este proyecto es desarrollado con fines académicos y profesionales. Todos los derechos reservados.

---

<div align="center">
  
**¿Listo para empezar?** 🚀

[Volver al inicio](#-sicgov---sistema-de-información-complementario-good-vibes)

</div>
```

---

## 🎯 **RESUMEN DE LO AGREGADO**

| Sección | Novedad |
|---------|---------|
| **Nombre del sistema** | SICGOV |
| **Equipo de desarrollo** | Roles y ramas personales |
| **Estrategia de ramas** | Git Flow adaptado con ramas personales |
| **Reglas de commits** | Tags y formato obligatorio |
| **Flujo de trabajo diario** | Comandos paso a paso |
| **Pull Requests** | Plantilla y checklist |
| **Módulos** | Asignados por persona |
| **Solución de problemas** | Errores comunes y soluciones |
