# 📋 **SICGOV - Sistema de Información Complementario Good Vibes**

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/mysql-8.0+-blue.svg)](#)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-v1.5.0--active-success.svg)](#)

## 📌 Tabla de Contenidos
1.  [Descripción del Proyecto](#-descripción-del-proyecto)
2.  [Tecnologías Utilizadas](#-tecnologías-utilizadas)
3.  [Requisitos del Sistema](#-requisitos-del-sistema)
4.  [Guía de Instalación Detallada](#-guía-de-instalación-detallada)
5.  [Gestión de Base de Datos](#-gestión-de-base-de-datos)
6.  [Estructura y Arquitectura (MVC)](#-estructura-y-arquitectura-mvc)
7.  [Flujo de Trabajo con Git](#-flujo-de-trabajo-con-git)
8.  [Estándares de Código y Estilo](#-estándares-de-código-y-estilo)
9.  [Módulos del Sistema](#-módulos-del-sistema)
10. [Solución de Problemas (FAQ)](#-solución-de-problemas-faq)

---

## 🎯 Descripción del Proyecto

**SICGOV (Sistema de Información Complementario Good Vibes)** es una solución integral de gestión empresarial diseñada para el restaurante temático **Good Vibes Tapas & Bar**. El sistema ha sido construido desde cero utilizando el patrón **MVC (Model-View-Controller)** en PHP puro, garantizando un control total sobre la lógica de negocio y una alta eficiencia en el rendimiento.

El sistema complementa las operaciones administrativas, permitiendo la gestión de inventario, personal, reservaciones y un portal de noticias dinámico, todo bajo una arquitectura segura y modular.

---

## 🛠 Tecnologías Utilizadas

### Core & Backend
- **PHP 8.2+**: Uso de tipado estricto y características modernas.
- **MySQL 8.0 / MariaDB**: Base de datos relacional con integridad referencial.
- **PDO (PHP Data Objects)**: Para consultas preparadas y seguridad contra Inyección SQL.
- **Composer**: Gestión de dependencias (Dotenv, PHPMailer, Faker).

### Frontend Premium
- **Bootstrap 5.3**: Framework base para diseño responsivo.
- **JavaScript (ES6+)**: Lógica asíncrona mediante AJAX (jQuery 3.7).
- **FullCalendar 6**: Motor interactivo para el módulo de reservaciones.
- **DataTables**: Gestión avanzada de tablas y reportes.
- **SweetAlert2 & Flatpickr**: UI moderna para notificaciones y selección de fechas.

---

## 📋 Requisitos del Sistema

- **Servidor Web**: Apache 2.4+ (con `mod_rewrite` activo).
- **PHP**: Versión 8.2 o superior.
- **Extensiones PHP**: `pdo_mysql`, `gd`, `mbstring`, `openssl`.
- **Base de Datos**: MySQL 8.0+ o MariaDB 10.4+.
- **Herramientas**: Composer y Git instalados en el PATH.

---

## 🚀 Guía de Instalación Detallada

### 1. Clonación del Repositorio
Abre tu terminal y navega hasta tu directorio de servidor (ej: `htdocs`):
```bash
git clone https://github.com/JorgeCabrera2003/SICGOV.git
cd sicgov
```

### 2. Instalación de Dependencias
Ejecuta Composer para descargar las librerías necesarias:
```bash
composer install
```
*Si tienes errores de carga de clases después, ejecuta: `composer dump-autoload`.*

### 3. Configuración de Variables de Entorno
Crea un archivo `.env` en la raíz del proyecto basado en `.env.example`:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_password
DB_NAME_SYSTEM=goobv-sistema
DB_NAME_USER=goobv-usuarios
```

---

## 💾 Gestión de Base de Datos

El sistema cuenta con un **Instalador Automático e Interactivo** que configura todo el entorno.

### Ejecución del Setup
```bash
php database/setup.php
```

### ¿Qué hace el instalador?
1.  **Validación de Conexión**: Verifica que tus credenciales en `.env` sean correctas.
2.  **Creación de Esquemas**: Crea las bases de datos `goobv-sistema` y `goobv-usuarios`.
3.  **Migraciones**: Carga la estructura de tablas desde `/database/migrations/`.
4.  **Seeders (Datos de Prueba)**: 
    - Crea el usuario administrador por defecto (`admin_root` / `1234`).
    - Genera datos aleatorios realistas (clientes, productos, mesas) usando **FakerPHP**.

---

## 🏗 Estructura y Arquitectura (MVC)

El sistema está organizado para facilitar el trabajo en equipo y la escalabilidad:

- **`/app`**: El corazón del sistema.
  - `/Controllers`: Reciben las peticiones y orquestan la respuesta.
  - `/Models`: Interactúan con la base de datos (Seguridad y Sistema).
  - `/Core`: Clases base (Database Singleton, Router).
- **`/public`**: Único punto de acceso público. Contiene `index.php` y los assets.
- **`/resources/views`**: Contiene las plantillas HTML/PHP divididas por módulos.
- **`/config`**: Archivos de configuración estática.

---

## 🔄 Flujo de Trabajo con Git

Para mantener la integridad del código, seguimos un flujo de **Feature Branches**:

### 1. Sincronización Diaria
Antes de empezar a trabajar, trae los últimos cambios de la rama de integración (`dev`):
```bash
git checkout jorge-dev
git pull origin dev
```

### 2. Registro de Cambios (Commits)
Usamos **Conventional Commits** para que el historial sea legible:
- `FEAT: [Módulo] Descripción` -> Nueva funcionalidad.
- `FIX: [Módulo] Descripción` -> Corrección de bug.
- `STYLE: [Módulo] Descripción` -> Cambios visuales (CSS).
- `DOCS: Descripción` -> Cambios en documentación.

### 3. Envío de Código
```bash
git add .
git commit -m "FEAT: Agregar validación de reservaciones"
git push origin jorge-dev
```

---

## 🎨 Estándares de Código y Estilo

### CSS Moderno
Hemos adoptado el estándar **BEM + Native Nesting**. Es imperativo seguir las reglas documentadas para evitar conflictos de especificidad.
👉 **[Ver Guía de Estilos CSS](docs/STYLE_GUIDE.md)**

### PHP Clean Code
- Usar **PascalCase** para Clases y Modelos.
- Usar **camelCase** para métodos y variables.
- Siempre usar **PDO Prepared Statements** para cualquier interacción con la BD.

---

## 🧩 Módulos del Sistema

| Módulo | Estado | Características |
|--------|--------|-----------------|
| **Seguridad** | ✅ | Login, Roles, Bitácora, reCAPTCHA. |
| **Portal Noticias** | ✅ | Editorial, Carrusel, Filtros avanzados. |
| **Reservaciones** | ✅ | Calendario 3D, Validación de choque de horarios. |
| **Inventario** | 🔄 | Control de insumos, stock mínimo, alertas. |
| **Ventas** | 📅 | Toma de pedidos, facturación, QR. |

---

## 🔧 Solución de Problemas (FAQ)

**1. ¿Por qué no cargan los estilos o imágenes?**
Asegúrate de que la constante `BASE_URL` en `config/config.php` apunte correctamente a tu URL local.

**2. Error "Class not found" al crear un nuevo archivo**
Recuerda que usamos PSR-4. Debes ejecutar `composer dump-autoload` cada vez que crees una clase nueva con un namespace.

**3. Error de reCAPTCHA en local**
Asegúrate de tener la extensión `gd` habilitada en tu `php.ini` de XAMPP.

---

## 👥 Equipo SICGOV
- **Jorge Cabrera** - Líder Técnico & Arquitecto.
- **Leizer Torrealba** - Documentación & QA.
- **Mariangel Bokor** - Especialista en UI/UX.
- **Santiago Coello** - Backend Developer.
- **Abrahan Rodriguez** - Database Manager.

---
*SICGOV - Innovación y Estilo para Good Vibes Tapas & Bar.* 🌟
