# Documentación del Motor de Reportes Universales - SICGOV

## 1. Introducción
El sistema de reportes de SICGOV ha sido diseñado bajo el concepto de **Librería de Dominio Cohesiva**. A diferencia de los sistemas tradicionales donde cada reporte tiene su propia lógica de diseño y datos, este motor utiliza una **Arquitectura Universal** que separa la obtención de datos de su representación visual.

## 2. Arquitectura del Sistema
El motor se compone de tres capas fundamentales:

### A. Capa de Datos (`ReporteController`)
Actúa como un **Mapeador Universal**. Utiliza la expresión `match` de PHP 8.1 para definir la configuración de cada reporte:
- **Fetch**: Función anónima (*closure*) que obtiene los datos del modelo.
- **Map**: Función que transforma los datos crudos en el formato requerido para la tabla.
- **Config**: Define títulos y columnas.

### B. Capa de Servicio (`ReportService`)
Es el motor de renderizado (Wrapper de Dompdf). Su responsabilidad es:
- Configurar las opciones de PDF (Papel, Orientación).
- Inyectar el Logo (vía Base64).
- Renderizar el HTML final delegando la paginación al motor CSS3.

### C. Capa Visual (`report_template.php`)
Plantilla única basada en **CSS3 Paged Media**. 
- **Header/Footer**: Se repiten automáticamente en cada página.
- **Paginación**: Calculada por el navegador de Dompdf (evita cálculos manuales en PHP).

---

## 3. Cómo añadir un Nuevo Reporte (Guía Paso a Paso)

Para añadir un nuevo reporte (ejemplo: "Auditoría de Bitácora"), solo debes seguir estos 3 pasos:

### Paso 1: Definir la configuración en `ReporteController.php`
Añade un nuevo caso al bloque `match` dentro del método `generarReporte`:

```php
'bitacora' => [
    'titulo'  => 'Auditoría de Bitácora del Sistema',
    'columns' => ['Fecha', 'Usuario', 'Acción', 'Detalle'],
    'fetch'   => fn() => (new Bitacora())->ListarTodo(), // Llamada al modelo
    'map'     => fn($b) => [
        date('d/m/Y H:i', strtotime($b['fecha'])),
        $b['username'],
        $b['accion'],
        $b['detalle']
    ]
],
```

### Paso 2: Añadir la interfaz en `resources/views/reports/index.php`
Crea una nueva tarjeta (card) para el reporte:

```html
<button type="button" class="btn btn-primary btn-config-report" data-tipo="bitacora">
    <i class="bi bi-file-earmark-pdf me-2"></i>Generar Reporte
</button>
```

### Paso 3: ¡Listo!
El sistema automáticamente:
1. Abrirá el modal de configuración.
2. Permitirá elegir orientación/papel.
3. Inyectará el logo y los metadatos.
4. Generará el PDF con el diseño estándar del restaurante.

---

## 4. Características Técnicas Avanzadas

### 🖼️ Manejo de Logos e Imágenes
Para garantizar la compatibilidad en Windows/Linux, el sistema convierte el logo a **Base64** antes de enviarlo al motor. Esto evita errores de rutas absolutas o permisos de archivos.

### 🌓 Soporte de Modo Oscuro (UI)
La interfaz de administración utiliza **Variables CSS Adaptativas**:
- `--report-card-bg`: Cambia de blanco a `#1A1C20` automáticamente.
- `.text-gradient`: Se ajusta para ser legible en fondos claros y oscuros.

### 🕒 Sincronización Horaria
El sistema está forzado a la zona horaria `America/Caracas`, asegurando que todos los reportes tengan la marca de tiempo exacta de Venezuela, independientemente de la ubicación del servidor.

---

## 5. Uso Programático del Servicio
Si deseas generar un reporte desde cualquier otro controlador (sin pasar por el Centro de Reportes), puedes invocar al servicio directamente:

```php
use App\Services\ReportService;

public function imprimirTicket($id) {
    // 1. Obtener datos
    $datos = $this->model->obtenerDetalle($id);
    
    // 2. Preparar el servicio
    $reporte = new ReportService();
    
    // 3. Configurar metadatos
    $info = [
        'titulo' => 'Ticket de Venta',
        'subtitulo' => 'Folio: ' . $id,
        'usuario' => 'Cajero 01',
        'logo' => $this->obtenerLogoBase64() // Opcional
    ];
    
    // 4. Definir columnas y datos de tabla
    $columnas = ['Cant', 'Producto', 'Precio'];
    $filas = [
        ['1', 'Cerveza Zulia', '120.00'],
        ['2', 'Hamburguesa especial', '450.00']
    ];

    // 5. Renderizar
    $reporte->setup($info, $columnas, $filas, ['paper' => 'a4'])
            ->render("Ticket_{$id}.pdf");
}
```

---

## 6. Mejores Prácticas Sugeridas
1. **DRY (Don't Repeat Yourself)**: No crees nuevos archivos `.php` para el diseño de un reporte. Usa siempre la plantilla universal.
2. **Delegación CSS**: Si necesitas cambiar el estilo de las tablas, hazlo en `report_template.php`.
3. **Paginación**: Nunca uses `array_chunk` para dividir datos manualmente. Deja que el CSS `page-break-inside: avoid` haga su trabajo.

---
**Versión**: 2.0 (Motor Universal)
