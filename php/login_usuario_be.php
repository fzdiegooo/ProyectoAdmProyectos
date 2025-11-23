<?php
session_start();
include 'database.php';

//Se da a entender que la respuesta se dará en JSON 
//Para el frontend
header('Content-Type: application/json');

try {
    //El database hace la conexión con la base de datos
    $conexion = Database::conectar();
    //Recepciona y limpia los espacios en blanco.
    $correo = trim($_POST['correo']);
    $contrasena = trim($_POST['contrasena']);

    // Buscar usuario en la tabla unificada de usuarios (incluyendo secret para 2FA)
    $stmt_usuario = $conexion->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt_usuario->bindParam(':correo', $correo);
    $stmt_usuario->execute();
    $usuario = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

    // Verificación de credenciales y redirección según rol
    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        $_SESSION['usuario'] = $usuario['correo'];
        $_SESSION['tipo'] = $usuario['rol']; // Almacena el rol en la sesión
        $_SESSION['dni'] = $usuario['dni'] ?? null;
        $_SESSION['direccion'] = $usuario['direccion'] ?? null;

        // Verificar si tiene 2FA activado
        if (!empty($usuario['secret'])) {
            // Tiene 2FA activado, guardar datos temporales y redirigir a verificación
            $_SESSION['pending_2fa'] = true;
            $_SESSION['temp_secret'] = $usuario['secret'];
            $_SESSION['temp_rol'] = $usuario['rol'];
            
            echo json_encode([
                "success" => true,
                "requires_2fa" => true,
                "message" => "Verificación 2FA requerida",
                "redirect" => "php/verificar_2fa.php"
            ]);
            exit;
        }

        // No tiene 2FA, proceder con login normal según rol
        unset($_SESSION['pending_2fa']);

        // Redirección según el rol del usuario
        switch ($usuario['rol']) {
            case 'admin':
                echo json_encode([
                    "success" => true, 
                    "message" => "Inicio de sesión como Administrador.", 
                    "redirect" => "Admin/admin-page.php"
                ]);
                break;
                
            case 'vendedor':
                echo json_encode([
                    "success" => true, 
                    "message" => "Bienvenido Trabajador.", 
                    "redirect" => "Trabajador/pedidos.php"
                ]);
                break;
                
            case 'cliente':
                if ($usuario['estado'] == 0) {
                    echo json_encode([
                        "success" => false, 
                        "message" => "Cuenta inactiva"
                    ]);
                    break;
                }
                echo json_encode([
                    "success" => true, 
                    "message" => "Inicio de sesión exitoso.", 
                    "redirect" => "Cliente/cliente-page.php"
                ]);
                break;
                
            default:
                echo json_encode([
                    "success" => false, 
                    "message" => "Rol de usuario no reconocido"
                ]);
        }
        exit;
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Usuario o contraseña incorrectos. Verifique sus datos."
        ]);
        exit;
    }

    //Si el error ocurre en laa base de datos se mandará un mensaje 
    //de error de conexión
}catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error de conexión: " . $e->getMessage()]);
    exit;
}
?>