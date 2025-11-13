<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Iniciar Sesión</title>
  <?php include './includes/icon.php' ?>
  <!-- // Icono del sitio -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- // Bootstrap para estilos -->

  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- // Estilos propios -->
</head>

<body style="background-color:#f5f5f5; color:#333;">

  <?php include 'includes/navbar.php'; ?>
  <!-- // Barra de navegación -->

  <main class="container py-5">
    <div class="card mx-auto" style="max-width:420px">
      <!-- // Contenedor centrado para el formulario -->

      <div class="card-body">
        <h3 class="card-title">Iniciar sesión</h3>

        <form id="loginForm" novalidate>
          <!-- // Formulario sin validación automática del navegador -->

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" required class="form-control">
            <!-- // Campo de email con validación requerida -->
          </div>

          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" required class="form-control">
            <!-- // Campo de contraseña -->
          </div>

          <div id="loginMsg"></div>
          <!-- // Contenedor donde se muestran mensajes de error o éxito -->

          <button class="btn btn-primary" type="submit">Entrar</button>
          <!-- // Botón principal del formulario -->
        </form>

      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- // Scripts de Bootstrap -->

  <script src="assets/js/app.js"></script>
  <!-- // JS global del proyecto -->

  <script>
  // // Manejo del formulario de login
  document.getElementById('loginForm').addEventListener('submit', async function(e){
    e.preventDefault(); 
    // // Evita que el formulario recargue la página

    const form = new FormData(this);
    // // Recolecta los datos del formulario

    const res = await fetch('../api/login.php',{method:'POST',body:form});
    // // Envía la solicitud al backend para validar login
    
    const data = await res.json();
    const msg = document.getElementById('loginMsg');

    // // Si el login es correcto, muestra mensaje y redirige
    if(data.success){
      msg.innerHTML = '<div class="alert alert-success">Login correcto. Redirigiendo…</div>';
      setTimeout(()=>window.location.href='../reservar.php',600);
    }
    else {
      // // Muestra el error devuelto por el backend
      msg.innerHTML = '<div class="alert alert-danger">'+(data.error||'Error')+'</div>';
    }
  });
  </script>
</body>
</html>
