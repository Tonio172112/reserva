<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
<a class="navbar-brand d-flex align-items-center" href="../index.php">
  <div style="width:80px;height:80px;background:#fff;border-radius:6px;margin-right:10px;display:flex;align-items:center;justify-content:center;color:#000;font-weight:700;">
    <img src="../assets/logo.png" alt="logo" style="max-width:100%; max-height:100%; object-fit:contain;">
  </div>
</a>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMain">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="../index.php">INICIO</a></li>
        <li class="nav-item"><a class="nav-link" href="../register.php">REGISTRARSE</a></li>
        <li class="nav-item"><a class="nav-link" href="../reservar.php">RESERVAR</a></li>
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="../cursos.php">CURSOS</a></li>
          <li class="nav-item"><a class="nav-link" id="logoutBtn" href="#">CERRAR SESIÓN</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="../login.php">INICIAR SESIÓN</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const logout = document.getElementById('logoutBtn');
  if(logout){
    logout.addEventListener('click', async function(e){
      e.preventDefault();
      const res = await fetch('../api/logout.php', {method:'POST'});
      const data = await res.json();
      if(data.success) window.location.href = '../index.php';
      else alert('Error al cerrar sesión');
    });
  }
});
</script>
