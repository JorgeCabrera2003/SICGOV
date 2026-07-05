<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;

/**
 * ReportService - Módulo de Reportes Universales (SICGOV)
 * 
 * Basado en el principio de "Separación de Preocupaciones" y "DRY".
 * Actúa como una librería de dominio para el renderizado de documentos PDF
 * delegando la complejidad visual al motor CSS3 de Dompdf.
 * 
 * @author Senior Software Architect
 */
class ReportService
{
    private Dompdf $dompdf;
    private array $info = [];
    private array $columns = [];
    private array $data = [];
    private string $paperSize = 'letter';
    private string $orientation = 'portrait';
    private string $templatePath;

    public function __construct()
    {
        // Configuración de opciones siguiendo el principio de robustez
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true); // Para cargar logos/imágenes externas si es necesario
        $options->set('defaultFont', 'Helvetica');
        
        $this->dompdf = new Dompdf($options);
        $this->templatePath = dirname(__DIR__, 2) . '/resources/views/reports/report_template.php';
    }

    /**
     * Configuración fluida del reporte (Fluent Interface)
     * "Evita las sorpresas": El estado del objeto se configura antes de la acción.
     */
    public function setup(array $info, array $columns, array $data, array $config = []): self
    {
        $this->info = $info;
        $this->columns = $columns;
        $this->data = $data;
        
        $this->paperSize = $config['paper'] ?? 'letter';
        $this->orientation = $config['orientation'] ?? 'portrait';
        
        return $this;
    }

    /**
     * Genera el PDF y lo envía al navegador
     * Cumple con la delegación total de renderizado.
     */
    public function render(string $filename = 'reporte.pdf'): void
    {
        if (!file_exists($this->templatePath)) {
            throw new Exception("Error Crítico: Plantilla de reporte no encontrada.");
        }

        // Buffer de salida para capturar la inyección de datos en la plantilla
        ob_start();
        
        // Variables disponibles dentro de report_template.php (Principio de Aislamiento)
        $info = $this->info;
        $columns = $this->columns;
        $data = $this->data;
        
        require $this->templatePath;
        $html = ob_get_clean();

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($this->paperSize, $this->orientation);
        $this->dompdf->render();

        // Salida al navegador (Attachment => false para previsualizar en el navegador)
        $this->dompdf->stream($filename, ["Attachment" => false]);
    }

    /**
     * Alternativa para obtener el contenido binario si se desea guardar en disco
     */
    public function getOutput(): string
    {
        if (!file_exists($this->templatePath)) {
            throw new Exception("Error Crítico: Plantilla de reporte no encontrada.");
        }

        ob_start();
        $info = $this->info;
        $columns = $this->columns;
        $data = $this->data;
        require $this->templatePath;
        $html = ob_get_clean();

        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($this->paperSize, $this->orientation);
        $this->dompdf->render();
        
        return $this->dompdf->output();
    }
}
