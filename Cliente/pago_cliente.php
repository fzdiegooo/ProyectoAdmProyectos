<?php
session_start();

// Verificar sesión y carrito
if (!isset($_SESSION['usuario']) || !isset($_SESSION['carrito']['productos'])) {
    header("Location: ../index.php");
    exit();
}

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

// Procesar el pago si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_pago'])) {
    try {
        $con->beginTransaction();
        
        // Obtener productos del carrito
        $productos_carrito = $_SESSION['carrito']['productos'];
        $total = 0;
        
        // Calcular total
        foreach ($productos_carrito as $codigo => $cantidad) {
            $sql = $con->prepare("SELECT pventa FROM productos WHERE codigo = ?");
            $sql->execute([$codigo]);
            $producto = $sql->fetch(PDO::FETCH_ASSOC);
            if ($producto) {
                $total += $producto['pventa'] * $cantidad;
            }
        }
        
        // Generar ID de transacción
        $id_transaccion = 'TXN-' . strtoupper(uniqid());
        
        // Obtener datos del usuario
        $sql_usuario = $con->prepare("SELECT correo, direccion FROM usuarios WHERE dni = ?");
        $sql_usuario->execute([$_SESSION['dni']]);
        $usuario = $sql_usuario->fetch(PDO::FETCH_ASSOC);
        
        // Insertar venta
        $sql_venta = $con->prepare("INSERT INTO ventas (id_transaccion, fecha_venta, status, email, id_cliente, direccion_cliente, total_venta, estado) VALUES (?, NOW(), 'Completado', ?, ?, ?, ?, 'Pendiente')");
        $sql_venta->execute([
            $id_transaccion,
            $usuario['correo'],
            $_SESSION['dni'],
            $usuario['direccion'],
            $total
        ]);
        
        $id_venta = $con->lastInsertId();
        
        // Insertar detalles de venta
        foreach ($productos_carrito as $codigo => $cantidad) {
            $sql_producto = $con->prepare("SELECT descripcion, pventa FROM productos WHERE codigo = ?");
            $sql_producto->execute([$codigo]);
            $producto = $sql_producto->fetch(PDO::FETCH_ASSOC);
            
            if ($producto) {
                $sql_detalle = $con->prepare("INSERT INTO detalles_ventas (id_venta, id_producto, descripcion, precio, cantidad) VALUES (?, ?, ?, ?, ?)");
                $sql_detalle->execute([
                    $id_venta,
                    $codigo,
                    $producto['descripcion'],
                    $producto['pventa'],
                    $cantidad
                ]);
                
                // Actualizar stock
                $sql_stock = $con->prepare("UPDATE productos SET stock = stock - ? WHERE codigo = ?");
                $sql_stock->execute([$cantidad, $codigo]);
            }
        }
        
        $con->commit();
        
        // Limpiar carrito
        unset($_SESSION['carrito']);
        
        // Redirigir a página de éxito
        header("Location: pago_exitoso.php?id_venta=" . $id_venta . "&transaccion=" . $id_transaccion);
        exit();
        
    } catch (Exception $e) {
        $con->rollback();
        $error_message = "Error al procesar el pago: " . $e->getMessage();
    }
}

// Obtener productos del carrito para mostrar CON MARCA
$productos_carrito = $_SESSION['carrito']['productos'];
$lista_carrito = array();
$total = 0;

foreach ($productos_carrito as $codigo => $cantidad) {
    $sql = $con->prepare("SELECT p.codigo, p.descripcion, p.pventa, m.nombre_marca 
                          FROM productos p 
                          LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                          WHERE p.codigo = ?");
    $sql->execute([$codigo]);
    $producto = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($producto) {
        $producto['cantidad'] = $cantidad;
        $producto['subtotal'] = $producto['pventa'] * $cantidad;
        $lista_carrito[] = $producto;
        $total += $producto['subtotal'];
    }
}

// Obtener datos del usuario
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, direccion FROM usuarios WHERE dni = ?");
$sql->execute([$_SESSION['dni']]);
$usuario = $sql->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de Pago - Delgado Electronic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
    <!-- SDK MercadoPago.js -->
    <style>
        .resumen-pago {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .btn-pagar {
            background-color: #28a745;
            color: white;
            font-weight: bold;
            padding: 12px 20px;
            border-radius: 8px;
            border: none;
            transition: all 0.3s;
        }
        .btn-pagar:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
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
    <!-- Incluir cabeceras similares a otros archivos -->
    <div class="content fixed-header">
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
         style="height: 35px; background-color: #5EBC50 !important;">
        <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
            <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 945853331
            </a>
        </div>
        </nav>

        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">
            <img src="../images/logo-completo.png" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 75px; width: auto; margin-top: 10px">
            <img src="../images/Icons/logo-icono.png" alt="Logo" class="navbar-brand logoPrincipal leftImage d-sm-none" style="height: 50px; width: auto;">
        </nav>
    </div>
    
    <div class="container mt-5 mb-5" style="margin-top: 150px !important;">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <h1 class="text-center mb-4">Resumen de Compra</h1>
        
        <div class="row">
            <!-- Resumen de productos -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5>Productos en el carrito</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Marca</th>
                                    <th>Precio Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lista_carrito as $producto): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($producto['descripcion']); ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($producto['nombre_marca'])): ?>
                                            <span class="marca-badge"><?php echo htmlspecialchars($producto['nombre_marca']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Sin marca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo MONEDA . number_format($producto['pventa'], 2); ?></td>
                                    <td><?php echo $producto['cantidad']; ?></td>
                                    <td><?php echo MONEDA . number_format($producto['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Datos de envío y pago -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5>Datos de Envío</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellido_paterno'] . ' ' . $usuario['apellido_materno']); ?></p>
                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($usuario['direccion']); ?></p>
                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['celular']); ?></p>
                    </div>
                </div>
                
                <div class="card resumen-pago">
                    <h5 class="card-title">Resumen del Pago</h5>
                    <hr>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span><?php echo MONEDA . number_format($total, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Envío:</span>
                        <span><?php echo MONEDA . '0.00'; ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total a pagar:</span>
                        <span><?php echo MONEDA . number_format($total, 2); ?></span>
                    </div>
                    
                    <!-- Formulario de pago -->
                    <form method="POST" class="mt-4">
                        <div class="d-grid">
                            <button type="submit" name="procesar_pago" class="btn-pagar">
                                <i class="fas fa-credit-card me-2"></i> Confirmar Pago
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
