<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cursos</title>
  <?php include './includes/icon.php' ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>
  <main class="container py-5">
    <div class="card mx-auto" style="max-width:900px">
      <div class="card-body">
        <h3 class="card-title">Mis Cursos</h3>
        <div id="coursesMsg"></div>
        <div class="mb-3 text-end">
          <button id="newCourseBtn" class="btn btn-primary">Nuevo Curso</button>
        </div>
        <div id="coursesList"></div>
      </div>
    </div>
  </main>

  <!-- modal -->
  <div class="modal" tabindex="-1" id="courseModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="courseForm">
        <div class="modal-header"><h5 class="modal-title">Curso</h5></div>
        <div class="modal-body">
          <input type="hidden" name="id" id="courseId">
          <div class="mb-2"><label>Título</label><input name="titulo" id="titulo" class="form-control" required></div>
          <div class="mb-2"><label>Descripción</label><textarea name="descripcion" id="descripcion" class="form-control"></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" id="closeModal">Cerrar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
        </form>
      </div>
    </div>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
  const coursesList = document.getElementById('coursesList');
  const courseModal = document.getElementById('courseModal');
  const courseForm = document.getElementById('courseForm');
  const coursesMsg = document.getElementById('coursesMsg');

  function showMsg(html){ coursesMsg.innerHTML = html; setTimeout(()=>coursesMsg.innerHTML='',4000); }

  async function loadCourses(){
    const res = await fetch('api/courses.php?action=list');
    const data = await res.json();
    if(!data.success) { coursesList.innerHTML = '<div class="alert alert-info">No tenés cursos actualmente. Creá uno.</div>'; return; }
    if(data.data.length === 0){ coursesList.innerHTML = '<div class="alert alert-info">No tenés cursos actualmente. Creá uno.</div>'; return; }
    let html = '<div class="list-group">';
    data.data.forEach(c=>{
      html += `<div class="list-group-item d-flex justify-content-between align-items-start">
        <div><strong>${c.titulo}</strong><div class="small text-muted">${c.descripcion||''}</div></div>
        <div>
          <button class="btn btn-sm btn-outline-secondary me-1" onclick="editCourse(${c.id_curso},'${encodeURIComponent(c.titulo)}','${encodeURIComponent(c.descripcion||'')}')">Editar</button>
          <button class="btn btn-sm btn-outline-danger" onclick="deleteCourse(${c.id_curso})">Eliminar</button>
        </div>
      </div>`;
    });
    html += '</div>';
    coursesList.innerHTML = html;
  }

  function encodeDecode(s){ try{ return decodeURIComponent(s); }catch(e){ return s; } }

  function editCourse(id,titulo,desc){
    document.getElementById('courseId').value = id;
    document.getElementById('titulo').value = encodeDecode(titulo);
    document.getElementById('descripcion').value = encodeDecode(desc);
    courseModal.style.display = 'block';
  }
  document.getElementById('newCourseBtn').addEventListener('click', ()=>{
    document.getElementById('courseId').value = '';
    document.getElementById('titulo').value = '';
    document.getElementById('descripcion').value = '';
    courseModal.style.display = 'block';
  });
  document.getElementById('closeModal').addEventListener('click', ()=> courseModal.style.display = 'none');

  courseForm.addEventListener('submit', async function(e){
    e.preventDefault();
    const form = new FormData(this);
    const id = form.get('id');
    const action = id ? 'update' : 'create';
    const res = await fetch('api/courses.php?action='+action,{method:'POST',body:form});
    const data = await res.json();
    if(data.success){ courseModal.style.display = 'none'; loadCourses(); showMsg('<div class="alert alert-success">Guardado</div>'); }
    else showMsg('<div class="alert alert-danger">'+(data.error||'Error')+'</div>');
  });

  async function deleteCourse(id){
    const confirmar = confirm("¿Está seguro de que desea eliminar el curso?\nTodas las reservas serán canceladas y los horarios liberados.");
    if(!confirmar) return;

    const form = new FormData();
    form.append('id', id);

    const res = await fetch('api/courses.php?action=delete', {
        method: 'POST',
        body: form
    });

    const data = await res.json();

    if(data.success){
        loadCourses();
        showMsg('<div class="alert alert-success">Curso eliminado correctamente. Todas las reservas fueron canceladas.</div>');
    }
    else {
        showMsg('<div class="alert alert-danger">'+(data.error||"Error al eliminar el curso")+'</div>');
    }
}

  loadCourses();
  </script>
</body>
</html>
