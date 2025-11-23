<?php
require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();
/*
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dni = $_POST["dni"];

    // Preparar la consulta SQL de eliminación mediante el estado
    $sql_delete = $con->prepare("UPDATE repartidor SET estado_R = ? WHERE dni_R = ?;");
    $estado = 0;
    $sql_delete->bindParam(1, $estado, PDO::PARAM_INT);
    $sql_delete->bindParam(2, $dni, PDO::PARAM_INT);

    if ($resultado = $sql_delete->execute()) {
        echo "Repartidor Eliminado";

    } else {
        echo "Hubo un error al eliminar repartidor";
    }
}
*/
// Verificar si se envió la solicitud para eliminar un repartidor
if (isset($_POST['elimina']) && $_POST['elimina'] === 'eliminar' && isset($_POST['dni'])) {

    // Vincular el dno del repartidor y ejecutar la consulta de eliminación
    $dni = $_POST['dni'];
    
    // Preparar la consulta SQL de eliminación mediante el estado
    $sql_delete = $con->prepare("UPDATE repartidor SET estado_R = ? WHERE dni_R = ?;");
    $estado = 0;
    $sql_delete->bindParam(1, $estado, PDO::PARAM_INT);
    $sql_delete->bindParam(2, $dni, PDO::PARAM_INT);

    header('Content-Type: application/json');
    if ($resultado = $sql_delete->execute()) {
        echo json_encode(["status" => "success", "message" => "Repartidor eliminado correctamente"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar repartidor"]);
        exit;
    }

}

// Verificar si se envió un código a través de AJAX para verificar su existencia
if (isset($_POST['dni'])) {
    // Obtener el código enviado por AJAX
    $dni = $_POST['dni'];

    // Realizar la consulta para verificar si el código existe
    $sql = $con->prepare("SELECT COUNT(*) AS count FROM repartidor WHERE dni = ?");
    $sql->execute([$dni]);
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);

    // Devolver respuesta al cliente
    echo json_encode(['exists' => $resultado['count'] > 0]);
    exit(); // Detener la ejecución del script después de manejar la solicitud AJAX
}
?>