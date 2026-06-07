# Changelog del Sistema Good Vibes

Todas las versiones y cambios notables del sistema serán documentados en este archivo.

## [v1.1.0] - Módulo de Pedidos Públicos Interactivos

### FEAT (Nuevas Características)
- **Módulo de Checkout:** Se integró un sistema de "Proceder al Pago" donde los usuarios pueden llenar sus datos y subir un comprobante.
- **Pago Móvil Integrado:** El sistema de pago ahora muestra dinámicamente los datos de Pago Móvil con botones rápidos para copiar.
- **Persistencia en el Carrito:** El carrito de compras guarda el estado del usuario en su `localStorage` para evitar pérdida de datos si la página se recarga.
- **Personalización Dinámica:** Se integró un modal de personalización que permite a los usuarios remover ingredientes principales (sin alterar precio) o agregar extras (aumentando el precio de forma instantánea).
- **Rediseño UI/UX (Menú Público):** Se dividió la pantalla con un carrito *sticky* en escritorio y un botón flotante dinámico en dispositivos móviles. Diseño de tarjetas premium para los platillos y banner interactivo.
- **Transacciones Seguras (Backend):** El modelo `PedidoPublico` maneja la inserción segura de Persona, Cliente, Pedido, Detalle de Pedido e Imagen (Comprobante) a través de transacciones SQL para mantener la integridad de la base de datos (SOLID).

### REFACTOR (Refactorización)
- **Controladores Limpios:** Refactorización de `FrontController.php` y `MenuController.php` para eliminar el uso de clases completas siguiendo el nuevo enfoque estructurado/modular (DRY).

### CHORE (Tareas / Base de Datos)
- Migración de `cedula_empleado` para aceptar nulos (permitiendo pedidos automatizados desde la web).
- Expansión de la tabla polimórfica `imagen` para aceptar tipo `'PAGO'`.
- Creación del método de pago `'Pago Móvil'` por defecto.
- Carga masiva de nuevos platillos y generación de imágenes fotorealistas para el menú.
