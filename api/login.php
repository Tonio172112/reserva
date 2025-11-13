<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Método no permitido']);
}

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$hash = '';

if (!$email || !$password) {
    json_response(['error' => 'Datos inválidos']);
}

$stmt = $mysqli->prepare('SELECT id_usuario, password_hash, nombre FROM usuarios WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->bind_result($id_usuario, $hash, $nombre);

if (!$stmt->fetch()) {
    json_response(['error' => 'Usuario o contraseña inválidos']);
}
$stmt->close();
if (!password_verify($password, $hash)) {
    json_response(['error' => 'Usuario o contraseña inválidos']);
}

session_regenerate_id(true);
$_SESSION['user_id'] = $id_usuario;
$_SESSION['user_name'] = $nombre;

json_response(['success' => true]);

