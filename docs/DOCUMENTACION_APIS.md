# Documentación Maestra de APIs - SICGOV (Good Vibes)

## 1. Arquitectura de las APIs del Sistema

El sistema **SICGOV** opera bajo un patrón **Front Controller** unificado (`app/Controllers/FrontController.php`), el cual centraliza el enrutamiento a través de parámetros de consulta (`Query Parameters`) y despacha las operaciones funcionales (CRUD, validaciones, consultas complejas, reportes) mediante solicitudes asíncronas (`AJAX / RESTful RPC`) que devuelven respuestas estructuradas en formato `application/json`.

### 1.1. Especificación de Enrutamiento
* **Ruta Base:** `http://localhost/SICGOV/public/`
* **Parámetro de Controlador:** `?page={NombreControlador}` (ej. `?page=Reservacion`, `?page=Pedido`, `?page=Promocion`)
* **Parámetro de Ámbito:** `&type={admin|publico|detalle}` (por defecto `admin`)
* **Acción o Petición:** Enviada vía cuerpo `POST` (o `GET` en endpoints específicos) bajo la clave `peticion` o `action`.
* **Encabezado Recomendado:** `X-Requested-With: XMLHttpRequest` (identifica peticiones asíncronas y activa el formateador de errores JSON estándar).

### 1.2. Estructura Estándar de Respuesta JSON
```json
{
  "resultado": 200,
  "mensaje": "Operación ejecutada exitosamente",
  "datos": [ ... ],
  "registro": { ... }
}
```
* **200 / 201:** Operación exitosa o recurso creado.
* **400:** Error de validación o datos de formulario incorrectos.
* **401:** No autenticado o credenciales inválidas.
* **403:** Acción no autorizada / Permiso de rol insuficiente.
* **404:** Registro o entidad no encontrada.
* **500:** Error interno del servidor o fallo de base de datos.

---

## 2. Inventario y Censo de APIs

El sistema cuenta con un total de **37 Controladores Funcionales** y más de **170 Endpoints / Acciones específicas**, distribuidos en 9 grandes dominios:

| N° | Dominio | Controladores | N° Endpoints | Ámbito Principal |
| :--- | :--- | :--- | :---: | :--- |
| **01** | Autenticación, Sesión y Perfil | `Login`, `LogOut`, `RecuperacionClave`, `Perfil` | 15 | Público / Autenticado |
| **02** | Seguridad, Roles y Auditoría | `Usuario`, `Rol`, `Permiso`, `ModuloSistema`, `Bitacora`, `Media`, `Backup` | 38 | Administrador |
| **03** | Gestión de Personal (RRHH) | `Empleado`, `Cargo`, `Asistencia`, `Horario`, `Turno`, `PermisoLaboral`, `TipoPermiso` | 42 | RRHH / Admin |
| **04** | **Reservaciones, Salón y Mesas (Jorge)** | `Reservacion`, `Mesas`, `Areas` | 19 | Público / Salón |
| **05** | **Pedidos y Punto de Venta (Jorge)** | `Pedido`, `PedidoPublico` | 11 | Público / POS |
| **06** | **Menú, Promociones y Noticias (Jorge)** | `Menu`, `Categoria`, `Promocion`, `Noticia` | 27 | Público / Admin |
| **07** | Inventario y Proveedores | `Insumo`, `CategoriaInsumo`, `Proveedor` | 30 | Almacén / Compras |
| **08** | Clientes y Relaciones | `Cliente` | 7 | Ventas / Admin |
| **09** | Sistema, Notificaciones e IA | `Notification`, `Reporte`, `Ayuda`, `Papelera`, `Home`, `Dashboard` | 16 | Universal / IA |

---

## 3. Catálogo Detallado por Dominio

### 01. Autenticación, Sesión y Perfil

#### `POST /?page=Login`
* **Acciones:**
  * `peticion=sesion`: Inicia sesión en el sistema.
    * *Parámetros:* `particle` (V/E), `CI` (dígitos), `password`, `g-recaptcha-response`.
    * *Respuesta:* Redirección HTTP 302 hacia `Dashboard` o `Home` según rol.
  * `peticion=verificar_cedula`: Comprueba si la persona ya está registrada.
    * *Parámetros:* `nacionalidad` (V/E), `cedula`.
    * *Respuesta:* `{"resultado": 200, "existe": true|false}`.
  * `peticion=registrar`: Registro externo de nuevos clientes.
    * *Parámetros:* `nacionalidad`, `cedula`, `nombre`, `apellido`, `fecha_nacimiento`, `sexo`, `telefono`, `correo`, `direccion`, `username`, `clave`, `rclave`.
    * *Reglas:* `username` solo admite letras (sin números ni símbolos). Cédula debe respetar formato legal venezolano.

