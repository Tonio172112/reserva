<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
if(empty($_SESSION['user_id'])) json_response(['error'=>'Debes iniciar sesión']);
$uid = $_SESSION['user_id'];

$stmt = $mysqli->prepare('SELECT l.id_lista, l.fecha_solicitada, c.descripcion FROM lista_espera l LEFT JOIN cursos c ON l.id_curso = c.id_curso WHERE l.id_cliente = ? ORDER BY l.creado_en DESC');
$stmt->bind_param('i',$uid); $stmt->execute(); $res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
$stmt->close();
json_response(['waitlist'=>$out]);
