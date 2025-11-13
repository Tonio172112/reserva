<?php
require_once __DIR__ . '/../includes/db.php';

// obtener fecha enviada por GET; si no existe, se devuelve error
$fecha = $_GET['fecha'] ?? null;
if(!$fecha) json_response(['error'=>'Fecha requerida']);

// buscar todos los horarios disponibles para la fecha solicitada
$stmt = $mysqli->prepare('SELECT id_horario,dia,fecha,hora_inicio,hora_fin,disponible FROM HorarioDisponible WHERE fecha = ? ORDER BY hora_inicio');
$stmt->bind_param('s',$fecha);
$stmt->execute();
$res = $stmt->get_result();

$horarios = [];

// recorrer cada horario disponible para la fecha
while($r = $res->fetch_assoc()){

    // verificar si el horario ya tiene una reserva confirmada
    $stmt2 = $mysqli->prepare('SELECT COUNT(*) FROM Reserva WHERE id_horario = ? AND estado = "confirmada"');
    $stmt2->bind_param('i',$r['id_horario']);
    $stmt2->execute();
    $stmt2->bind_result($count);
    $stmt2->fetch();
    $stmt2->close();

    // si no está reservado y el flag disponible=1, se agrega a la lista final
    if($count == 0 && $r['disponible']) $horarios[] = $r;
}

$stmt->close();

// devolver horarios disponibles en formato JSON
json_response(['horarios'=>$horarios]);