#### `GET /?page=LogOut`
* Destruye la sesión PHP, revoca cookies de autenticación y redirige a la pantalla de login.

#### `POST /?page=RecuperacionClave`
* **Acciones (`page=recuperar-password`, `page=verificar-codigo`, `page=restablecer-password`):**
  * `peticion=recuperar`: Envía un código OTP de 6 dígitos al correo electrónico del usuario.
  * `peticion=verificar`: Comprueba la vigencia y exactitud del código de 6 dígitos.
  * `peticion=restablecer`: Guarda la nueva clave una vez verificado el código.

#### `POST /?page=Perfil`
* **Acciones:**
  * `peticion=forzar-cambiar-clave`: Actualización obligatoria en primer login.
  * `peticion=actualizar-perfil`: Modifica teléfono, correo, dirección y datos personales.
  * `peticion=cambiar-clave`: Cambio voluntario requiriendo confirmación de clave actual.
  * `peticion=obtener-actividad`: Historial de accesos y movimientos del usuario.

---

### 04. Reservaciones, Salón y Mesas (Módulos de Jorge)

#### `POST /?page=Reservacion`
* **Autenticación:** Público para clientes / Administrativo para personal.
* **Acciones:**
  1. `peticion=listar`: Devuelve eventos de la agenda. Si `type=publico`, anonimiza las reservaciones de terceros mostrando únicamente "Ocupado".
  2. `peticion=validar`: Validación en tiempo real de reglas de negocio.
     * **Regla 1 (Fechas Pasadas):** No se permiten reservaciones en fechas anteriores a hoy.
     * **Regla 2 (Solapamiento Horario):** Impide que dos reservaciones compartan mesa en horarios colisionantes.
     * **Regla 3 (Capacidad):** La cantidad de personas (1 a 50) no puede exceder la capacidad de la mesa seleccionada.
  3. `peticion=registrar`: Inserta la reservación en estado `PENDIENTE`.
  4. `peticion=modificar`: Actualiza horario, comensales, mesa o estado (`PENDIENTE`, `CONFIRMADA`, `CANCELADA`, `COMPLETADA`).
  5. `peticion=eliminar`: Cancela o retira una reservación liberando la mesa.

#### `POST /?page=Mesas`
* **Acciones:**
  * `peticion=consultar`: Lista mesas con su número, capacidad (1 a 50), estado actual y área vinculada.
  * `peticion=cambiar_estado`: Actualiza el estado operativo (`DISPONIBLE`, `LIBRE`, `OCUPADA`, `MANTENIMIENTO`).
  * `peticion=cambiar_estatus`: Desactivación/activación lógica (`0` o `1`).
  * `peticion=consultar_por_area`: Filtra mesas disponibles por área del restaurante.

---

### 05. Pedidos y Punto de Venta (Módulos de Jorge)

#### `POST /?page=Pedido`
* **Acciones:**
  1. `peticion=listar`: Lista órdenes con número incremental descendente, cliente, método de pago y URLs de comprobantes.
  2. `peticion=buscar`: Desglose detallado del pedido (`detalle_pedido`), cliente y comprobante de pago.
  3. `peticion=cambiar_estado`: **Máquina de Estados Finita:**
     * `PENDIENTE` $\to$ `CONFIRMADO` $\lor$ `CANCELADO`.
     * `CONFIRMADO` $\to$ `PREPARANDO` $\lor$ `LISTO` $\lor$ `CANCELADO`.
     * `PREPARANDO` $\to$ `LISTO` $\lor$ `CANCELADO`.
     * `LISTO` $\to$ `ENTREGADO` $\lor$ `PAGADO` $\lor$ `CANCELADO`.
     * `ENTREGADO` $\to$ `PAGADO`.
     * `PAGADO` / `CANCELADO`: Terminales.
     * **Regla Insumos:** Descuenta insumos al entrar a `PREPARANDO` (si requiere cocina) o `LISTO`. Restaura stock al `CANCELAR`.
     * **Regla Mesas:** Al pasar a `ENTREGADO`, `PAGADO` o `CANCELADO`, libera automáticamente la mesa (`UPDATE mesa SET estado = 'DISPONIBLE'`).
  4. `peticion=obtener_comprobante`: Extrae la imagen del comprobante de pago polimórfico en la base de seguridad (`entidad_tipo = 'PAGO'`).
  5. `peticion=verificar_stock`: Comprueba disponibilidad de insumos para cada plato del carrito.
  6. `peticion=crear_pos`: Registra ventas presenciales en caja.

