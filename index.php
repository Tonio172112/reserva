<?php
session_start();
require_once './includes/db.php';
// // Inicia sesión y carga la conexión a la base de datos
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Inicio - Sistema de Reservas</title>
  <?php include './includes/icon.php'; ?>
  <!-- // Icono del sitio -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- // Bootstrap para estilos -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- // Estilos personalizados -->
</head>
<body class="bg-light">
  <?php include './includes/navbar.php'; ?>
  <!-- // Navbar común a todas las páginas -->

  <main class="container py-5">

  <?php if (empty($_SESSION['user_id'])): ?>
    <!-- // Vista para usuarios NO logueados -->

    <div class="text-center">
      <h1 class="display-5 mb-3">Bienvenido a Divino Éter</h1>
      <p class="lead mb-4">Iniciá sesión para ver tus reservas o crear nuevos cursos.</p>

      <!-- // Botones para iniciar sesión o registrarse -->
      <a href="login.php" class="btn btn-primary btn-lg me-2">Iniciar sesión</a>
      <a href="register.php" class="btn btn-outline-secondary btn-lg">Registrarse</a>
    </div>

  <?php else: ?>
    <!-- // Vista para usuarios logueados -->

    <?php
    // // Consulta SQL que obtiene todas las reservas del usuario ordenadas por fecha y horario
    $stmt = $mysqli->prepare("
      SELECT r.id_reserva, c.titulo AS curso, r.fecha_reserva, r.hora_inicio, r.hora_fin
      FROM reserva r
      JOIN cursos c ON r.id_curso = c.id_curso
      WHERE r.id_usuario = ?
      ORDER BY r.fecha_reserva, r.hora_inicio
    ");
    
    $stmt->bind_param('i', $_SESSION['user_id']);
    // // Se filtra por el usuario logueado

    $stmt->execute();
    $result = $stmt->get_result();
    $reservas = $result->fetch_all(MYSQLI_ASSOC);
    // // Se obtienen todas las reservas en un arreglo

    $stmt->close();
    ?>

    <h2 class="mb-4 text-center">Mis Reservas</h2>

    <?php if (empty($reservas)): ?>
      <!-- // Mensaje si no hay reservas -->
      <div class="alert alert-info text-center">No tenés reservas actualmente.</div>

    <?php else: ?>
      <!-- // Tabla con reservas del usuario -->

      <div class="card shadow-sm">
        <div class="card-body">

          <!-- // Filtro por fecha -->
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="filterFecha" class="form-label">Filtrar por fecha:</label>
              <input type="date" id="filterFecha" class="form-control">
            </div>
          </div>

          <!-- // Tabla de reservas -->
          <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white" id="tablaReservas">
              <thead class="table-secondary">
                <tr>
                  <th>Curso</th>
                  <th>Fecha</th>
                  <th>Horario</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($reservas as $r): ?>
                <tr>
                  <td><?= htmlspecialchars($r['curso']) ?></td>
                  <td><?= htmlspecialchars($r['fecha_reserva']) ?></td>
                  <td>
                    <?= htmlspecialchars(substr($r['hora_inicio'],0,5)) ?>
                     -
                    <?= htmlspecialchars(substr($r['hora_fin'],0,5)) ?>
                  </td>
                  <td>
                    <!-- // Botón para cancelar reserva -->
                    <button class="btn btn-danger btn-sm cancelar" data-id="<?= $r['id_reserva'] ?>">
                      Cancelar
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        </div>
      </div>

    <?php endif; ?>

    <div id="msg" class="mt-3"></div>
    <!-- // Contenedor para mensajes de cancelación -->

  <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
  // // Código JS que maneja el filtrado y cancelación de reservas
  document.addEventListener('DOMContentLoaded', () => {

    const filterInput = document.getElementById('filterFecha');
    const table = document.getElementById('tablaReservas');
    const rows = table ? table.querySelectorAll('tbody tr') : [];

    // // Filtra filas por fecha seleccionada
    if (filterInput) {
      filterInput.addEventListener('input', () => {
        const filter = filterInput.value;
        rows.forEach(row => {
          const fecha = row.children[1].textContent.trim();
          row.style.display = (!filter || fecha === filter) ? '' : 'none';
        });
      });
    }

    // // Manejo del botón "Cancelar" en cada reserva
    document.querySelectorAll('.cancelar').forEach(btn => {
      btn.addEventListener('click', async () => {

        if(!confirm('¿Deseás cancelar esta reserva?')) return;

        const fd = new FormData();
        fd.append('action','delete');
        fd.append('id', btn.dataset.id);

        // // Se envía petición para eliminar la reserva en reserve.php
        const res = await fetch('api/reserve.php', { 
          method: 'POST', 
          body: fd 
        });

        const data = await res.json();
        const msg = document.getElementById('msg');

        // // Muestra mensaje según éxito o error
        if(data.success){
          msg.innerHTML = '<div class="alert alert-success">Reserva cancelada correctamente.</div>';
          setTimeout(()=>location.reload(),800);
        } else {
          msg.innerHTML = '<div class="alert alert-danger">'+(data.error || 'Error al cancelar')+'</div>';
        }

      });
    });

  });
  </script>

</body>
</html>
