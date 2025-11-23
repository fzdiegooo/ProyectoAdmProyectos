<?php
session_start();

if (!isset($_GET['id_venta']) || !isset($_GET['transaccion'])) {
    header("Location: carritodetalles_cliente.php");
    exit();
}

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

$id_venta = $_GET['id_venta'];
$transaccion = $_GET['transaccion'];

// Obtener detalles de la venta
$sql_venta = $con->prepare("SELECT * FROM ventas WHERE id_venta = ? AND id_transaccion = ?");
$sql_venta->execute([$id_venta, $transaccion]);
$venta = $sql_venta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    header("Location: carritodetalles_cliente.php");
    exit();
}

// Obtener detalles de productos CON MARCA
$sql_detalles = $con->prepare("SELECT dv.*, p.descripcion as producto_descripcion, m.nombre_marca 
                               FROM detalles_ventas dv 
                               LEFT JOIN productos p ON dv.id_producto = p.codigo 
                               LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                               WHERE dv.id_venta = ?");
$sql_detalles->execute([$id_venta]);
$detalles = $sql_detalles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - Delgado Electronic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .marca-badge {
            background-color: #6c757d;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-success text-white text-center">
                        <h4><i class="fas fa-check-circle me-2"></i>¡Compra Realizada con Éxito!</h4>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">Tu pedido ha sido procesado correctamente</h5>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <p><strong>Número de Pedido:</strong> #<?php echo $venta['id_venta']; ?></p>
                                <p><strong>ID de Transacción:</strong> <?php echo $venta['id_transaccion']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
                                <p><strong>Total Pagado:</strong> <?php echo MONEDA . number_format($venta['total_venta'], 2); ?></p>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6>Productos Comprados:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Marca</th>
                                        <th>Precio</th>
                                        <th>Cantidad</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($detalles as $detalle): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($detalle['descripcion']); ?></td>
                                        <td>
                                            <?php if (!empty($detalle['nombre_marca'])): ?>
                                                <span class="marca-badge"><?php echo htmlspecialchars($detalle['nombre_marca']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin marca</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo MONEDA . number_format($detalle['precio'], 2); ?></td>
                                        <td><?php echo $detalle['cantidad']; ?></td>
                                        <td><?php echo MONEDA . number_format($detalle['precio'] * $detalle['cantidad'], 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Información Importante:</h6>
                            <ul class="mb-0">
                                <li>Tu pedido está siendo procesado y será enviado pronto</li>
                                <li>Recibirás una confirmación por email</li>
                                <li>Puedes seguir el estado de tu pedido en "Mis Compras"</li>
                                <li>El tiempo de entrega estimado es de 1-3 días hábiles</li>
                            </ul>
                        </div>
                        
                        <div class="text-center">
                            <a href="cliente-page.php" class="btn btn-primary me-2">
                                <i class="fas fa-home me-1"></i> Volver a la Tienda
                            </a>
                            <a href="compras_cliente.php" class="btn btn-success">
                                <i class="fas fa-list me-1"></i> Ver Mis Compras
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
