<?php
namespace App\Models\Security;

use App\Core\Database;

class LoginSettings {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection('security');
    }

    public function get_recaptcha_sitekey() {
        return "6LcPpHMsAAAAABkC9hEJsvWq_gBvRhH2kYpJwDub"; 
    }

    public function get_recaptcha_secret() {
        return "6LcPpHMsAAAAAO9TCacGt9083opA4L5oscbaL5mk";
    }
}