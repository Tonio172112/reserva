<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrarse</title>
  <?php include './includes/icon.php' ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <?php include 'includes/navbar.php'; ?>
  <main class="container py-5">
    <div class="card mx-auto" style="max-width:540px">
      <div class="card-body">
        <h3 class="card-title">Crear cuenta</h3>
        <form id="registerForm" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input required type="email" name="email" class="form-control">
          </div>
          <div class="mb-3 row">
            <div class="col">
              <label class="form-label">Nombre</label>
              <input required name="nombre" class="form-control">
            </div>
            <div class="col">
              <label class="form-label">Apellido</label>
              <input required name="apellido" class="form-control">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input required type="tel" name="telefono" class="form-control" placeholder="Ej: 3411234567">
          </div>
          <div class="mb-3">
            <label class="form-label">DNI</label>
            <input required name="dni" class="form-control" placeholder="DNI">
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña (mín 8)</label>
            <input required type="password" name="password" minlength="8" class="form-control">
          </div>
          <div id="registerMsg" class="mb-3"></div>
          <button class="btn btn-primary" type="submit">Registrarme</button>
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
  <script>
  document.getElementById('registerForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = new FormData(this);
    const res = await fetch('../api/register.php',{method:'POST',body:form});
    const data = await res.json();
    const msg = document.getElementById('registerMsg');
    if(data.success){ msg.innerHTML = '<div class="alert alert-success">'+data.message+'</div>'; this.reset(); }
    else { msg.innerHTML = '<div class="alert alert-danger">'+(data.error||'Error')+'</div>'; }
  });
  </script>
</body>
</html>
