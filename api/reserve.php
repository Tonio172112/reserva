<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(['error'=>'Método no permitido']);
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión para reservar']);

$id_horario = intval($_POST['id_horario'] ?? 0);
$id_curso = intval($_POST['id_curso'] ?? 0);
if(!$id_horario || !$id_curso) json_response(['error'=>'Datos incompletos']);

$stmt = $mysqli->prepare('SELECT fecha,hora_inicio,hora_fin,disponible FROM HorarioDisponible WHERE id_horario = ?');
$stmt->bind_param('i',$id_horario); $stmt->execute(); $stmt->bind_result($fecha,$hora_inicio,$hora_fin,$disponible);
if(!$stmt->fetch()){ json_response(['error'=>'Horario no encontrado']); }
$stmt->close();
if(!$disponible) json_response(['error'=>'Horario no disponible']);

$stmt = $mysqli->prepare('SELECT COUNT(*) FROM Reserva WHERE fecha_reserva = ? AND estado = "confirmada" AND (hora_inicio < ? AND hora_fin > ?)');
$stmt->bind_param('sss', $fecha, $hora_fin, $hora_inicio);
$stmt->execute();
$stmt->bind_result($count); $stmt->fetch(); $stmt->close();

$user_id = $_SESSION['user_id'];

if($count > 0){
    $stmt = $mysqli->prepare('INSERT INTO lista_espera (id_cliente, id_curso, fecha_solicitada) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $user_id, $id_curso, $fecha);
    $ok = $stmt->execute(); $stmt->close();
    if($ok) json_response(['waitlist'=>true,'message'=>'Ya existe un curso en ese horario. Has sido agregado a la lista de espera.']);
    else json_response(['error'=>'No se pudo agregar a la lista de espera']);
}

$stmt = $mysqli->prepare('INSERT INTO Reserva (id_horario,id_curso,id_usuario,fecha_reserva,hora_inicio,hora_fin) VALUES(?,?,?,?,?,?)');
$stmt->bind_param('iiisss',$id_horario,$id_curso,$user_id,$fecha,$hora_inicio,$hora_fin);
$ok = $stmt->execute();
if(!$ok) json_response(['error'=>'Error al crear reserva']);
$stmt->close();
$stmt = $mysqli->prepare('UPDATE HorarioDisponible SET disponible = 0 WHERE id_horario = ?');
$stmt->bind_param('i',$id_horario); $stmt->execute(); $stmt->close();

json_response(['success'=>true,'message'=>'Reserva confirmada']);
