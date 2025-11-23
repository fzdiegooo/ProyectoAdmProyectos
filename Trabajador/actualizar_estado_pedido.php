<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'vendedor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require '../Config/config.php';
require '../php/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();
    $con = $db->conectar();
    
    $id_venta = $_POST['id_venta'] ?? '';
    $nuevo_estado = $_POST['estado'] ?? '';
    
    // Validar estados permitidos
    $estados_permitidos = ['Pendiente', 'Entregado', 'Cancelado'];
    
    if (!in_array($nuevo_estado, $estados_permitidos)) {
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        exit();
    }
    
    try {
        $sql = $con->prepare("UPDATE ventas SET estado = ? WHERE id_venta = ?");
        $resultado = $sql->execute([$nuevo_estado, $id_venta]);
        
        if ($resultado) {
            echo json_encode([
                'success' => true, 
                'message' => 'Estado actualizado correctamente',
                'nuevo_estado' => $nuevo_estado
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>
