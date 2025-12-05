<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';   
require '../phpmailer/src/SMTP.php';
include '../php/database.php';  // Asegúrate de que esta ruta sea correcta

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: recovery.php");
    exit;
}

// Verificar que el correo exista en POST
if (!isset($_POST['correo']) || empty($_POST['correo'])) {
    header("Location: recovery.php?alert=2");
    exit;
}

$correo = $_POST['correo'];  // El correo electrónico se obtiene desde el formulario

try {
    // Verificar si el correo es válido
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: recovery.php?alert=2");
        exit;
    }

    // Conectar a la base de datos usando PDO
    $conexion = Database::conectar();
    
    // Preparar la consulta SQL usando PDO
    $query = "SELECT * FROM usuarios WHERE correo = :correo";  // Filtrar por correo
    $stmt = $conexion->prepare($query);
    
    // Vincular el parámetro correctamente
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);  // Vincula el valor del correo como cadena
    $stmt->execute();  // Ejecutar la consulta

    // Obtener el resultado de la consulta
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {  // Si se encontró el usuario
        $usuario = $row['nombres'];  // Suponiendo que 'nombres' es el campo con el nombre del usuario
        $dniUsuario = $row['dni'];    // Ahora usamos 'dni' como el identificador del usuario

        // Crear la instancia de PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';  // Enviar usando SMTP
            $mail->Host = 'smtp.gmail.com';  // Dirección SMTP de Gmail
            $mail->SMTPAuth = true;  // Habilitar autenticación SMTP
            $mail->Username = 'zxiteft21@gmail.com';  // Tu correo de Gmail
            $mail->Password = 'wisp klzo tnys ycjd';  // Tu contraseña de correo (recomendado usar contraseñas de aplicación)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;  // Puerto para usar TLS
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            //$mail->Port = 587; 
            //587 465
            // Configuración del remitente (asegúrate de poner tu dirección de correo válida)
            $mail->setFrom('zxiteft21@gmail.com', 'DELGADO ELECTRONICS');
            
            // Validación del correo de destino
            if (empty($correo)) {
                die('No se proporcionó una dirección de correo electrónico válida para el destinatario.');
            }
            // Configuración del destinatario
            $mail->addAddress($correo, $usuario);  // Enviar correo al usuario con su nombre

            // Contenido del correo
            $mail->isHTML(true);  // Configurar el formato del correo a HTML
            $mail->Subject = 'Recuperación de Contraseña';
            // Obtener la URL base del proyecto
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            // Usar str_replace para normalizar las barras invertidas a barras normales
            $scriptPath = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
            $baseUrl = $protocol . '://' . $host . $scriptPath;
            $recoveryUrl = $baseUrl . '/Cliente/change_password.php?dni=' . $dniUsuario;
            $mail->Body = "Hola, $usuario. Este correo fue generado para realizar el cambio de tu contraseña. Sigue las instrucciones del siguiente enlace: 
            <a href='$recoveryUrl'>Recuperar contraseña</a>";

            // Desactivar la depuración en producción
            $mail->SMTPDebug = 0;  // 0 = sin debug, 2 = mensajes detallados
            $mail->Debugoutput = 'html';
            
            // Enviar el correo
            $mail->send();

            header("Location: recovery.php?alert=1");
            exit;
        } catch (Exception $e) {
            // Mostrar detalles de error si el correo no se envía
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            // Temporalmente mostrar el error para debugging
            die("Error al enviar el correo: " . $mail->ErrorInfo . "<br><br>Exception: " . $e->getMessage());
            // header("Location: recovery.php?alert=2");
        }
    } else {
        // Si el usuario no existe en la base de datos
        echo "Usuario no encontrado con ese correo: $correo<br>";
        header("Location: recovery.php?alert=3");  // Alerta si el usuario no existe
    }
} catch (Exception $e) {
    // Si hay un error en la conexión a la base de datos
    die("Error al conectar a la base de datos: " . $e->getMessage());
}
?>
