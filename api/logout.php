<?php
session_start(); // inicia la sesión para poder destruirla

// solo se permite POST para cerrar sesión
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['error'=>'Método no permitido']);
    exit;
}

// limpia el arreglo de sesión
$_SESSION = [];

// si la sesión usa cookies, se elimina la cookie asociada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); // obtiene parámetros actuales de la cookie
    setcookie(
        session_name(),     // nombre de la cookie de sesión
        '',                 // valor vacío
        time() - 42000,     // expira en el pasado para forzar eliminación
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// destruye la sesión en el servidor
session_destroy();

header('Content-Type: application/json');
// respuesta JSON indicando cierre exitoso
echo json_encode(['success'=>true,'message'=>'Sesión cerrada']);
