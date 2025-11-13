<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión']); // verificar sesión activa

$action = $_REQUEST['action'] ?? 'create'; // acción solicitada (create, list_days, list_times, etc.)

// obtener id_cliente asociado al usuario
$stmt = $mysqli->prepare('SELECT id_cliente FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($id_cliente);
$stmt->fetch();
$stmt->close();

/* =======================================================
   CREAR RESERVA
   =======================================================*/
if($action === 'create'){
    $id_horario = intval($_POST['id_horario'] ?? 0);
    $id_curso = intval($_POST['id_curso'] ?? 0);
    if(!$id_horario || !$id_curso) json_response(['error'=>'Datos incompletos']); // validar datos

    // verificar que el curso pertenece al cliente dueño de la cuenta
    if($id_cliente){
        $stmt = $mysqli->prepare('SELECT id_curso FROM cursos WHERE id_curso = ? AND id_cliente = ?');
        $stmt->bind_param('ii',$id_curso,$id_cliente);
        $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows === 0){ $stmt->close(); json_response(['error'=>'Curso inválido']); }
        $stmt->close();
    }

    // obtener información del horario
    $stmt = $mysqli->prepare('SELECT fecha,hora_inicio,hora_fin,disponible FROM horariodisponible WHERE id_horario = ?');
    $stmt->bind_param('i',$id_horario);
    $stmt->execute();
    $stmt->bind_result($fecha,$hora_inicio,$hora_fin,$disponible);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Horario no encontrado']); }
    $stmt->close();
    if(!$disponible) json_response(['error'=>'Horario no disponible']); // evitar dobles reservas

    // evitar duplicado de reserva del mismo curso en ese horario
    $stmt = $mysqli->prepare('SELECT id_reserva FROM reserva WHERE id_horario = ? AND id_curso = ?');
    $stmt->bind_param('ii',$id_horario,$id_curso);
    $stmt->execute(); $stmt->store_result();
    if($stmt->num_rows > 0){
        $stmt->close();
        json_response(['error'=>'Ya existe una reserva para ese horario y curso']);
    }

    // insertar la reserva confirmada
    $stmt = $mysqli->prepare('INSERT INTO reserva (id_horario,id_curso,id_usuario,fecha_reserva,hora_inicio,hora_fin,estado,creado_en) VALUES(?,?,?,?,?,?,"confirmada",NOW())');
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param('iiisss',$id_horario,$id_curso,$user_id,$fecha,$hora_inicio,$hora_fin);
    $ok = $stmt->execute();
    $stmt->close();
    if(!$ok) json_response(['error'=>'Error al crear reserva']);

    // marcar horario como no disponible
    $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 0 WHERE id_horario = ?');
    $stmt->bind_param('i',$id_horario);
    $stmt->execute(); $stmt->close();

    json_response(['success'=>true,'message'=>'Reserva confirmada para el curso.']);
}

/* =======================================================
   LISTAR DÍAS DISPONIBLES
   =======================================================*/
elseif($action === 'list_days'){
    $days = [];
    $res = $mysqli->query("SELECT DISTINCT fecha FROM horariodisponible WHERE disponible = 1 AND (dia='sábado' OR dia='domingo') ORDER BY fecha");
    if($res){
        while($row = $res->fetch_assoc()) $days[] = $row['fecha'];
    }
    json_response(['success'=>true,'days'=>$days]);
}

/* =======================================================
   LISTAR HORARIOS POR FECHA
   =======================================================*/
