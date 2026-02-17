<?php
// Configuracion de las dos conexiones a la base de datos
return [
    'security' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'goobv-usuarios',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ],
    'system' => [
        'driver' => 'mysql',
        'host' => 'localhost',
        'database' => 'goobv-sistema',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ]
];
