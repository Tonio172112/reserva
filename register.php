<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Registrarse</title>

  <?php include './includes/icon.php' ?>
  <!-- // Icono del sitio -->

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- // Bootstrap para estilos -->

  <link rel="stylesheet" href="assets/css/styles.css">
  <!-- // Estilos personalizados -->
</head>
<body>

  <?php include 'includes/navbar.php'; ?>
  <!-- // Navbar principal -->

  <main class="container py-5">
    <div class="card mx-auto" style="max-width:540px">
      <!-- // Contenedor centrado del formulario -->

      <div class="card-body">
        <h3 class="card-title">Crear cuenta</h3>

        <!-- // Formulario de registro -->
        <form id="registerForm" novalidate>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input required type="email" name="email" class="form-control">
            <!-- // Campo de email obligatorio -->
          </div>

          <div class="mb-3 row">
            <!-- // Campos de nombre y apellido organizados en columnas -->
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
            <!-- // Teléfono obligatorio -->
          </div>

          <div class="mb-3">
            <label class="form-label">DNI</label>
            <input required name="dni" class="form-control" placeholder="DNI">
            <!-- // DNI obligatorio y usado también para validar duplicados -->
          </div>

          <div class="mb-3">
            <label class="form-label">Contraseña (mín 8)</label>
            <input required type="password" name="password" minlength="8" class="form-control">
            <!-- // Contraseña con validación mínima de longitud -->
          </div>

          <div id="registerMsg" class="mb-3"></div>
          <!-- // Contenedor donde se muestran mensajes del servidor -->

          <button class="btn btn-primary" type="submit">Registrarme</button>
          <!-- // Enviar formulario -->
        </form>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <!-- // Scripts de Bootstrap -->

  <script src="assets/js/app.js"></script>
  <!-- // JS general del proyecto -->

  <script>
  // // Maneja el envío del formulario por AJAX
  document.getElementById('registerForm').addEventListener('submit', async function(e){
    e.preventDefault(); 
    // // Evita que el formulario recargue la página

    const form = new FormData(this);
    // // Captura los valores del formulario

    const res = await fetch('../api/register.php',{method:'POST',body:form});
    // // Envía la información al backend para crear el usuario

    const data = await res.json();
    const msg = document.getElementById('registerMsg');

    // // Si se creó correctamente, muestra éxito y limpia el formulario
    if(data.success){
      msg.innerHTML = '<div class="alert alert-success">'+data.message+'</div>';
      this.reset();
    }
    else {
      // // Si hubo error, lo muestra en pantalla
      msg.innerHTML = '<div class="alert alert-danger">'+(data.error||'Error')+'</div>';
    }
  });
  </script>

</body>
</html>
