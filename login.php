
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar Sesión</title>
  <?php include './includes/icon.php' ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body style="background-color:#f5f5f5; color:#333;">

  <?php include 'includes/navbar.php'; ?>
  <main class="container py-5">
    <div class="card mx-auto" style="max-width:420px">
      <div class="card-body">
        <h3 class="card-title">Iniciar sesión</h3>
        <form id="loginForm" novalidate>
          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" required class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" required class="form-control">
          </div>
          <div id="loginMsg"></div>
          <button class="btn btn-primary" type="submit">Entrar</button>
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/app.js"></script>
  <script>
  document.getElementById('loginForm').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = new FormData(this);
    const res = await fetch('../api/login.php',{method:'POST',body:form});
    const data = await res.json();
    const msg = document.getElementById('loginMsg');
    if(data.success){ msg.innerHTML = '<div class="alert alert-success">Login correcto. Redirigiendo…</div>'; setTimeout(()=>window.location.href='../reservar.php',600); }
    else { msg.innerHTML = '<div class="alert alert-danger">'+(data.error||'Error')+'</div>'; }
  });
  </script>
</body>
</html>
