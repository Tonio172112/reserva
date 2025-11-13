<?php
session_start();
require_once './includes/db.php';
// // Verifica si el usuario está autenticado
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reservar horario</title>

  <?php include './includes/icon.php'; ?>
  <!-- // Icono del sitio -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- // Bootstrap -->

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
  <!-- // FullCalendar estilos -->

  <style>
    body, html { height: 100%; }
    /* // El calendario se muestra centrado con un estilo de tarjeta */
    #calendar {
      background: #fff;
      padding: 12px;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.06);
      max-width: 1000px;
      margin: 0 auto 20px;
    }
    /* // Resalta fines de semana */
    .fc-daygrid-day.fc-weekend { background: #f8f9fa; } 
  </style>
</head>

<body class="bg-light">
  <?php include './includes/navbar.php'; ?>
  <!-- // Barra de navegación -->

  <main class="container-fluid py-4">
    <h2 class="mb-4 text-center">Reservar horario para tu curso</h2>

    <div class="card mb-4 mx-auto" style="max-width:700px;">
      <div class="card-body">
        <h5 class="card-title">Seleccioná tu curso</h5>

        <!-- // Dropdown dinámico cargado desde la API -->
        <select id="selectCurso" class="form-select mb-3">
          <option value="">Cargando cursos...</option>
        </select>

        <div id="cursoMsg" class="text-danger"></div>
        <!-- // Mensajes relacionados a los cursos -->
      </div>
    </div>

    <div id="calendar"></div>
    <!-- // Aquí se renderiza el calendario -->

    <div id="horarios" class="mt-4 container"></div>
    <!-- // Aquí se listan los horarios disponibles tras hacer clic en un día -->
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <!-- // Scripts de FullCalendar -->

  <script>
  document.addEventListener('DOMContentLoaded', async function() {

    const selectCurso = document.getElementById('selectCurso');
    const horariosDiv = document.getElementById('horarios');
    const cursoMsg = document.getElementById('cursoMsg');

    // --------------------------------------------------------------
    // Carga los cursos del usuario desde api/courses.php
    // --------------------------------------------------------------
    async function cargarCursos(){
      try {
        const res = await fetch('api/courses.php?action=list');
        const json = await res.json();

        selectCurso.innerHTML = ''; // Limpia el select

        // Si no hay cursos, se muestra mensaje y deshabilita select
        if(!json.success || !Array.isArray(json.data) || json.data.length === 0){
          selectCurso.innerHTML = '<option value="">No tenés cursos registrados</option>';
          cursoMsg.textContent = 'No tenés cursos registrados. Creá uno desde la sección Cursos.';
          selectCurso.disabled = true;
          return false;
        }

        // Llena el select con los cursos disponibles
        selectCurso.disabled = false;
        selectCurso.innerHTML = '<option value="">Seleccioná un curso</option>';
        json.data.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.id_curso;
          opt.textContent = c.titulo;
          selectCurso.appendChild(opt);
        });

        cursoMsg.textContent = '';
        return true;
      } catch(e){
        selectCurso.innerHTML = '<option value="">Error cargando cursos</option>';
        cursoMsg.textContent = 'Error al cargar cursos.';
        selectCurso.disabled = true;
        return false;
      }
    }

    // --------------------------------------------------------------
    // Obtiene días con horarios disponibles desde la API
    // --------------------------------------------------------------
    async function fetchAvailableDays(){
      try {
        const res = await fetch('api/reserve.php?action=list_days');
        if(!res.ok) return [];
        const json = await res.json();
        if(json.success && Array.isArray(json.days)) return json.days;
        return [];
      } catch(e){
        return [];
      }
    }

    // --------------------------------------------------------------
    // Obtiene horarios disponibles para una fecha (GET)
    // --------------------------------------------------------------
    async function fetchTimesByDate(fecha){
      try {
        const url = 'api/reserve.php?action=list_times&fecha=' + encodeURIComponent(fecha);
        const res = await fetch(url);
        if(!res.ok) return { ok:false, error: 'Respuesta no OK' };

        const json = await res.json();

        // Respuesta estándar: json.times
        if(json.success && Array.isArray(json.times))
          return { ok:true, times: json.times };

        // Algunos endpoints devuelven un array directamente
        if(Array.isArray(json))
          return { ok:true, times: json };

        return { ok:false, error: json.error ?? 'Sin datos' };

      } catch(e){
        return { ok:false, error: 'Error en la petición' };
      }
    }

    const calendarEl = document.getElementById('calendar');

    // --------------------------------------------------------------
    // Inicializa FullCalendar con carga dinámica de fechas
    // --------------------------------------------------------------
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      locale: 'es',
      selectable: true,
      height: 'auto',

      // Carga visual de días (fines de semana y disponibles)
      events: async function(info, successCallback, failureCallback){
        try {
          const availableDays = await fetchAvailableDays();
          const events = [];

          const start = new Date(info.start);
          const end = new Date(info.end);

          // Recorre cada día visible en el calendario
          for(let d = new Date(start); d < end; d.setDate(d.getDate()+1)){
            const dow = d.getDay(); // 0=domingo, 6=sábado

            // Solo colorea fines de semana
            if(dow === 0 || dow === 6){
              const yyyy = d.getFullYear();
              const mm = String(d.getMonth()+1).padStart(2,'0');
              const dd = String(d.getDate()).padStart(2,'0');
              const datestr = `${yyyy}-${mm}-${dd}`;

              // Color base del fin de semana
              events.push({ start: datestr, display: 'background', color: '#f0f4f8' });

              // Color azul si hay horarios disponibles
              if(availableDays.indexOf(datestr) !== -1){
                events.push({ start: datestr, display: 'background', color: '#0d6efd' });
              }
            }
          }

          successCallback(events);
        } catch(err){
          failureCallback(err);
        }
      },

      // --------------------------------------------------------------
      // Maneja el clic en un día del calendario
      // --------------------------------------------------------------
      dateClick: async function(info) {
        const fecha = info.dateStr;
        const dow = info.date.getDay();

        // Evita días que no sean sábado o domingo
        if(dow !== 0 && dow !== 6){
          horariosDiv.innerHTML = '<div class="alert alert-info">Seleccioná un sábado o domingo.</div>';
          return;
        }

        // Requiere seleccionar un curso antes
        if (!selectCurso.value) {
          horariosDiv.innerHTML = '<div class="alert alert-warning">Primero seleccioná un curso.</div>';
          return;
        }

        horariosDiv.innerHTML = '<div class="text-center text-muted my-3">Cargando horarios...</div>';

        const result = await fetchTimesByDate(fecha);

        // Error en respuesta
        if(!result.ok){
          horariosDiv.innerHTML = '<div class="alert alert-danger">Error al cargar horarios. '
                                  + (result.error || '') + '</div>';
          return;
        }

        const times = result.times;

        if(!times || times.length === 0){
          horariosDiv.innerHTML = '<div class="alert alert-info">No hay horarios disponibles para esta fecha.</div>';
          return;
        }

        // Construcción del listado de horarios
        let html = `<h5 class="mb-3">Horarios disponibles para ${fecha}</h5>`;
        html += '<div class="list-group">';

        times.forEach(t => {
          const id = t.id_horario ?? t.id ?? null;
          const hi = t.hora_inicio ?? t.horaInicio ?? '';
          const hf = t.hora_fin ?? t.horaFin ?? '';

          html += `
            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center reservar-btn"
                    data-id="${id}">
              <span>${hi} - ${hf}</span>
              <span class="badge bg-success">Reservar</span>
            </button>`;
        });

        html += '</div>';
        horariosDiv.innerHTML = html;

        // Maneja clic en cada horario disponible
        document.querySelectorAll('.reservar-btn').forEach(btn=>{
          btn.addEventListener('click', async ()=> {
            const id_horario = btn.dataset.id;
            if(!id_horario){
              alert('ID de horario inválido.');
              return;
            }

            if(!confirm('¿Deseás reservar este horario?')) return;

            const fd = new FormData();
            fd.append('action','create');
            fd.append('id_horario', id_horario);
            fd.append('id_curso', selectCurso.value);

            try {
              const r = await fetch('api/reserve.php', { method: 'POST', body: fd });
              const resp = await r.json();

              // Respuesta exitosa
              if(resp.success){
                horariosDiv.innerHTML = '<div class="alert alert-success">'
                                        +(resp.message||'Reserva confirmada')+'</div>';
                calendar.refetchEvents();
              } 
              // Caso lista de espera
              else if (resp.waitlist){
                horariosDiv.innerHTML = '<div class="alert alert-warning">'
                                        +(resp.message||'Agregado a lista de espera')+'</div>';
              } 
              // Error
              else {
                horariosDiv.innerHTML = '<div class="alert alert-danger">'
                                        +(resp.error||'Error al reservar')+'</div>';
              }

            } catch(e){
              horariosDiv.innerHTML = '<div class="alert alert-danger">Error en la reserva.</div>';
            }
          });
        });
      }
    });

    // Renderiza el calendario
    calendar.render();

    // Carga cursos al iniciar
    const ok = await cargarCursos();

    // Si cambia el curso seleccionado, recarga eventos del calendario
    selectCurso.addEventListener('change', ()=> calendar.refetchEvents());
  });
  </script>

</body>
</html>
