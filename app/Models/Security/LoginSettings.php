<?php
namespace App\Models\Security;

use App\Core\Database;

class LoginSettings {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection('security');
    }

    public function get_recaptcha_sitekey() {
        // Asumiendo que tienes una tabla de 'configuracion' en la BD de seguridad
        return "TU_SITE_KEY_AQUI"; 
    }

    public function get_recaptcha_secret() {
        return "TU_SECRET_KEY_AQUI";
    }
}