<?php
require_once __DIR__ . '/../includes/db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Método no permitido']);

$email = filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL);
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$password = $_POST['password'] ?? '';

if(!$email || !$nombre || !$apellido || !$telefono || !$dni || strlen($password) < 8){
    json_response(['error'=>'Datos inválidos. Completar todos los campos y contraseña de al menos 8 caracteres.']);
}


$stmt = $mysqli->prepare('SELECT id_usuario FROM usuarios WHERE email = ?');
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){ json_response(['error'=>'El email ya está registrado.']); }
$stmt->close();

$stmt = $mysqli->prepare('SELECT id_cliente FROM clientes WHERE dni = ?');
$stmt->bind_param('s',$dni);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){ json_response(['error'=>'El DNI ya está registrado.']); }
$stmt->close();

$stmt = $mysqli->prepare('INSERT INTO clientes (dni,nombre,apellido,telefono) VALUES(?,?,?,?)');
$stmt->bind_param('ssss',$dni,$nombre,$apellido,$telefono);
$ok = $stmt->execute();
if(!$ok) json_response(['error'=>'Error al crear cliente.']);
$id_cliente = $mysqli->insert_id;
$stmt->close();

$hash = password_hash($password, PASSWORD_DEFAULT);
$has_col = $mysqli->query("SHOW COLUMNS FROM usuarios LIKE 'id_cliente'")->num_rows > 0;
if($has_col){
    $stmt = $mysqli->prepare('INSERT INTO usuarios (email,password_hash,nombre,apellido,telefono,id_cliente) VALUES(?,?,?,?,?,?)');
    $stmt->bind_param('sssssi', $email, $hash, $nombre, $apellido, $telefono, $id_cliente);
}else{
    $stmt = $mysqli->prepare('INSERT INTO usuarios (email,password_hash,nombre,apellido,telefono) VALUES(?,?,?,?,?)');
    $stmt->bind_param('sssss', $email, $hash, $nombre, $apellido, $telefono);
}
$ok = $stmt->execute();
if(!$ok) json_response(['error'=>'Error al crear usuario.']);
$stmt->close();

json_response(['success'=>true,'message'=>'Cuenta creada correctamente.']);
?>
