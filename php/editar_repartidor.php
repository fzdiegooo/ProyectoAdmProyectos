<?php
/*
require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los datos existen antes de acceder a ellos
    if (
        isset($_POST["editDni"], $_POST["editNombre"], $_POST["editApellidoPaterno"], $_POST["editApellidoMaterno"], 
              $_POST["editTelefono"], $_POST["editFechaNacimiento"], $_POST["editCorreo"], 
              $_POST["editGenero"], $_POST["editDireccion"], $_POST["editPlaca"], $_POST["editVehiculo"])
    ) {
        
        $dni = trim($_POST["editDni"]);
        $nombre = trim($_POST["editNombre"]);
        $apellido_paterno = trim($_POST["editApellidoPaterno"]);
        $apellido_materno = trim($_POST["editApellidoMaterno"]);
        $telefono = trim($_POST["editTelefono"]);
        $fecha_nacimiento = trim($_POST["editFechaNacimiento"]);
        $correo = trim($_POST["editCorreo"]);
        $genero = trim($_POST["editGenero"]);
        $direccion = trim($_POST["editDireccion"]);
        $placa = trim($_POST["editPlaca"]);
        $vehiculo = trim($_POST["editVehiculo"]);

        // Validar que los campos no estén vacíos
        if (
            empty($dni) || empty($nombre) || empty($apellido_paterno) || empty($apellido_materno) ||
            empty($telefono) || empty($fecha_nacimiento) || empty($correo) || 
            empty($genero) || empty($direccion) || empty($placa) || empty($vehiculo)
        ) {
            echo "error: campos vacíos";
            exit;
        }

        // Consulta SQL para actualizar el repartidor
        $sql = "UPDATE repartidor SET 
                    nombres_R = ?, 
                    apellido_paterno_R = ?, 
                    apellido_materno_R = ?, 
                    telefono_R = ?, 
                    fecha_nacimiento_R = ?, 
                    correo_R = ?, 
                    genero_R = ?, 
                    direccion_R = ?, 
                    placa = ?, 
                    vehiculo = ? 
                WHERE dni_R = ?";

        $stmt = $con->prepare($sql);
        $resultado = $stmt->execute([
            $nombre, $apellido_paterno, $apellido_materno, $telefono, 
            $fecha_nacimiento, $correo, $genero, $direccion, $placa, $vehiculo, $dni
        ]);

        if ($resultado) {
            echo "success";
        } else {
            echo "error: no se pudo actualizar";
        }

    } else {
        echo "error: datos no recibidos";
    }
} else {
    echo "error: método incorrecto";
}
    */
?>