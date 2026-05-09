<?php

namespace App\Models\System;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class Papelera extends Database
{
    private $modulos = [
        'NOTICIAS' => [
            'tabla' => 'noticia',
            'pk' => 'id_noticia',
            'label' => 'titulo',
            'db' => 'security'
        ],
        'INGREDIENTES' => [
            'tabla' => 'ingrediente',
            'pk' => 'id_ingrediente',
            'label' => 'nombre_ingrediente',
            'db' => 'business'
        ],
        'USUARIOS' => [
            'tabla' => 'usuario',
            'pk' => 'cedula',
            'label' => 'username',
            'db' => 'security'
        ],
        'CLIENTES' => [
            'tabla' => 'cliente c',
            'pk' => 'c.cedula',
            'label' => 'CONCAT(p.nombre, " ", p.apellido)',
            'db' => 'business',
            'join' => 'INNER JOIN persona p ON c.cedula = p.cedula',
            'where' => 'estatus = 0'
        ],

        'PRODUCTOS' => [
            'tabla' => 'producto',
            'pk' => 'id_producto',
            'label' => 'nombre_producto',
            'db' => 'business'
        ],
        'CATEGORIAS_INGREDIENTE' => [
            'tabla' => 'categoria_ingrediente',
            'pk' => 'id_categoria',
            'label' => 'nombre',
            'db' => 'business'
        ]
    ];

    public function Transaccion($peticion)
    {
        return match ($peticion['peticion']) {
            'consultar' => $this->ConsultarPapelera(),
            'restaurar' => $this->RestaurarRegistro($peticion['modulo'], $peticion['id']),
            'eliminar_permanente' => $this->EliminarPermanente($peticion['modulo'], $peticion['id']),
            default => [
                'response' => ['resultado' => 400, 'mensaje' => 'Petición no válida'],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Bad Request']
            ]
        };
    }

    private function ConsultarPapelera()
    {
        $datos = [];
        $errores = [];
        try {
            foreach ($this->modulos as $key => $conf) {
                try {
                    $pdo = \App\Core\Database::getConnection($conf['db']);
                    
                    $join = $conf['join'] ?? "";
                    $where = $conf['where'] ?? "estatus = 0";

                    $sql = "SELECT '{$key}' as modulo, {$conf['pk']} as id, {$conf['label']} as descripcion, 
                            '{$conf['tabla']}' as tabla, '{$conf['db']}' as db
                            FROM {$conf['tabla']} 
                            {$join}
                            WHERE {$where}";
                    
                    $stm = $pdo->prepare($sql);
                    $stm->execute();
                    $registros = $stm->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($registros as &$reg) {
                        $reg['id_original'] = $reg['id'];
                    }

                    $datos = array_merge($datos, $registros);
                } catch (\PDOException $e) {
                    $errores[] = "Error en {$key}: " . $e->getMessage();
                    Helper::ErrorLog("Error en módulo Papelera ({$key}): " . $e->getMessage());
                    // Continuamos con el siguiente módulo
                }
            }

            return [
                'response' => [
                    'resultado' => 200, 
                    'datos' => $datos, 
                    'advertencias' => $errores
                ],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK']
            ];
        } catch (\Exception $e) {
            Helper::ErrorLog("Error general en ConsultarPapelera: " . $e->getMessage());
            return [
                'response' => ['resultado' => 500, 'mensaje' => 'Error al consultar la papelera'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Internal Server Error']
            ];
        }
    }



    private function RestaurarRegistro($modulo, $id)
    {
        if (!isset($this->modulos[$modulo])) {
            return [
                'response' => ['resultado' => 400, 'mensaje' => 'Módulo no soportado'],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Bad Request']
            ];
        }

        $conf = $this->modulos[$modulo];
        $tabla_pura = explode(' ', $conf['tabla'])[0];
        $pk_pura = strpos($conf['pk'], '.') !== false ? explode('.', $conf['pk'])[1] : $conf['pk'];

        try {
            $pdo = \App\Core\Database::getConnection($conf['db']);
            $sql = "UPDATE {$tabla_pura} SET estatus = 1 WHERE {$pk_pura} = :id";
            $stm = $pdo->prepare($sql);
            $stm->execute([':id' => $id]);

            Helper::Bitacora('RESTAURAR', $modulo, "Se restauró el registro ID: {$id} del módulo {$modulo}");

            return [
                'response' => ['resultado' => 200, 'mensaje' => 'Registro restaurado correctamente'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK']
            ];
        } catch (\PDOException $e) {
            Helper::ErrorLog("Error en RestaurarRegistro: " . $e->getMessage());
            return [
                'response' => ['resultado' => 500, 'mensaje' => 'Error al restaurar el registro'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Internal Server Error']
            ];
        }
    }

    private function EliminarPermanente($modulo, $id)
    {
        if (!isset($this->modulos[$modulo])) {
            return [
                'response' => ['resultado' => 400, 'mensaje' => 'Módulo no soportado'],
                'HTTP_STATUS' => ['codigo' => 400, 'mensaje' => 'Bad Request']
            ];
        }

        $conf = $this->modulos[$modulo];
        $tabla_pura = explode(' ', $conf['tabla'])[0];
        $pk_pura = strpos($conf['pk'], '.') !== false ? explode('.', $conf['pk'])[1] : $conf['pk'];

        try {
            $pdo = \App\Core\Database::getConnection($conf['db']);
            $sql = "DELETE FROM {$tabla_pura} WHERE {$pk_pura} = :id";
            $stm = $pdo->prepare($sql);
            $stm->execute([':id' => $id]);

            Helper::Bitacora('ELIMINACION_PERMANENTE', $modulo, "Se eliminó permanentemente el registro ID: {$id} del módulo {$modulo}");

            return [
                'response' => ['resultado' => 200, 'mensaje' => 'Registro eliminado permanentemente'],
                'HTTP_STATUS' => ['codigo' => 200, 'mensaje' => 'OK']
            ];
        } catch (\PDOException $e) {
            Helper::ErrorLog("Error en EliminarPermanente: " . $e->getMessage());
            return [
                'response' => ['resultado' => 500, 'mensaje' => 'Error al eliminar permanentemente el registro'],
                'HTTP_STATUS' => ['codigo' => 500, 'mensaje' => 'Internal Server Error']
            ];
        }
    }


}
