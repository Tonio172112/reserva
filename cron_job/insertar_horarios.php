<?php
$mysqli = new mysqli("localhost", "root", "", "divino_eter");

if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}

$query = "
INSERT INTO horariodisponible (dia, fecha, hora_inicio, hora_fin, disponible)
SELECT 
    CASE DAYOFWEEK(fecha)
        WHEN 7 THEN 'sábado'
        WHEN 1 THEN 'domingo'
    END AS dia,
    fecha,
    hora_inicio,
    hora_fin,
    1 AS disponible
FROM (
    SELECT 
        DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', n.n)) AS fecha
    FROM (
        SELECT 1 n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION 
               SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION
               SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 UNION
               SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19 UNION SELECT 20 UNION
               SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24 UNION SELECT 25 UNION
               SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29 UNION SELECT 30 UNION SELECT 31
    ) AS n
    WHERE MONTH(DATE(CONCAT(YEAR(CURDATE()), '-', MONTH(CURDATE()), '-', n.n))) = MONTH(CURDATE())
) AS fechas_mes
JOIN (
    SELECT '09:00:00' AS hora_inicio, '12:00:00' AS hora_fin
    UNION ALL
    SELECT '13:00:00', '16:00:00'
) AS turnos
WHERE DAYOFWEEK(fecha) IN (7, 1)
  AND fecha >= CURDATE()
  AND NOT EXISTS (
      SELECT 1 FROM horariodisponible h
      WHERE h.fecha = fechas_mes.fecha
        AND h.hora_inicio = turnos.hora_inicio
        AND h.hora_fin = turnos.hora_fin
  );
";

$mysqli->query($query);

echo "Turnos insertados: " . $mysqli->affected_rows;

$mysqli->close();
?>
