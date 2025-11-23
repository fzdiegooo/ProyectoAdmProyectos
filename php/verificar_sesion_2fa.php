<?php
// Incluir este archivo al inicio de todas las páginas protegidas

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Verificar si está en proceso de verificación 2FA
if (isset($_SESSION['pending_2fa'])) {
    header("Location: /verificar_2fa.php");
    exit();
}

// Función para verificar si el usuario tiene 2FA activado
function verificar2FAActivado($dni) {
    require_once '../php/database.php';
    
    $db = new Database();
    $con = $db->conectar();
    
    $sql = $con->prepare("SELECT secret FROM usuarios WHERE dni = ?");
    $sql->execute([$dni]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    
    return !empty($usuario['secret']);
}

// Opcional: Verificar periódicamente si el usuario sigue teniendo 2FA activado
// (útil si se desactiva desde otro dispositivo)
if (isset($_SESSION['dni'])) {
    $tiene2FA = verificar2FAActivado($_SESSION['dni']);
    // Puedes usar esta información para mostrar notificaciones o recordatorios
}
?>