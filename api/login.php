<?php
session_start(); 
require_once __DIR__ . '/../includes/db.php'; // carga conexión a la base de datos

// solo se permite POST para iniciar sesión
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Método no permitido']);
}

// obtiene email validado y contraseña enviada
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$hash = ''; // variable donde se almacenará el hash de la BD

// si faltan datos, se detiene
if (!$email || !$password) {
    json_response(['error' => 'Datos inválidos']);
}

// consulta del usuario por email
$stmt = $mysqli->prepare('SELECT id_usuario, password_hash, nombre FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($id_usuario, $hash, $nombre);

// si no existe el usuario, error genérico
if (!$stmt->fetch()) {
    json_response(['error' => 'Usuario o contraseña inválidos']);
}
$stmt->close();

// verifica contraseña contra el hash guardado
if (!password_verify($password, $hash)) {
    json_response(['error' => 'Usuario o contraseña inválidos']);
}

// login correcto: regenerar sesión y guardar datos mínimos
session_regenerate_id(true);
$_SESSION['user_id'] = $id_usuario;
$_SESSION['user_name'] = $nombre;

// respuesta JSON de éxito
json_response(['success' => true]);
