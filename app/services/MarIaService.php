<?php

namespace App\Services;

use Exception;

/**
 * MarIaService - Servicio de Integración con Microservicio MAR-IA
 * 
 * Sigue el principio de separación de preocupaciones. Aisla la lógica de 
 * comunicación HTTP con la Inteligencia Artificial del controlador de reportes.
 */
class MarIaService
{
    private string $endpointUrl;

    public function __construct()
    {
        // En un entorno de producción, esto debería venir de una variable de entorno o configuración.
        // Para este caso, el microservicio está en localhost:8090
        $this->endpointUrl = 'http://localhost:8090/classify';
    }

    /**
     * Envía una consulta a la IA MAR-IA
     * 
     * @param string $cedula La cédula del usuario activo
     * @param string $mensaje La solicitud o intención en lenguaje natural
     * @return array La respuesta estructurada de MAR-IA
     */
    public function consultar(string $cedula, string $mensaje): array
    {
        $payload = json_encode([
            'cedula' => $cedula,
            'mensaje' => $mensaje
        ]);

        $ch = curl_init($this->endpointUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        // Timeout para evitar que el sistema principal se cuelgue si la IA está caída
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'error' => "Error de conexión con MAR-IA: " . $error
            ];
        }

        if ($httpCode !== 200) {
            // Incluso si no es 200, podríamos tener un JSON de error devuelto (Ej. 503)
            $decoded = json_decode($response, true);
            if (isset($decoded['error'])) {
                return $decoded;
            }
            
            // Si el código es 422 (Unprocessable Entity), usualmente significa que el mensaje
            // no pasó la validación (ej. fue muy corto o vacío).
            if ($httpCode === 422) {
                return [
                    'error' => "Lo siento, no te entendi bien, puedes volver a enviar tu mensaje"
                ];
            }
            
            return [
                'error' => "El servicio de Inteligencia Artificial devolvió el código HTTP " . $httpCode
            ];
        }

        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => "La respuesta de MAR-IA no es un JSON válido."
            ];
        }

        return $result;
    }
}
