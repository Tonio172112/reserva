<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión']);

$action = $_REQUEST['action'] ?? 'create';


$stmt = $mysqli->prepare('SELECT id_cliente FROM usuarios WHERE id_usuario = ?');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($id_cliente);
$stmt->fetch();
$stmt->close();


if($action === 'create'){
    $id_horario = intval($_POST['id_horario'] ?? 0);
    $id_curso = intval($_POST['id_curso'] ?? 0);
    if(!$id_horario || !$id_curso) json_response(['error'=>'Datos incompletos']);

    if($id_cliente){
        $stmt = $mysqli->prepare('SELECT id_curso FROM cursos WHERE id_curso = ? AND id_cliente = ?');
        $stmt->bind_param('ii',$id_curso,$id_cliente);
        $stmt->execute(); $stmt->store_result();
        if($stmt->num_rows === 0){ $stmt->close(); json_response(['error'=>'Curso inválido']); }
        $stmt->close();
    }

    $stmt = $mysqli->prepare('SELECT fecha,hora_inicio,hora_fin,disponible FROM horariodisponible WHERE id_horario = ?');
    $stmt->bind_param('i',$id_horario);
    $stmt->execute();
    $stmt->bind_result($fecha,$hora_inicio,$hora_fin,$disponible);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Horario no encontrado']); }
    $stmt->close();
    if(!$disponible) json_response(['error'=>'Horario no disponible']);

    $stmt = $mysqli->prepare('SELECT id_reserva FROM reserva WHERE id_horario = ? AND id_curso = ?');
    $stmt->bind_param('ii',$id_horario,$id_curso);
    $stmt->execute(); $stmt->store_result();
    if($stmt->num_rows > 0){
        $stmt->close();
        json_response(['error'=>'Ya existe una reserva para ese horario y curso']);
    }

    $stmt = $mysqli->prepare('INSERT INTO reserva (id_horario,id_curso,id_usuario,fecha_reserva,hora_inicio,hora_fin,estado,creado_en) VALUES(?,?,?,?,?,?,"confirmada",NOW())');
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param('iiisss',$id_horario,$id_curso,$user_id,$fecha,$hora_inicio,$hora_fin);
    $ok = $stmt->execute();
    $stmt->close();
    if(!$ok) json_response(['error'=>'Error al crear reserva']);

    $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 0 WHERE id_horario = ?');
    $stmt->bind_param('i',$id_horario);
    $stmt->execute(); $stmt->close();

    json_response(['success'=>true,'message'=>'Reserva confirmada para el curso.']);

} elseif($action === 'list_days'){
    $days = [];
    $res = $mysqli->query("SELECT DISTINCT fecha FROM horariodisponible WHERE disponible = 1 AND (dia='sábado' OR dia='domingo') ORDER BY fecha");
    if($res){
        while($row = $res->fetch_assoc()) $days[] = $row['fecha'];
    }
    json_response(['success'=>true,'days'=>$days]);


} elseif($action === 'list_times'){
    $fecha = $_REQUEST['fecha'] ?? '';
    if(!$fecha) json_response(['success'=>true,'times'=>[]]);
    $times = [];
    $stmt = $mysqli->prepare("SELECT id_horario,hora_inicio,hora_fin FROM horariodisponible WHERE fecha = ? AND disponible = 1 ORDER BY hora_inicio");
    $stmt->bind_param('s',$fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    while($row = $res->fetch_assoc()) $times[] = $row;
    $stmt->close();
    json_response(['success'=>true,'times'=>$times]);


} elseif($action === 'list'){
    $stmt = $mysqli->prepare('SELECT r.id_reserva,r.id_horario,r.id_curso,r.fecha_reserva,r.hora_inicio,r.hora_fin,r.estado FROM reserva r WHERE r.id_usuario = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    json_response(['success'=>true,'data'=>$rows]);

} elseif($action === 'delete'){
    $id = intval($_POST['id'] ?? 0);
    if(!$id) json_response(['error'=>'ID inválido']);
    $stmt = $mysqli->prepare('SELECT id_horario FROM reserva WHERE id_reserva = ? AND id_usuario = ?');
    $stmt->bind_param('ii',$id,$_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($id_horario);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Reserva no encontrada']); }
    $stmt->close();
    $stmt = $mysqli->prepare('DELETE FROM reserva WHERE id_reserva = ?');
    $stmt->bind_param('i',$id);
    $stmt->execute(); $stmt->close();
    if($id_horario){
        $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 1 WHERE id_horario = ?');
        $stmt->bind_param('i',$id_horario);
        $stmt->execute(); $stmt->close();
    }
    json_response(['success'=>true]);


} elseif($action === 'update'){
    $id = intval($_POST['id'] ?? 0);
    $new_horario = intval($_POST['id_horario'] ?? 0);
    if(!$id || !$new_horario) json_response(['error'=>'Datos inválidos']);
    $stmt = $mysqli->prepare('SELECT id_horario,id_curso FROM reserva WHERE id_reserva = ? AND id_usuario = ?');
    $stmt->bind_param('ii',$id,$_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($old_horario,$id_curso);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Reserva no encontrada']); }
    $stmt->close();

    $stmt = $mysqli->prepare('SELECT fecha,hora_inicio,hora_fin,disponible FROM horariodisponible WHERE id_horario = ?');
    $stmt->bind_param('i',$new_horario);
    $stmt->execute();
    $stmt->bind_result($fecha,$hora_inicio,$hora_fin,$disponible);
    if(!$stmt->fetch()){ $stmt->close(); json_response(['error'=>'Horario no encontrado']); }
    $stmt->close();
    if(!$disponible) json_response(['error'=>'Horario no disponible']);

    $stmt = $mysqli->prepare('UPDATE reserva SET id_horario=?,fecha_reserva=?,hora_inicio=?,hora_fin=? WHERE id_reserva=?');
    $stmt->bind_param('isssi',$new_horario,$fecha,$hora_inicio,$hora_fin,$id);
    $stmt->execute(); $stmt->close();

    if($old_horario){
        $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 1 WHERE id_horario = ?');
        $stmt->bind_param('i',$old_horario);
        $stmt->execute(); $stmt->close();
    }
    $stmt = $mysqli->prepare('UPDATE horariodisponible SET disponible = 0 WHERE id_horario = ?');
    $stmt->bind_param('i',$new_horario);
    $stmt->execute(); $stmt->close();

    json_response(['success'=>true]);

} else {
    json_response(['error'=>'Acción desconocida']);
}
?>
