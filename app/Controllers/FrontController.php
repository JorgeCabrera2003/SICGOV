<?php

    namespace App\Controllers;
    class FrontController {

        private $dir;
        private $controller;        
        private $url;

        public function __construct() {

            if (isset($_REQUEST["url"])) {

                $this->url = $_REQUEST["url"];

                $this->dir = BASE_PATH . '/app/Controllers/';

                $this->controller = 'Controller.php';

                $this->getURL();

            } else {
                $this->show404();
            }
        }

        private function getURL() {

            $controllerName = ucfirst($this->url) . $this->controller;

            if(file_exists($this->dir . $controllerName)) {
                
                require_once($this->dir . $controllerName);
            
            } else {
                $this->show404();
            }
        }

        private function show404() {
            http_response_code(404);
            require_once BASE_PATH . '/resources/views/errors/404.php';
            exit;
        }

    }

?>