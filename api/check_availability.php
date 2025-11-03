<?php
require_once __DIR__ . '/../includes/db.php';
$fecha = $_GET['fecha'] ?? null;
if(!$fecha) json_response(['error'=>'Fecha requerida']);

$stmt = $mysqli->prepare('SELECT id_horario,dia,fecha,hora_inicio,hora_fin,disponible FROM HorarioDisponible WHERE fecha = ? ORDER BY hora_inicio');
$stmt->bind_param('s',$fecha);
$stmt->execute();
$res = $stmt->get_result();
$horarios = [];
while($r = $res->fetch_assoc()){
    $stmt2 = $mysqli->prepare('SELECT COUNT(*) FROM Reserva WHERE id_horario = ? AND estado = "confirmada"');
    $stmt2->bind_param('i',$r['id_horario']);
    $stmt2->execute();
    $stmt2->bind_result($count); $stmt2->fetch(); $stmt2->close();
    if($count == 0 && $r['disponible']) $horarios[] = $r;
}
$stmt->close();
json_response(['horarios'=>$horarios]);
