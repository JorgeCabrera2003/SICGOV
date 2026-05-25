<?php
require_once __DIR__ . '/../vendor/autoload.php';

$val1 = 'V-18844657';
$res1 = preg_match('/^[VEJPGvejpg]{1}[-][0-9]{7,15}$/', $val1);

echo "Regex 1 match: " . var_export($res1, true) . "\n";
