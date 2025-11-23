<?php

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

//Verificar si el dato ingresado existe para editar un repartidor
if (isset($_POST['tipo']) && isset($_POST['valor']) && isset($_POST['dni']) && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $dni = $_POST['dni']; // Recibimos el DNI del repartidor en edición

    $campo = "";
    switch ($tipo) {
        case "telefono":
            $campo = "telefono_R";
            break;
        case "correo":
            $campo = "correo_R";
            break;
        case "placa":
            $campo = "placa";
            break;
        default:
            echo "error";
            exit;
        
    }

    // Excluir el DNI del repartidor actual en la validación
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM repartidor WHERE $campo = ? AND dni_R != ?");
    $stmt->execute([$valor, $dni]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo ($result && $result['count'] > 0) ? "existe" : "disponible";
    exit;
}


//Apartado Admin - clientes
//Verificar los datos de los usuarios antes de actualizar los datos ingresados
header("Content-Type: application/json");

if(isset($_POST['tipo']) && isset($_POST['valor']) && isset($_POST['dni_usuario']) && isset($_POST['action']) && $_POST['action'] === "edit_U") {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $dni_usuario = $_POST['dni_usuario'];
    
    // Determinar el campo correspondiente en la base de datos
    $campo = "";
    if ($tipo === "telefono") {
        $campo = "celular";
    } elseif ($tipo === "correo") {
        $campo = "correo";
    } else {
        echo json_encode(["error" => "Tipo de validación no válido"]);
        exit;  // Asegurar que el script se detiene aquí
    }

    // Excluir el DNI del usuario actual en la validación
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM usuarios WHERE $campo = ? AND dni != ?");
    $stmt->execute([$valor, $dni_usuario]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    // Comprobamos si ya existe
    echo json_encode(["unico" => $resultado['count'] == 0]);
    exit; // Evita que el script continúe ejecutando el siguiente `else`
}else{

    echo json_encode(["error" => "Datos no recibidos correctamente"]);
    exit;
}


?>