elseif($action === 'list_times'){
    $fecha = $_REQUEST['fecha'] ?? '';
    if(!$fecha) json_response(['success'=>true,'times'=>[]]); // sin fecha → vacío

    $times = [];
    $stmt = $mysqli->prepare("SELECT id_horario,hora_inicio,hora_fin FROM horariodisponible WHERE fecha = ? AND disponible = 1 ORDER BY hora_inicio");
    $stmt->bind_param('s',$fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $times[] = $row;
    $stmt->close();

    json_response(['success'=>true,'times'=>$times]);
}

/* =======================================================
   LISTAR RESERVAS DEL USUARIO
   =======================================================*/
elseif($action === 'list'){
    $stmt = $mysqli->prepare('SELECT r.id_reserva,r.id_horario,r.id_curso,r.fecha_reserva,r.hora_inicio,r.hora_fin,r.estado FROM reserva r WHERE r.id_usuario = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['success'=>true,'data'=>$rows]);
}

/* =======================================================
   CANCELAR RESERVA
   =======================================================*/
elseif($action === 'delete'){
    $id = intval($_POST['id'] ?? 0);
    if(!$id) json_response(['error'=>'ID inválido']);

    // obtener horario asociado a la reserva
    $stmt = $mysqli->prepare('SELECT id_horario FROM reserva WHERE id_reserva = ? AND id_usuario = ?');
    $stmt->bind_param('ii', $id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($id_horario);
    if(!$stmt->fetch()){
        $stmt->close();
        json_response(['error'=>'Reserva no encontrada']);
    }
    $stmt->close();

    // eliminar la reserva
    $stmt = $mysqli->prepare('DELETE FROM reserva WHERE id_reserva = ?');
    $stmt->bind_param('i', $id);
    $ok = $stmt->execute();
    $stmt->close();

    // liberar el horario correspondiente
    if($ok && $id_horario){
        $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 1 WHERE id_horario = ?');
        $stmt->bind_param('i',$id_horario);
        $stmt->execute();
        $stmt->close();
    }

    if($ok) json_response(['success'=>true]);
    else json_response(['error'=>'No se pudo cancelar la reserva']);
}

/* =======================================================
   MODIFICAR RESERVA → CAMBIAR DE HORARIO
   =======================================================*/
elseif($action === 'update'){
    $id = intval($_POST['id'] ?? 0);
    $new_horario = intval($_POST['id_horario'] ?? 0);
    if(!$id || !$new_horario) json_response(['error'=>'Datos inválidos']);

    // obtener horario actual de la reserva
    $stmt = $mysqli->prepare('SELECT id_horario,id_curso FROM reserva WHERE id_reserva = ? AND id_usuario = ?');
    $stmt->bind_param('ii',$id,$_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($old_horario,$id_curso);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Reserva no encontrada']); }
    $stmt->close();

    // obtener info del nuevo horario
    $stmt = $mysqli->prepare('SELECT fecha,hora_inicio,hora_fin,disponible FROM horariodisponible WHERE id_horario = ?');
    $stmt->bind_param('i',$new_horario);
    $stmt->execute();
    $stmt->bind_result($fecha,$hora_inicio,$hora_fin,$disponible);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Horario no encontrado']); }
    $stmt->close();
    if(!$disponible) json_response(['error'=>'Horario no disponible']);

    // actualizar la reserva con el nuevo horario
    $stmt = $mysqli->prepare('UPDATE reserva SET id_horario=?,fecha_reserva=?,hora_inicio=?,hora_fin=? WHERE id_reserva=?');
    $stmt->bind_param('isssi',$new_horario,$fecha,$hora_inicio,$hora_fin,$id);
    $stmt->execute(); $stmt->close();

    // liberar horario anterior
    if($old_horario){
        $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 1 WHERE id_horario = ?');
        $stmt->bind_param('i',$old_horario);
        $stmt->execute(); $stmt->close();
    }

    // marcar nuevo horario como ocupado
    $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 0 WHERE id_horario = ?');
    $stmt->bind_param('i',$new_horario);
    $stmt->execute(); $stmt->close();

    json_response(['success'=>true]);
}

/* =======================================================
   ACCIÓN DESCONOCIDA
   =======================================================*/
else {
    json_response(['error'=>'Acción desconocida']);
}
?>
