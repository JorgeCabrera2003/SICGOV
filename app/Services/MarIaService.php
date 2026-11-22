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
        if (class_exists(\Dotenv\Dotenv::class) && defined('BASE_PATH')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
            $dotenv->safeLoad();
        }

        $mariaUrl = $_ENV['MARIA_URL'] ?? getenv('MARIA_URL') ?: 'http://127.0.0.1:8090';
        $this->endpointUrl = rtrim($mariaUrl, '/') . '/classify';
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

        // Si falló por host inaccesible (ej. host 'mar-ia' en entorno local), reintentar automáticamente con 127.0.0.1
        if ($error && strpos($this->endpointUrl, '127.0.0.1') === false) {
            $fallbackUrl = 'http://127.0.0.1:8090/classify';
            $chFb = curl_init($fallbackUrl);
            curl_setopt($chFb, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($chFb, CURLOPT_POST, true);
            curl_setopt($chFb, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($chFb, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($chFb, CURLOPT_TIMEOUT, 15);

            $fbResponse = curl_exec($chFb);
            $fbHttpCode = curl_getinfo($chFb, CURLINFO_HTTP_CODE);
            $fbError = curl_error($chFb);
            curl_close($chFb);

            if (!$fbError && $fbHttpCode === 200) {
                $this->endpointUrl = $fallbackUrl;
                $response = $fbResponse;
                $httpCode = $fbHttpCode;
                $error = '';
            }
        }

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
