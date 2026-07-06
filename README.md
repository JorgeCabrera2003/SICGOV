# 📋 **SICGOV - Sistema de Información Complementario Good Vibes**

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/mysql-8.0+-blue.svg)](#)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-v1.5.0--active-success.svg)](#)

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

**SICGOV (Sistema de Información Complementario Good Vibes)** es una plataforma integral diseñada para optimizar la administración del restaurante temático **Good Vibes Tapas & Bar**. El sistema opera bajo un patrón **MVC (Model-View-Controller)** en PHP puro, complementando las funciones administrativas del sistema principal de ventas y ofreciendo una solución técnica de alto nivel.

### Objetivos Estratégicos:
- **Gestión de Personal**: Control de asistencia, horarios, turnos y permisos.
- **Gestión de Inventario**: Control de existencias y alertas de reposición automáticas.
- **Experiencia de Usuario**: Interfaz moderna con soporte nativo para **Modo Oscuro** y animaciones fluidas.
- **Reservaciones**: Control inteligente de mesas y espacios mediante agenda interactiva.

---

## 🛠 Tecnologías Utilizadas

### Backend & Core
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| PHP | 8.2+ | Lenguaje principal (tipado estricto) |
| MySQL/MariaDB | 8.0/10.6 | Base de datos relacional con integridad referencial |
| PDO | - | Extensión para conexiones seguras y consultas preparadas |

### Frontend & UI
| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| JavaScript | ES6+ | Lógica asíncrona (AJAX) y manipulación del DOM |
| Bootstrap | 5.3 | Framework UI para diseño responsivo |
| FullCalendar | 6.1 | Motor interactivo para el calendario de reservaciones |
| DataTables | 1.13 | Gestión avanzada de tablas dinámicas |
| Select2 | 4.1 | Selectores avanzados con búsqueda en tiempo real |
| SweetAlert2 | 11 | Sistema de notificaciones y alertas premium |

---

## 📦 Librerías y Dependencias (Front-End)
Todas estas librerías han sido descargadas y se sirven localmente desde `public/assets/lib/` para mejorar el rendimiento y asegurar la independencia de conexión externa, a excepción de Google reCAPTCHA.

| Librería | Versión | Licencia | Enlace Oficial | Propósito en SICGOV |
|----------|---------|----------|----------------|---------------------|
| **jQuery** | 3.7.1 | MIT | [jquery.com](https://jquery.com/) | Manipulación del DOM y base para otras librerías. |
| **Bootstrap** | 5.3.2 | MIT | [getbootstrap.com](https://getbootstrap.com/) | Framework UI base, componentes interactivos y sistema de grillas. |
| **DataTables** | 1.13.6 | MIT | [datatables.net](https://datatables.net/) | Renderizado y filtrado avanzado de tablas dinámicas. |
| **Select2** | 4.1.0-rc.0 | MIT | [select2.org](https://select2.org/) | Selectores personalizables con búsqueda. |
| **SweetAlert2** | 11 | MIT | [sweetalert2.github.io](https://sweetalert2.github.io/) | Alertas y modales interactivos. |
| **Chart.js** | 4.4 | MIT | [chartjs.org](https://www.chartjs.org/) | Creación de gráficos estadísticos. |
| **Driver.js** | 1.3.1 | MIT | [driverjs.com](https://driverjs.com/) | Motor de tutoriales paso a paso (Tours). |

---

## 📁 Estructura del Proyecto

```
├── app/
│   ├── Controllers/          # Controladores MVC
│   │   ├── LoginController.php
│   │   ├── NoticiaController.php
│   │   ├── ReservacionController.php
│   │   └── ...
│   ├── Models/
│   │   ├── Security/         # Modelos de Seguridad (Usuario, Bitácora)
│   │   └── System/           # Modelos de Negocio (Producto, Reservación)
│   ├── Core/                 # Componentes base (Database, Router)
│   └── Helpers/              # Funciones auxiliares reutilizables
├── config/                   # Archivos de configuración y constantes
├── database/
│   ├── migrations/            # Scripts SQL de estructura
│   ├── seeders/               # Pobladores de datos de prueba
│   └── setup.php              # Instalador automático e interactivo
├── public/                    # Punto de entrada único (Assets, index.php)
│   └── assets/
│       ├── css/               # Estilos BEM (main.css, reservaciones.css)
│       └── js/                # Lógica modular (modulo_reservaciones.js)
├── resources/
│   └── views/                # Plantillas PHP (MVC Views)
├── .env                       # Variables de entorno (Configuración local)
└── composer.json              # Gestión de dependencias PHP
```

---

## 🚀 Instalación y Configuración

### 1. Clonar el repositorio
```bash
git clone https://github.com/JorgeCabrera2003/SICGOV.git
cd sicgov
```

### 2. Instalar dependencias
```bash
composer install
```

### 3. Configurar variables de entorno
Crear un archivo `.env` en la raíz basado en `.env.example`:
```env
DB_HOST=localhost
DB_USER=root
DB_PASS=tu_password
DB_NAME_SYSTEM=goobv-sistema
DB_NAME_USER=goobv-usuarios
```

### 4. Instalación de la Base de Datos
Ejecuta el instalador interactivo que configurará esquemas, tablas y datos de prueba:
```bash
php database/setup.php
```

---

## 📝 Nomenclatura y Convenciones

### Estándares de Desarrollo
| Tipo | Convención | Ejemplo |
|------|------------|---------|
| **Clases** | PascalCase | `ProductoController.php` |
| **Modelos** | PascalCase (singular) | `Reservacion.php` |
| **Vistas** | snake_case | `detalle_reserva.php` |
| **Tablas** | minúsculas, singular | `reservacion`, `producto` |
| **Métodos** | camelCase | `validarDisponibilidad()` |
| **Variables** | camelCase | `$fechaReserva` |
| **Constantes** | MAYÚSCULAS | `BASE_URL` |

### Estructura de Commits (OBLIGATORIA)
```
[TIPO]: Descripción breve (≤50 caracteres)

- Qué se cambió
- Por qué se cambió
```

### Tipos de Commit (Tags)
| Tag | Descripción | Ejemplo |
|-----|-------------|---------|
| `FEAT` | Nueva funcionalidad | `FEAT: Agregar módulo de reservaciones` |
| `FIX` | Corrección de bug | `FIX: Error en validación de fecha` |
| `DOCS` | Documentación | `DOCS: Actualizar manual técnico` |
| `STYLE` | Estilos/CSS | `STYLE: Ajustar contraste en modo oscuro` |
| `REFACTOR` | Refactorización | `REFACTOR: Optimizar consulta de disponibilidad` |
| `PERF` | Rendimiento | `PERF: Añadir índices a la tabla cliente` |
| `CHORE` | Mantenimiento | `CHORE: Limpiar archivos temporales` |

---

## 🔄 Flujo de Trabajo con Git

### Estrategia de Ramas
- **`main`**: Rama de producción (Solo código estable y aprobado).
- **`dev`**: Rama de integración donde se fusionan los desarrollos.
- **`jorge-dev`, `equipo-dev`**: Ramas personales de trabajo diario.

### Comandos Diarios
1. **Sincronizar**: `git pull origin dev` (Traer cambios de los compañeros).
2. **Desarrollar**: Hacer cambios pequeños y frecuentes.
3. **Commit**: `git commit -m "FEAT: Implementar X"`
4. **Subir**: `git push origin tu-rama`

---

## 🧩 Módulos del Sistema

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| **Autenticación** | ✅ | Login con reCAPTCHA, roles y sesiones seguras. |
| **Noticias** | ✅ | Portal editorial con gestión de imágenes. |
| **Reservaciones** | ✅ | Agenda interactiva y validación de horarios. |
| **Inventario** | 🔄 | Control de stock y alertas (En desarrollo). |

---

## 🎨 Guía de Estilos CSS
El sistema utiliza **BEM** y **Anidamiento Nativo de CSS**. 
👉 **[Consulta la Guía de Estilos Detallada](docs/STYLE_GUIDE.md)** para aprender a usar el sistema de diseño.

---

## 🔧 Solución de Problemas (FAQ)
- **Error 404 en Assets**: Verifica la constante `BASE_URL` en `config/config.php`.
- **Class not found**: Ejecuta `composer dump-autoload`.
- **Error SQL de Columna**: Asegúrate de haber ejecutado `php database/setup.php` para tener la última estructura.

---

## 👥 Equipo SICGOV
- **Jorge Cabrera** (Líder Técnico)
- Leizer Torrealba, Mariangel Bokor, Santiago Coello, Abrahan Rodriguez.

---
*SICGOV - Innovación y Estilo para Good Vibes Tapas & Bar.* 🌟
