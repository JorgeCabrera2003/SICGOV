<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $info['titulo'] ?? 'Reporte SICGOV' ?></title>
    <style>
        /**
         * SICGOV - Eco-Friendly Reporting Style
         * Basado en "El Programador Pragmático": 
         * No te repitas (DRY) y deja que las herramientas hagan su trabajo.
         */
        
        @page {
            margin: 1.5cm 1cm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333; /* Gris oscuro para ahorro de tinta */
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        /* 1. Encabezado Fijo (Position Fixed) */
        header {
            position: fixed;
            top: -1cm;
            left: 0;
            right: 0;
            height: 2.5cm;
            border-bottom: 0.5pt solid #eee;
        }

        /* 2. Pie de Página Dinámico (CSS Counters) */
        footer {
            position: fixed;
            bottom: -0.5cm;
            left: 0;
            right: 0;
            height: 1cm;
            text-align: center;
            border-top: 0.5pt solid #eee;
            padding-top: 5px;
            color: #777;
        }

        footer .page-number:after {
            content: "Página " counter(page);
        }

        /* 3. Contenedor de Datos con margen para no solapar header/footer */
        main {
            margin-top: 2.5cm;
            margin-bottom: 1cm;
        }

        /* 4. Estética de la Tabla Universal */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1cm;
        }

        /* Cabecera persistente en cada página */
        thead {
            display: table-header-group;
        }

        th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 8px 5px;
            border-bottom: 1pt solid #ccc;
            text-align: left;
        }

        td {
            padding: 6px 5px;
            border-bottom: 0.5pt solid #eee;
            vertical-align: top;
        }

        /* Evitar saltos de página a mitad de una fila */
        tr {
            page-break-inside: avoid;
        }

        /* Estilos de Información del Reporte */
        .info-header {
            width: 100%;
        }

        .info-header .title {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a1a;
            margin: 0;
        }

        .info-header .subtitle {
            font-size: 11px;
            color: #666;
            margin: 2px 0;
        }

        .meta-data {
            text-align: right;
            font-size: 9px;
            color: #888;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

    </style>
</head>
<body>

    <!-- Estructura Fija de Dompdf -->
    <header>
        <table class="info-header">
            <tr>
                <td>
                    <div class="title"><?= mb_strtoupper($info['titulo'] ?? 'REPORTE GENERAL') ?></div>
                    <div class="subtitle"><?= $info['subtitulo'] ?? 'Detalle informativo del sistema' ?></div>
                </td>
                <td class="meta-data">
                    <div><strong>Fecha:</strong> <?= date('d/m/Y h:i A') ?></div>
                    <div><strong>Generado por:</strong> <?= $info['usuario'] ?? 'Sistema SICGOV' ?></div>
                </td>
            </tr>
        </table>
    </header>

    <footer>
        <div class="page-number"></div>
        <div style="font-size: 8px; margin-top: 2px;">
            SICGOV - Sistema de Información Complementario Good Vibes &copy; <?= date('Y') ?>
        </div>
    </footer>

    <main>
        <!-- Tabla Dinámica Universal -->
        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $column): ?>
                        <th><?= htmlspecialchars($column) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data)): ?>
                    <tr>
                        <td colspan="<?= count($columns) ?>" class="text-center" style="padding: 20px;">
                            No se encontraron registros para mostrar en este periodo.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars($value ?? '-') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Sección de Resumen o Firmas si se requiere (Inyectado opcionalmente) -->
        <?php if (!empty($info['resumen'])): ?>
            <div style="margin-top: 20px; padding: 10px; background-color: #f9f9f9; border-radius: 5px;">
                <div class="fw-bold" style="margin-bottom: 5px;">RESUMEN DEL REPORTE:</div>
                <div style="font-size: 9px;"><?= nl2br(htmlspecialchars($info['resumen'])) ?></div>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
