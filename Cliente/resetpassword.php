<?php
session_start();
extract($_POST);  // Extrae las variables enviadas por el formulario (incluyendo 'newclave' y 'dni')
include '../php/database.php';  // Conexión a la base de datos

// Variables del formulario
$newPassword = $_POST['newclave'];  // Nueva contraseña proporcionada por el usuario
$dni = $_POST['dni'];  // DNI del usuario

// Cifra la nueva contraseña usando PASSWORD_DEFAULT
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// Consulta para actualizar la contraseña en la base de datos
$query = "UPDATE usuarios SET contrasena = :newPassword WHERE dni = :dni";

try {
    // Obtenemos la conexión PDO
    $conexion = Database::conectar();

    // Preparamos la consulta
    $stmt = $conexion->prepare($query);

    // Vinculamos los parámetros con las variables
    $stmt->bindParam(':newPassword', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);

    // Ejecutamos la consulta
    if ($stmt->execute()) {
        // Si la contraseña se actualiza correctamente, redirigimos al usuario
        header("Location: http://localhost/ProyectoCapstone/index.php");
    } else {
        // Si ocurre un error al actualizar
        header("Location: http://localhost/ProyectoCapstone/index.php");
    }
} catch (PDOException $e) {
    // Si ocurre un error con la base de datos, redirigimos con el error
    header("Location: index.php?msg=Database error: " . $e->getMessage());
}
?>
