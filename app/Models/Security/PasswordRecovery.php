<?php

namespace App\Models\Security;

use App\Core\Database;
use App\Helpers\Helper;
use PDO;

class PasswordRecovery extends Database
{
    public function __construct()
    {
    }

    public function guardarCodigo($correo, $codigo, $minutos_validez = 15)
    {
        try {
            $this->LlamarConexion("security");
            
            // Inactivar códigos anteriores
            $sql_update = "UPDATE recuperacion_clave SET usado = 1 WHERE correo = :correo";
            $stm_upd = $this->LlamarConexion()->prepare($sql_update);
            $stm_upd->bindParam(':correo', $correo);
            $stm_upd->execute();
            
            $sql = "INSERT INTO recuperacion_clave (correo, codigo, fecha_expiracion) 
                    VALUES (:correo, :codigo, DATE_ADD(NOW(), INTERVAL :minutos MINUTE))";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':correo', $correo);
            $stm->bindParam(':codigo', $codigo);
            $stm->bindParam(':minutos', $minutos_validez, PDO::PARAM_INT);
            $stm->execute();
            $this->DestruirConexion();
            return true;
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return false;
        }
    }

    public function validarCodigo($correo, $codigo)
    {
        try {
            $this->LlamarConexion("security");
            $sql = "SELECT id FROM recuperacion_clave 
                    WHERE correo = :correo AND codigo = :codigo AND usado = 0 AND fecha_expiracion > NOW()
                    ORDER BY id DESC LIMIT 1";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':correo', $correo);
            $stm->bindParam(':codigo', $codigo);
            $stm->execute();
            if ($stm->rowCount() > 0) {
                return true;
            }
            return false;
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return false;
        }
    }

    public function marcarComoUsado($correo, $codigo)
    {
        try {
            $this->LlamarConexion("security");
            $sql = "UPDATE recuperacion_clave SET usado = 1 WHERE correo = :correo AND codigo = :codigo";
            $stm = $this->LlamarConexion()->prepare($sql);
            $stm->bindParam(':correo', $correo);
            $stm->bindParam(':codigo', $codigo);
            $stm->execute();
            $this->DestruirConexion();
            return true;
        } catch (\PDOException $e) {
            Helper::ErrorLog($e->getMessage() . " en " . $e->getFile() . " línea " . $e->getLine());
            return false;
        }
    }
}
