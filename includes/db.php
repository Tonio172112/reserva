<?php
//Variables de conexión a la base de datos
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'divino_eter');

// Crea la conexión con un mysqli
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
http_response_code(500);
echo json_encode(['error' => 'Error de conexión a la base de datos']);
exit;
}
$mysqli->set_charset('utf8mb4');

function json_response($data){
header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);
exit;
}