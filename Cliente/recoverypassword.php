<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require '../phpmailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';   
require '../phpmailer/src/SMTP.php';
include '../php/database.php';  // Asegúrate de que esta ruta sea correcta

$correo = $_POST['correo'];  // El correo electrónico se obtiene desde el formulario

try {
    // Verificar si el correo es válido
    if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        die('El correo electrónico es inválido o no ha sido proporcionado.');
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
            $mail->Username = 'alvaroarroyocdnm@gmail.com';  // Tu correo de Gmail
            $mail->Password = 'ykff ytnw pxcz xaeo';  // Tu contraseña de correo (recomendado usar contraseñas de aplicación)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;  // Puerto para usar SSL
            //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            //$mail->Port = 587; 
            //587 465
            // Configuración del remitente (asegúrate de poner tu dirección de correo válida)
            $mail->setFrom('alvaroarroyocdnm@gmail.com', 'AUXILIUM-FARMA');
            
            // Validación del correo de destino
            if (empty($correo)) {
                die('No se proporcionó una dirección de correo electrónico válida para el destinatario.');
            }
            // Configuración del destinatario
            $mail->addAddress($correo, $usuario);  // Enviar correo al usuario con su nombre

            // Contenido del correo
            $mail->isHTML(true);  // Configurar el formato del correo a HTML
            $mail->Subject = 'Recuperación de Contraseña';
            $mail->Body = "Hola, $usuario. Este correo fue generado para realizar el cambio de tu contraseña. Sigue las instrucciones del siguiente enlace: 
            <a href='http://localhost/ProyectoCapstone/Cliente/change_password.php?dni=$dniUsuario'>Recuperar contraseña</a>";

            // Activar la depuración SMTP para más detalles (opcional)
            $mail->SMTPDebug = 2;  // Muestra información detallada de depuración
            $mail->Debugoutput = 'html'; // Muestra errores en formato HTML
            // Enviar el correo
            $mail->send();

            header("Location: recovery.php?alert=1");     // Redirigir a la página de recuperación con éxito
        } catch (Exception $e) {
            // Mostrar detalles de error si el correo no se envía   
            echo "Error al enviar el correo: " . $mail->ErrorInfo . "<br>";
            header("Location: recovery.php?alert=2");  // Alerta de error al enviar correo
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
