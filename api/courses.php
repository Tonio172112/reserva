<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión']);

$action = $_REQUEST['action'] ?? 'list';

// obtener id_cliente asociado al usuario
$stmt = $mysqli->prepare('SELECT id_cliente FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($id_cliente);
$stmt->fetch();
$stmt->close();

if(!$id_cliente) json_response(['error'=>'No se encontró cliente asociado al usuario']);

/* =======================================================
   LISTAR CURSOS
   =======================================================*/
if($action === 'list'){
    $stmt = $mysqli->prepare('SELECT id_curso,titulo,descripcion FROM cursos WHERE id_cliente = ?');
    $stmt->bind_param('i',$id_cliente);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['success'=>true,'data'=>$rows]);
}

/* =======================================================
   CREAR CURSO
   =======================================================*/
elseif($action === 'create'){
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    if(!$titulo) json_response(['error'=>'Título requerido']);

    $stmt = $mysqli->prepare('INSERT INTO cursos (id_cliente,titulo,descripcion) VALUES(?,?,?)');
    $stmt->bind_param('iss',$id_cliente,$titulo,$descripcion);
    $ok = $stmt->execute();

    if(!$ok) json_response(['error'=>'Error al crear curso']);
    json_response(['success'=>true,'id'=>$mysqli->insert_id]);
}

/* =======================================================
   ACTUALIZAR CURSO
   =======================================================*/
elseif($action === 'update'){
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if(!$id || !$titulo) json_response(['error'=>'Datos inválidos']);

    $stmt = $mysqli->prepare('UPDATE cursos SET titulo=?,descripcion=? WHERE id_curso = ? AND id_cliente = ?');
    $stmt->bind_param('ssii',$titulo,$descripcion,$id,$id_cliente);
    $ok = $stmt->execute();

    if(!$ok) json_response(['error'=>'No se pudo actualizar']);
    json_response(['success'=>true]);
}

/* =======================================================
   ELIMINAR CURSO + RESERVAS + LIBERAR HORARIOS
   =======================================================*/
elseif($action === 'delete'){
    $id = intval($_POST['id'] ?? 0);
    if(!$id) json_response(['error'=>'ID inválido']);

    // verificar que el curso pertenece al cliente actual
    $stmt = $mysqli->prepare('SELECT id_curso FROM cursos WHERE id_curso = ? AND id_cliente = ?');
    $stmt->bind_param('ii',$id,$id_cliente);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows === 0){
        $stmt->close();
        json_response(['error'=>'Curso no encontrado o no autorizado']);
    }
    $stmt->close();

    // obtener horarios asociados a reservas del curso
    $horarios = [];
    $stmt = $mysqli->prepare('SELECT id_horario FROM reserva WHERE id_curso = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $horarios[] = intval($row['id_horario']);
    $stmt->close();

    // eliminar reservas del curso
    $stmt = $mysqli->prepare('DELETE FROM reserva WHERE id_curso = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $stmt->close();

    // liberar horarios usados por esas reservas
    if(!empty($horarios)){
        $ids = implode(',', $horarios);
        $mysqli->query("UPDATE horariodisponible SET disponible = 1 WHERE id_horario IN ($ids)");
    }

    // eliminar el curso
    $stmt = $mysqli->prepare('DELETE FROM cursos WHERE id_curso = ?');
    $stmt->bind_param('i',$id);
    $ok = $stmt->execute();
    $stmt->close();

    if($ok) json_response(['success'=>true]);
    else json_response(['error'=>'No se pudo eliminar el curso']);
}

/* =======================================================
   ACCIÓN DESCONOCIDA
   =======================================================*/
else {
    json_response(['error'=>'Acción desconocida']);
}
?>
