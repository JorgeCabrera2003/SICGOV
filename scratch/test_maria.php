<?php
require_once __DIR__ . '/../vendor/autoload.php';

$frases = [
    "Resumen del inventario en PDF",
    "dame los empleados",
    "reporte de clientes",
    "cuales son los platillos",
    "pedidos pendientes",
    "asistencia de hoy",
    "Generar reporte de usuarios inactivos"
];

foreach ($frases as $f) {
    $ch = curl_init("http://mar-ia:8090/classify");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["cedula" => "V-00000000", "mensaje" => $f]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($ch), true);
    echo "FRASE: '$f' => INTENCION: " . ($res['intencion'] ?? 'NULL') . " (Confianza: " . ($res['confianza'] ?? 0) . ")
";
}
