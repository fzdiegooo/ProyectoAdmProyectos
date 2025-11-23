<?php

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();


//Verificar si el dato ingresado existe para agregar un repartidor
if (isset($_POST['tipo']) && isset($_POST['valor']) && isset($_POST['dni']) && isset($_POST['action']) && $_POST['action'] === 'agre') {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $dni = $_POST['dni']; // Recibimos el DNI del repartidor en edición

    $campo = "";
    switch ($tipo) {

        case "dni":
            $campo = "dni_R";
            break;
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
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM repartidor WHERE $campo = ?");
    $stmt->execute([$valor]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo ($result && $result['count'] > 0) ? "existe" : "disponible";
    exit;
}
//REGISTRO USUARIO (El propio usuario crea su cuenta)
//Verificar si existe datos iguales en los campos UNIQUE (dni,telefono,correo) de la tabla usuarios (Registro Usuario)
if (isset($_POST['tipo']) && isset($_POST['valor']) && isset($_POST['dni']) && isset($_POST['action']) && $_POST['action'] === 'agre_U') {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $dni = $_POST['dni']; // Recibimos el DNI del repartidor en edición

    $campo = "";
    switch ($tipo) {

        case "dni":
            $campo = "dni";
            break;
        case "telefono":
            $campo = "celular";
            break;
        case "correo":
            $campo = "correo";
            break;
        default:
            echo "error";
            exit;
    }

    // Excluir el DNI del repartidor actual en la validación
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM usuarios WHERE $campo = ?");
    $stmt->execute([$valor]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo ($result && $result['count'] > 0) ? "existe" : "disponible";
    exit;
}
//Crear una cuenta de usuario desde el apartado Admin
header("Content-Type: application/json");
//Verificar si los datos ingresados ya están registrados en la BD, en los campos unicos (celular,dni, correo) para el apartado Admin - Usuarios (clientes)
if ( isset($_POST['tipo']) && isset($_POST['valor']) && isset($_POST['action']) && $_POST['action'] === 'agre_U2' ) {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];

    // Determinar el campo correspondiente en la base de datos
    $campo = "";
    if ($tipo === "dni") {
        $campo = "dni";
    } elseif ($tipo === "telefono") {
        $campo = "celular";
    } elseif ($tipo === "correo") {
        $campo = "correo";
    } else {
        echo json_encode(["error" => "Tipo de validación no válido"]);
        exit;
    }

    // Excluir el DNI del repartidor actual en la validación
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM usuarios WHERE $campo = ?");
    $stmt->execute([$valor]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Comprobamos si ya existe
    echo json_encode(["unico" => $resultado['count'] == 0]);
    exit; // Evita que el script continúe ejecutando el siguiente `else`
} else {
    echo json_encode(["error" => "Datos no recibidos correctamente"]);
    exit;
}

?>