#### `POST /?page=PedidoPublico`
* **Acciones:**
  * `action=buscarProducto` (GET): Datos del producto para el carrito web.
  * `action=verificar_stock` (POST): Valida stock antes de confirmar pedido público.
  * `POST` (registro directo): Crea cliente si no existe, inserta pedido web, detalles con extras/ingredientes removidos y registra pago móvil/transferencia con soporte de imagen de comprobante.

---

### 06. Menú, Promociones y Noticias (Módulos de Jorge)

#### `POST /?page=Promocion`
* **Acciones:**
  1. `peticion=consultar`: Lista promociones agrupando productos asociados con `GROUP_CONCAT`.
  2. `peticion=registrar`: Inserta promoción y múltiples registros en `planificador_promocion`, vinculando imagen polimórfica en `imagen`.
  3. `peticion=modificar`: Actualiza vigencia, horas, tipo de descuento (`PORCENTAJE` o `FIJO`) y reconstruye el planificador.
  4. `peticion=crear_noticia`: **Regla de Negocio Integrada:** Calcula el desglose financiero (ahorro, subtotal original, total con descuento) y redacta automáticamente una noticia publicitaria para el blog/portal público.
  5. `peticion=eliminar`: Borra promoción, planificación e imagen asociada.

#### `POST /?page=Noticia`
* **Acciones:**
  1. `peticion=consultar`: Lista noticias en panel administrativo con autor y fecha.
  2. `peticion=registrar`: Publica noticia con sanitización de caracteres especiales españoles, manejo de galería múltiple y disparo de notificación a todos los usuarios.
  3. `peticion=modificar`: Edición de contenido con trazabilidad en Bitácora de Auditoría.
  4. `peticion=eliminar`: Eliminación de noticia e imágenes vinculadas.
  5. `peticion=eliminar_imagen` / `marcar_principal`: Gestión de imagen de portada de la noticia.
  6. `?page=Noticia&type=publico` (GET): Portal público con filtros por autor, mes, año y categoría.
  7. `?page=Noticia&type=detalle&id=...` (GET): Vista completa de lectura de la noticia.

---

## 4. Guía de Uso en Postman y Automatización

Para que puedas verificar, ejecutar y documentar todas las APIs con tu propia cuenta en Postman:

### 4.1. Archivos Generados
1. **Colección Postman (v2.1):** [`postman/SICGOV_API_Collection.postman_collection.json`](file:///c:/xampp/htdocs/SICGOV/postman/SICGOV_API_Collection.postman_collection.json)
   * 9 Carpetas funcionales.
   * 78 Solicitudes preconfiguradas con cabeceras `X-Requested-With`, cuerpos URL-encoded y scripts de aserción `pm.test()`.
2. **Entorno Postman (Environment):** [`postman/SICGOV_Environment.postman_environment.json`](file:///c:/xampp/htdocs/SICGOV/postman/SICGOV_Environment.postman_environment.json)
   * `base_url`: `http://localhost/SICGOV/public`
   * Credenciales administrativas de prueba.

### 4.2. Pasos para Importar y Usar con tu Cuenta en Postman
1. Abre tu aplicación de **Postman Desktop** o entra en [web.postman.co](https://web.postman.co).
2. Haz clic en el botón superior **Import** (o `Ctrl + O`).
3. Arrastra o selecciona los dos archivos:
   * `postman/SICGOV_API_Collection.postman_collection.json`
   * `postman/SICGOV_Environment.postman_environment.json`
4. En la esquina superior derecha de Postman, selecciona el entorno activo: **SICGOV Local Environment**.
5. Para autenticarte, ejecuta la solicitud:
   * Carpeta `01. Autenticación, Sesión y Perfil` $\to$ **`Iniciar Sesión (Login)`**.
   * Postman almacenará automáticamente la cookie `PHPSESSID` en el jar de cookies y todas las peticiones protegidas subsecuentes se autenticarán de inmediato.
6. Haz clic derecho sobre la colección **SICGOV - Suite Completa de APIs** y selecciona **Run Collection** para ejecutar todas las pruebas en lote y obtener el reporte de calidad visual.

