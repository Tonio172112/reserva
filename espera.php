<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mi lista de espera</title>
  <?php include './includes/icon.php' ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include 'includes/navbar.php' ?>
  <main class="container py-5">
    <div class="card p-3">
      <h4>Mi lista de espera</h4>
      <div id="waitlistContainer" class="mt-3"></div>
    </div>
  </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<script>
async function loadWaitlist(){
  const res = await fetch('../api/waitlist.php');
  const data = await res.json();
  const c = document.getElementById('waitlistContainer');
  if(data.error){ c.innerHTML = '<div class="alert alert-danger">'+data.error+'</div>'; return; }
  if(!data.waitlist || data.waitlist.length===0){ c.innerHTML = '<div class="alert alert-info">No estás en ninguna lista de espera.</div>'; return; }
  let html = '<ul class="list-group">';
  data.waitlist.forEach(w=>{
    html += `<li class="list-group-item d-flex justify-content-between align-items-center">${w.descripcion ?? 'Curso (sin descripcion)'} — ${w.fecha_solicitada ?? ''} <span class="badge bg-secondary">${w.id_lista}</span></li>`;
  });
  html += '</ul>';
  c.innerHTML = html;
}
loadWaitlist();
</script>
</body>
</html>
