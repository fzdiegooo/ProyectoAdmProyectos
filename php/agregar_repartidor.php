<?php
/*
require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"];
    $nombre = $_POST["nombre"];
    $apellido_paterno = $_POST["apellido_paterno"];
    $apellido_materno = $_POST["apellido_materno"];
    $telefono = $_POST["telefono"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $correo = $_POST["correo"];
    $direccion = $_POST["direccion"];
    $placa = $_POST["placa"];
    $vehiculo = $_POST["vehiculo"];
    $genero = $_POST["genero"];
    $contrasena = isset($_POST["contrasena"]) ? $_POST["contrasena"] : null;

    if (!$contrasena) {
        echo "Error: No se recibió la contraseña.";
        exit;
    }

    $contrasena_encriptada = password_hash($contrasena, PASSWORD_DEFAULT);

    // Verificar si el DNI ya existe
    $sql_check = $con->prepare("SELECT dni_R FROM repartidor WHERE dni_R = ?");
    $sql_check->execute([$dni]);
    
    if ($sql_check->fetch()) {
        echo "Error: El DNI ya está registrado.";
        exit;
    }

    // Si el DNI no existe, proceder con la inserción
    $sql = $con->prepare("INSERT INTO repartidor (dni_R, nombres_R, apellido_paterno_R, apellido_materno_R, telefono_R, fecha_nacimiento_R, correo_R, direccion_R, placa, vehiculo, genero_R, contrasena_R, estado_R) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");

    $resultado = $sql->execute([$dni, $nombre, $apellido_paterno, $apellido_materno, $telefono, $fecha_nacimiento, $correo, $direccion, $placa, $vehiculo, $genero, $contrasena_encriptada]);

    if ($resultado) {
        echo "success";
    } else {
        echo "error";
    }
}

error_log(print_r($_POST, true)); // Esto registrará en el log lo que está recibiendo el PHP
*/
/*

// Se llaman los archivos de configuración y base de datos
require '../Config/config.php';
require '../php/database.php';


// Crear una instancia de Database y conectar a la base de datos
$db = new Database();
$con = $db->conectar();

// Obtener datos del formulario
$dni = trim($_POST['agreDni']);
$nombre = trim($_POST['agreNombre']);
$apellidoPaterno = trim($_POST['agreApellidoPaterno']);
$apellidoMaterno = trim($_POST['agreApellidoMaterno']);
$telefono = trim($_POST['agreTelefono']);
$fechaNacimiento = $_POST['agreFechaNacimiento'];
$correo = trim($_POST['agreCorreo']);
$contrasena = password_hash(trim($_POST['agreContrasena']), PASSWORD_BCRYPT);
$genero = $_POST['agreGenero'];
$direccion = trim($_POST['agreDireccion']);
$placa = trim($_POST['agrePlaca']);
$vehiculo = trim($_POST['agreVehiculo']);

// Validar unicidad en la base de datos
$query = "SELECT * FROM repartidor WHERE dni_R = ? OR telefono_R = ? OR correo_R = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $dni, $telefono, $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "DNI, teléfono o correo ya registrados."]);
    exit();
}

// Insertar repartidor en la base de datos
$query = "INSERT INTO repartidor (dni, nombre, apellido_paterno, apellido_materno, telefono, fecha_nacimiento, correo, contrasena, genero, direccion, placa, vehiculo) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $con->prepare($query);
$stmt->bind_param("ssssssssssss", $dni, $nombre, $apellidoPaterno, $apellidoMaterno, $telefono, $fechaNacimiento, $correo, $contrasena, $genero, $direccion, $placa, $vehiculo);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error al registrar repartidor."]);
}

$conn->close();
*/
?>