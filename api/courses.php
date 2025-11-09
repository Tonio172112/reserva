<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión']);
$action = $_REQUEST['action'] ?? 'list';
$stmt = $mysqli->prepare('SELECT id_cliente FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($id_cliente);
$stmt->fetch();
$stmt->close();
if(!$id_cliente) json_response(['error'=>'No se encontró cliente asociado al usuario']);

if($action === 'list'){
    $stmt = $mysqli->prepare('SELECT id_curso,titulo,descripcion FROM cursos WHERE id_cliente = ?');
    $stmt->bind_param('i',$id_cliente);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['success'=>true,'data'=>$rows]);
} elseif($action === 'create'){
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if(!$titulo) json_response(['error'=>'Título requerido']);
    $stmt = $mysqli->prepare('INSERT INTO cursos (id_cliente,titulo,descripcion) VALUES(?,?,?)');
    $stmt->bind_param('iss',$id_cliente,$titulo,$descripcion);
    $ok = $stmt->execute();
    if(!$ok) json_response(['error'=>'Error al crear curso']);
    json_response(['success'=>true,'id'=>$mysqli->insert_id]);
} elseif($action === 'update'){
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if(!$id || !$titulo) json_response(['error'=>'Datos inválidos']);
    $stmt = $mysqli->prepare('UPDATE cursos SET titulo=?,descripcion=? WHERE id_curso = ? AND id_cliente = ?');
    $stmt->bind_param('ssii',$titulo,$descripcion,$id,$id_cliente);
    $ok = $stmt->execute();
    if(!$ok) json_response(['error'=>'No se pudo actualizar']);
    json_response(['success'=>true]);
} elseif($action === 'delete'){
    $id = intval($_POST['id'] ?? 0);
    if(!$id) json_response(['error'=>'ID inválido']);
    $stmt = $mysqli->prepare('DELETE FROM cursos WHERE id_curso = ? AND id_cliente = ?');
    $stmt->bind_param('ii',$id,$id_cliente);
    $ok = $stmt->execute();
    if(!$ok) json_response(['error'=>'No se pudo eliminar']);
    json_response(['success'=>true]);
} else {
    json_response(['error'=>'Acción desconocida']);
}
?>
