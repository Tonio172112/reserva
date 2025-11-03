<?php
require_once __DIR__ . '/../includes/db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Método no permitido']);

$email = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$password = $_POST['password'] ?? '';

if(!$email || !$nombre || !$apellido || !$telefono || strlen($password) < 8){
    json_response(['error'=>'Datos inválidos. Completar todos los campos y contraseña de al menos 8 caracteres.']);
}

$stmt = $mysqli->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){ json_response(['error'=>'El email ya está registrado.']); }
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $mysqli->prepare('INSERT INTO usuarios (email,password_hash,nombre,apellido,telefono) VALUES(?,?,?,?,?)');
$stmt->bind_param('sssss', $email, $hash, $nombre, $apellido, $telefono);
$ok = $stmt->execute();
if(!$ok) json_response(['error'=>'Error al crear usuario.']);
$stmt->close();
json_response(['success'=>true,'message'=>'Cuenta creada correctamente.']);
