<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'cliente') {
        header("Location: ../Cliente/cliente-page.php");
        exit();
    }
}

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

// Consulta SQL para seleccionar los datos del trabajador utilizando el DNI (incluyendo secret)
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, correo, direccion, secret FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

// Asignar los datos del usuario a variables individuales
$nombres = $usuario['nombres'];
$apellido_paterno = $usuario['apellido_paterno'];
$apellido_materno = $usuario['apellido_materno'];
$celular = $usuario['celular'];
$direccion = $usuario['direccion'];
$correo = $usuario['correo'];

// Obtener ventas pendientes reales de la base de datos CON MARCA Y PROVEEDOR
$sql = $con->prepare("SELECT v.*, u.nombres, u.apellido_paterno, u.correo, u.celular, u.direccion 
                      FROM ventas v 
                      LEFT JOIN usuarios u ON v.id_cliente = u.dni 
                      WHERE v.estado = 'Pendiente' 
                      ORDER BY v.fecha_venta ASC");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Pedidos Pendientes</title>
    
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    
    <style>
        .bg-custom-color { background-color: #4e73df; }
        .card { border-left: 5px solid #4e73df; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-pending { background-color: #ffc107; color: #000; }
        .btn-action { margin-right: 5px; }
        .total-price { font-weight: bold; color: #2e59d9; }
        .urgent { border-left: 5px solid #dc3545 !important; }
        .marca-badge {
            background-color: #6c757d;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 5px;
        }
        .proveedor-badge {
            background-color: #17a2b8;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
        }
        .trabajador-badge {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-custom-color sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-text mx-3">Delgado Electronic</div>
            </a>
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Perfil</div>
            
            <li class="nav-item active">
                <a class="nav-link" href="datos_trabajador.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Datos Personales</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Gestión de Pedidos</div>
            
            <li class="nav-item active">
                <a class="nav-link" href="pedidos.php">
                    <i class="fas fa-fw fa-clock"></i>
                    <span>Pendientes</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="entregados.php">
                    <i class="fas fa-fw fa-check-circle"></i>
                    <span>Entregados</span>
                </a>
            </li>
            
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn bg-custom-color d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo $nombres; ?> 
                                    <span class="trabajador-badge">VENDEDOR</span>
                                </span>                                
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="datos_trabajador.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Perfil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../php/cerrar_sesion.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Cerrar Sesión
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <!-- Page Content -->
                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Pedidos Pendientes</h1>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pedidos Pendientes</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($resultado); ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Valor Pendiente</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $total_pendiente = array_sum(array_column($resultado, 'total_venta'));
                                                echo MONEDA . number_format($total_pendiente, 2); 
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos List -->
                    <?php if (empty($resultado)): ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                            <h5>No hay pedidos pendientes</h5>
                            <p>Todos los pedidos han sido procesados.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($resultado as $venta) : 
                                $fechaPedido = new DateTime($venta['fecha_venta']);
                                $ahora = new DateTime();
                                $intervalo = $fechaPedido->diff($ahora);
                                $horasTranscurridas = $intervalo->h + ($intervalo->days * 24);
                                $esUrgente = $horasTranscurridas > 24;
                            ?>
                                <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                                    <div class="card <?php echo $esUrgente ? 'urgent' : ''; ?>">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Pedido #<?php echo $venta['id_venta']; ?></h5>
                                            <div>
                                                <span class="status-badge status-pending">Pendiente</span>
                                                <?php if ($esUrgente): ?>
                                                    <span class="badge badge-danger ml-2">URGENTE</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <!-- Información del pedido -->
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Transacción:</strong> <?php echo $venta['id_transaccion']; ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Tiempo espera:</strong> 
                                                        <?php 
                                                            if ($intervalo->d > 0) {
                                                                echo $intervalo->d . ' días, ' . $intervalo->h . ' horas';
                                                            } else {
                                                                echo $intervalo->h . ' horas, ' . $intervalo->i . ' minutos';
                                                            }
                                                        ?>
                                                    </p>
                                                </div>
                                            </div>
                                            <hr>

                                            <!-- Datos del cliente -->
                                            <h6><i class="fas fa-user mr-2"></i>Datos del Cliente</h6>
                                            <div class="row mb-3">
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Nombre:</strong> <?php echo htmlspecialchars($venta['nombres'] . ' ' . $venta['apellido_paterno']); ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($venta['correo']); ?></p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p class="mb-1"><strong>Celular:</strong> <?php echo htmlspecialchars($venta['celular']); ?></p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="mb-1"><strong>Dirección:</strong> <?php echo htmlspecialchars($venta['direccion']); ?></p>
                                                </div>
                                            </div>
                                            <hr>

                                            <!-- Detalles de productos CON MARCA Y PROVEEDOR -->
                                            <h6><i class="fas fa-list-ul mr-2"></i>Productos</h6>
                                            <?php
                                            $sql_detalles = $con->prepare("SELECT dv.*, p.descripcion as producto_descripcion, m.nombre_marca, pr.nombre_proveedor 
                                                                           FROM detalles_ventas dv 
                                                                           LEFT JOIN productos p ON dv.id_producto = p.codigo 
                                                                           LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                                                                           LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor 
                                                                           WHERE dv.id_venta = ?");
                                            $sql_detalles->execute([$venta['id_venta']]);
                                            $detalles = $sql_detalles->fetchAll(PDO::FETCH_ASSOC);
                                            ?>
                                            
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Producto</th>
                                                            <th>Marca</th>
                                                            <th>Proveedor</th>
                                                            <th>Precio</th>
                                                            <th>Cantidad</th>
                                                            <th>Subtotal</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($detalles as $detalle) : ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($detalle['descripcion']); ?></td>
                                                            <td>
                                                                <?php if (!empty($detalle['nombre_marca'])): ?>
                                                                    <span class="marca-badge"><?php echo htmlspecialchars($detalle['nombre_marca']); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Sin marca</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <?php if (!empty($detalle['nombre_proveedor'])): ?>
                                                                    <span class="proveedor-badge"><?php echo htmlspecialchars($detalle['nombre_proveedor']); ?></span>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Sin proveedor</span>
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

                                            <!-- Acciones -->
                                            <div class="d-flex justify-content-between align-items-center mt-3">
                                                <div>
                                                    <h5 class="total-price">Total: <?php echo MONEDA . number_format($venta['total_venta'], 2); ?></h5>
                                                </div>
                                                <div>
                                                    <button class="btn btn-success btn-sm" onclick="cambiarEstado(<?php echo $venta['id_venta']; ?>, 'Entregado')">
                                                        <i class="fas fa-check mr-1"></i> Marcar como Entregado
                                                    </button>
                                                    <button class="btn btn-danger btn-sm ml-2" onclick="cambiarEstado(<?php echo $venta['id_venta']; ?>, 'Cancelado')">
                                                        <i class="fas fa-times mr-1"></i> Cancelar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    
    <script>
        function cambiarEstado(idVenta, nuevoEstado) {
            if (confirm('¿Está seguro de cambiar el estado de este pedido a "' + nuevoEstado + '"?')) {
                $.ajax({
                    url: 'actualizar_estado_pedido.php',
                    method: 'POST',
                    data: {
                        id_venta: idVenta,
                        estado: nuevoEstado
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert('Estado actualizado correctamente');
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error al procesar la solicitud');
                    }
                });
            }
        }
    </script>
</body>
</html>
