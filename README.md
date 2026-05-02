# 📋 **SICGOV - Sistema de Información Complementario Good Vibes**

[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Status](https://img.shields.io/badge/status-active-success.svg)](#)

## 📌 Tabla de Contenidos
1.  [Descripción del Proyecto](#-descripción-del-proyecto)
2.  [Tecnologías Utilizadas](#-tecnologías-utilizadas)
3.  [Arquitectura y Estándares](#-arquitectura-y-estándares)
4.  [Instalación y Configuración](#-instalación-y-configuración)
5.  [Módulos del Sistema](#-módulos-del-sistema)
6.  [Guía de Estilos CSS](#-guía-de-estilos-css)
7.  [Contribución](#-contribución)
8.  [Créditos y Versión](#-créditos-y-versión)

---

## 🎯 Descripción del Proyecto

**SICGOV** es una plataforma integral de gestión diseñada específicamente para **Good Vibes Tapas & Bar**. El sistema opera bajo un patrón **MVC (Model-View-Controller)** en PHP puro, proporcionando una solución robusta y escalable para la administración de personal, inventario, reservaciones y analítica financiera.

### Objetivos Estratégicos:
- **Automatización de Procesos**: Digitalización completa del ciclo de vida del cliente y el inventario.
- **Auditoría y Control**: Sistema de bitácora detallado para el seguimiento de operaciones críticas.
- **Experiencia de Usuario Premium**: Interfaz moderna con soporte nativo para Modo Oscuro y animaciones fluidas.

---

## 🛠 Tecnologías Utilizadas

| Categoría | Tecnologías |
|-----------|-------------|
| **Core** | PHP 8.2+, MySQL 8.0, PDO |
| **Frontend** | Bootstrap 5.3, JavaScript ES6+, jQuery 3.7 |
| **Componentes** | FullCalendar 6, Select2, DataTables, SweetAlert2, Flatpickr |
| **DevOps** | Composer, Git, Dotenv |

---

## 🏗 Arquitectura y Estándares

El proyecto sigue una estructura MVC estricta para garantizar la separación de responsabilidades:

- **Controllers**: Lógica de negocio y orquestación de peticiones.
- **Models**: Abstracción de la base de datos y validaciones de datos (Seguridad y Sistema).
- **Views**: Presentación mediante plantillas PHP limpias y componentes reutilizables.
- **Core**: Componentes base como la conexión Singleton y el manejador de rutas.

---

## 🚀 Instalación y Configuración

### Requisitos
- PHP >= 8.2
- MySQL >= 8.0
- Composer

### Pasos Rápidos
1. **Clonar**: `git clone https://github.com/JorgeCabrera2003/SICGOV.git`
2. **Dependencias**: `composer install`
3. **Entorno**: Copia `.env.example` a `.env` y ajusta tus credenciales.
4. **Base de Datos**: Ejecuta el instalador interactivo:
   ```bash
   php database/setup.php
   ```
5. **Servidor**: Configura tu DocumentRoot a la raíz del proyecto o accede vía `localhost/good-vibes/public/`.

---

## 🧩 Módulos del Sistema

| Módulo | Estado | Descripción |
|--------|--------|-------------|
| **Autenticación** | ✅ | Login seguro con reCAPTCHA y gestión de roles. |
| **Noticias** | ✅ | Portal editorial con gestión dinámica de contenido. |
| **Reservaciones** | ✅ | Agenda interactiva con FullCalendar y gestión de horarios. |
| **Productos** | ✅ | CRUD completo con gestión de imágenes y categorías. |
| **Inventario** | 🔄 | Control de existencias y alertas de stock. |
| **Personal** | 📅 | Gestión de empleados y asistencia (Próximamente). |

---

## 🎨 Guía de Estilos CSS

Hemos implementado un sistema de diseño moderno basado en:
- **BEM (Block Element Modifier)** para la nomenclatura de clases.
- **Anidamiento Nativo de CSS** para un código limpio y mantenible.
- **Diseño Responsivo** priorizando la experiencia móvil.

👉 **[Consulta la Guía Completa de Estilos aquí](docs/STYLE_GUIDE.md)**

---

## 📝 Nomenclatura de Commits (Convencional)
Para mantener un historial de cambios profesional, utilizamos:
- `FEAT:` Nuevas funcionalidades.
- `FIX:` Corrección de errores.
- `STYLE:` Cambios visuales y CSS.
- `REFACTOR:` Mejoras de código sin cambio funcional.

---

## 📅 Versión y Estado

**Versión Actual:** 1.5.0
**Fecha Última Actualización:** Mayo 2026
**Estado:** Desarrollo Activo

---

## 🏆 Créditos
Desarrollado por el equipo **SICGOV**:
- **Jorge Cabrera** (Líder Técnico)
- Leizer Torrealba, Mariangel Bokor, Santiago Coello, Abrahan Rodriguez.

---
*SICGOV - Innovación y Estilo en cada bit.* 🌟
