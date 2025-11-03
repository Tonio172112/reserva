
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservar</title>
  <?php include './includes/icon.php' ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>
  <main class="container py-5">
    <div class="row g-4">
      <div class="col-12 col-md-6">
        <div class="card p-3">
          <h5>Buscar horarios</h5>
          <div class="mb-3">
            <label>Fecha</label>
            <input id="fecha" class="form-control" type="date">
          </div>
          <div class="mb-3">
            <button id="buscar" class="btn btn-primary">Buscar</button>
          </div>
          <div id="alerts"></div>
        </div>

        <div id="horariosList" class="mt-3"></div>
      </div>

      <div class="col-12 col-md-6">
        <div class="card p-3">
          <h5>Reservar</h5>
          <form id="reserveForm">
            <input type="hidden" name="id_horario">
            <div class="mb-3">
              <label>ID Curso</label>
              <input name="id_curso" class="form-control" required>
              <div class="form-text">Ingresa el id del curso (creado en la tabla cursos).</div>
            </div>
            <div id="reserveMsg"></div>
            <button class="btn btn-success" type="submit">Confirmar Reserva</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
  <script>
  document.getElementById('buscar').addEventListener('click', async ()=>{
    const fecha = document.getElementById('fecha').value;
    const list = document.getElementById('horariosList');
    list.innerHTML = '';
    if(!fecha) return alert('Seleccioná una fecha');
    const res = await fetch(`../api/check_availability.php?fecha=${encodeURIComponent(fecha)}`);
    const data = await res.json();
    if(data.error){ list.innerHTML = `<div class="alert alert-danger">${data.error}</div>`; return; }
    if(!data.horarios || data.horarios.length === 0){ list.innerHTML = '<div class="alert alert-warning">No hay horarios disponibles</div>'; return; }
    const ul = document.createElement('div');
    data.horarios.forEach(h=>{
      const card = document.createElement('div');
      card.className = 'card mb-2 p-2';
      card.innerHTML = `<div><strong>${h.dia} ${h.fecha}</strong></div>
                        <div>${h.hora_inicio.slice(0,5)} - ${h.hora_fin.slice(0,5)}</div>
                        <div class="mt-2"><button class="btn btn-outline-primary btn-sm select-btn" data-id="${h.id_horario}" data-start="${h.hora_inicio}" data-end="${h.hora_fin}">Seleccionar</button></div>`;
      ul.appendChild(card);
    });
    list.appendChild(ul);

    document.querySelectorAll('.select-btn').forEach(b=>{
      b.addEventListener('click', (e)=>{
        const id = e.currentTarget.dataset.id;
        document.querySelector('input[name=id_horario]').value = id;
        document.getElementById('reserveMsg').innerHTML = `<div class="alert alert-info">Seleccionado horario ID ${id}</div>`;
      });
    });
  });

  document.getElementById('reserveForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = new FormData(this);
    const res = await fetch('../api/reserve.php',{method:'POST',body:form});
    const data = await res.json();
    const msg = document.getElementById('reserveMsg');
    if(data.success){ msg.innerHTML = `<div class="alert alert-success">${data.message}</div>`; }
    else if(data.waitlist){ msg.innerHTML = `<div class="alert alert-warning">${data.message}</div>`; }
    else { msg.innerHTML = `<div class="alert alert-danger">${data.error||'Error'}</div>`; }
  });
  </script>
</body>
</html>
