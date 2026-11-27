<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Helpers/Helper.php';
require_once __DIR__ . '/../app/Helpers/RegexHelper.php';
require_once __DIR__ . '/../app/Models/System/Mesas.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$m = new \App\Models\System\Mesas();
$r = $m->Transaccion(['peticion' => 'consultar']);
print_r($r['response']['datos']);
