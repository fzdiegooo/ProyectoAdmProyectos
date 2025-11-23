<?php
session_start();
require_once '../GoogleAuthenticator.php';

header('Content-Type: application/json');

try {
    // Verificar que el usuario esté en proceso de 2FA
    if (!isset($_SESSION['pending_2fa']) || !isset($_SESSION['temp_secret'])) {
        echo json_encode([
            "success" => false,
            "message" => "Sesión inválida"
        ]);
        exit;
    }

    $codigo = trim($_POST['codigo_2fa']);
    $secret = $_SESSION['temp_secret'];
    
    if (strlen($codigo) != 6 || !is_numeric($codigo)) {
        echo json_encode([
            "success" => false,
            "message" => "El código debe tener 6 dígitos numéricos"
        ]);
        exit;
    }
    
    $ga = new PHPGangsta_GoogleAuthenticator();
    
    if ($ga->verifyCode($secret, $codigo, 2)) {
        // Código correcto
        $rol = $_SESSION['temp_rol'];
        
        // Limpiar variables temporales
        unset($_SESSION['pending_2fa']);
        unset($_SESSION['temp_secret']);
        unset($_SESSION['temp_rol']);
        
        // Determinar redirección según rol
        $redirects = [
            'admin' => 'Admin/admin-page.php',
            'vendedor' => 'Trabajador/pedidos.php',
            'cliente' => 'Cliente/cliente-page.php'
        ];
        
        $redirect = $redirects[$rol] ?? 'index.php';
        
        echo json_encode([
            "success" => true,
            "message" => "Verificación exitosa",
            "redirect" => $redirect
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Código incorrecto. Inténtalo de nuevo."
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la verificación: " . $e->getMessage()
    ]);
}
?>