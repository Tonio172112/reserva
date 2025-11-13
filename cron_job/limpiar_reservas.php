<?php
$mysqli = new mysqli("localhost", "root", "", "divino_eter");

if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$query = "DELETE FROM horariodisponible WHERE fecha < CURDATE();";

$mysqli->query($query);

echo "Horarios antiguos eliminados. También se eliminaron reservas asociadas.";
$mysqli->close();
?>