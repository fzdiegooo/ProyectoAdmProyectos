<?php
session_start();

// Verificar sesión de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

// Consulta SQL para seleccionar los datos del admin utilizando el DNI (incluyendo secret)
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
$secret_actual = $usuario['secret'];

// ===== CONSULTAS PARA ESTADÍSTICAS =====

// 1. Ganancias mensuales y anuales
$mes_actual = date('Y-m');
$año_actual = date('Y');

$sql_ganancias_mes = $con->prepare("SELECT COALESCE(SUM(total_venta), 0) as total_mes FROM ventas WHERE DATE_FORMAT(fecha_venta, '%Y-%m') = ? AND estado = 'Entregado'");
$sql_ganancias_mes->execute([$mes_actual]);
$ganancias_mes = $sql_ganancias_mes->fetch(PDO::FETCH_ASSOC)['total_mes'];

$sql_ganancias_año = $con->prepare("SELECT COALESCE(SUM(total_venta), 0) as total_año FROM ventas WHERE YEAR(fecha_venta) = ? AND estado = 'Entregado'");
$sql_ganancias_año->execute([$año_actual]);
$ganancias_año = $sql_ganancias_año->fetch(PDO::FETCH_ASSOC)['total_año'];

// 2. Total de productos y clientes
$sql_productos = $con->prepare("SELECT COUNT(*) as total_productos FROM productos WHERE estado = 1");
$sql_productos->execute();
$total_productos = $sql_productos->fetch(PDO::FETCH_ASSOC)['total_productos'];

$sql_clientes = $con->prepare("SELECT COUNT(*) as total_clientes FROM usuarios WHERE rol = 'cliente'");
$sql_clientes->execute();
$total_clientes = $sql_clientes->fetch(PDO::FETCH_ASSOC)['total_clientes'];

// 3. Pedidos pendientes
$sql_pendientes = $con->prepare("SELECT COUNT(*) as pedidos_pendientes FROM ventas WHERE estado = 'Pendiente'");
$sql_pendientes->execute();
$pedidos_pendientes = $sql_pendientes->fetch(PDO::FETCH_ASSOC)['pedidos_pendientes'];

// 4. Producto más vendido
$sql_producto_top = $con->prepare("SELECT p.descripcion, m.nombre_marca, SUM(dv.cantidad) as total_vendido 
                                   FROM detalles_ventas dv 
                                   JOIN productos p ON dv.id_producto = p.codigo 
                                   LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                                   JOIN ventas v ON dv.id_venta = v.id_venta 
                                   WHERE v.estado = 'Entregado' 
                                   GROUP BY dv.id_producto 
                                   ORDER BY total_vendido DESC 
                                   LIMIT 1");
$sql_producto_top->execute();
$producto_top = $sql_producto_top->fetch(PDO::FETCH_ASSOC);

// 5. Cliente que más compró
$sql_cliente_top = $con->prepare("SELECT u.nombres, u.apellido_paterno, COUNT(v.id_venta) as total_compras, SUM(v.total_venta) as total_gastado 
                                  FROM ventas v 
                                  JOIN usuarios u ON v.id_cliente = u.dni 
                                  WHERE v.estado = 'Entregado' 
                                  GROUP BY v.id_cliente 
                                  ORDER BY total_gastado DESC 
                                  LIMIT 1");
$sql_cliente_top->execute();
$cliente_top = $sql_cliente_top->fetch(PDO::FETCH_ASSOC);

// 6. Marca más popular
$sql_marca_top = $con->prepare("SELECT m.nombre_marca, COUNT(dv.id_detalle_venta) as total_ventas 
                                FROM detalles_ventas dv 
                                JOIN productos p ON dv.id_producto = p.codigo 
                                JOIN marcas m ON p.id_marca = m.id_marca 
                                JOIN ventas v ON dv.id_venta = v.id_venta 
                                WHERE v.estado = 'Entregado' 
                                GROUP BY m.id_marca 
                                ORDER BY total_ventas DESC 
                                LIMIT 1");
$sql_marca_top->execute();
$marca_top = $sql_marca_top->fetch(PDO::FETCH_ASSOC);

// 7. Ventas por mes (últimos 6 meses) para gráfico
$sql_ventas_meses = $con->prepare("SELECT DATE_FORMAT(fecha_venta, '%Y-%m') as mes, SUM(total_venta) as total 
                                   FROM ventas 
                                   WHERE fecha_venta >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND estado = 'Entregado' 
                                   GROUP BY DATE_FORMAT(fecha_venta, '%Y-%m') 
                                   ORDER BY mes");
$sql_ventas_meses->execute();
$ventas_meses = $sql_ventas_meses->fetchAll(PDO::FETCH_ASSOC);

// 8. Top 5 productos más vendidos para gráfico circular
$sql_top_productos = $con->prepare("SELECT p.descripcion, SUM(dv.cantidad) as cantidad 
                                    FROM detalles_ventas dv 
                                    JOIN productos p ON dv.id_producto = p.codigo 
                                    JOIN ventas v ON dv.id_venta = v.id_venta 
                                    WHERE v.estado = 'Entregado' 
                                    GROUP BY dv.id_producto 
                                    ORDER BY cantidad DESC 
                                    LIMIT 5");
$sql_top_productos->execute();
$top_productos = $sql_top_productos->fetchAll(PDO::FETCH_ASSOC);

// 9. Ventas recientes
$sql_ventas_recientes = $con->prepare("SELECT v.id_venta, v.fecha_venta, v.total_venta, u.nombres, u.apellido_paterno, v.estado 
                                       FROM ventas v 
                                       JOIN usuarios u ON v.id_cliente = u.dni 
                                       ORDER BY v.fecha_venta DESC 
                                       LIMIT 5");
$sql_ventas_recientes->execute();
$ventas_recientes = $sql_ventas_recientes->fetchAll(PDO::FETCH_ASSOC);

// 10. ⭐ NUEVO: Los 5 productos con MENOR stock (modificado)
$sql_stock_critico = $con->prepare("SELECT p.descripcion, p.stock, m.nombre_marca, p.codigo 
                                    FROM productos p 
                                    LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                                    WHERE p.estado = 1 
                                    ORDER BY p.stock ASC 
                                    LIMIT 5");
$sql_stock_critico->execute();
$productos_stock_critico = $sql_stock_critico->fetchAll(PDO::FETCH_ASSOC);

// 11. Ticket promedio y métricas de rendimiento
$sql_ticket_promedio = $con->prepare("SELECT AVG(total_venta) as ticket_promedio, 
                                      COUNT(*) as total_ventas_entregadas,
                                      AVG(cantidad_productos) as productos_por_venta
                                      FROM (
                                          SELECT v.total_venta, SUM(dv.cantidad) as cantidad_productos
                                          FROM ventas v 
                                          JOIN detalles_ventas dv ON v.id_venta = dv.id_venta 
                                          WHERE v.estado = 'Entregado' 
                                          GROUP BY v.id_venta
                                      ) as subquery");
$sql_ticket_promedio->execute();
$metricas_rendimiento = $sql_ticket_promedio->fetch(PDO::FETCH_ASSOC);

// 12. Valor total del inventario
$sql_valor_inventario = $con->prepare("SELECT SUM(stock * pventa) as valor_total_inventario FROM productos WHERE estado = 1");
$sql_valor_inventario->execute();
$valor_inventario = $sql_valor_inventario->fetch(PDO::FETCH_ASSOC)['valor_total_inventario'];

// 13. Productos sin ventas (productos muertos)
$sql_productos_sin_ventas = $con->prepare("SELECT COUNT(*) as productos_sin_ventas 
                                           FROM productos p 
                                           WHERE p.estado = 1 
                                           AND p.codigo NOT IN (
                                               SELECT DISTINCT dv.id_producto 
                                               FROM detalles_ventas dv 
                                               JOIN ventas v ON dv.id_venta = v.id_venta 
                                               WHERE v.estado = 'Entregado'
                                           )");
$sql_productos_sin_ventas->execute();
$productos_sin_ventas = $sql_productos_sin_ventas->fetch(PDO::FETCH_ASSOC)['productos_sin_ventas'];

// 14. Top 3 proveedores por ventas
$sql_top_proveedores = $con->prepare("SELECT pr.nombre_proveedor, 
                                      COUNT(dv.id_detalle_venta) as total_productos_vendidos,
                                      SUM(dv.precio * dv.cantidad) as total_ingresos
                                      FROM detalles_ventas dv 
                                      JOIN productos p ON dv.id_producto = p.codigo 
                                      JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor 
                                      JOIN ventas v ON dv.id_venta = v.id_venta 
                                      WHERE v.estado = 'Entregado' 
                                      GROUP BY pr.id_proveedor 
                                      ORDER BY total_ingresos DESC 
                                      LIMIT 3");
$sql_top_proveedores->execute();
$top_proveedores = $sql_top_proveedores->fetchAll(PDO::FETCH_ASSOC);

// 15. Ventas por día de la semana
$sql_ventas_dias = $con->prepare("SELECT DAYNAME(fecha_venta) as dia_semana, 
                                  COUNT(*) as total_ventas,
                                  SUM(total_venta) as total_ingresos
                                  FROM ventas 
                                  WHERE estado = 'Entregado' 
                                  AND fecha_venta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                                  GROUP BY DAYOFWEEK(fecha_venta), DAYNAME(fecha_venta)
                                  ORDER BY DAYOFWEEK(fecha_venta)");
$sql_ventas_dias->execute();
$ventas_por_dia = $sql_ventas_dias->fetchAll(PDO::FETCH_ASSOC);

// 16. Clientes nuevos vs recurrentes (este mes)
$sql_clientes_nuevos = $con->prepare("SELECT 
    COUNT(CASE WHEN primera_compra = 1 THEN 1 END) as clientes_nuevos,
    COUNT(CASE WHEN primera_compra = 0 THEN 1 END) as clientes_recurrentes
    FROM (
        SELECT v.id_cliente,
            CASE WHEN MIN(v.fecha_venta) >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END as primera_compra
        FROM ventas v 
        WHERE v.estado = 'Entregado' 
        AND DATE_FORMAT(v.fecha_venta, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')
        GROUP BY v.id_cliente
    ) as subquery");
$sql_clientes_nuevos->execute();
$clientes_analisis = $sql_clientes_nuevos->fetch(PDO::FETCH_ASSOC);

// 17. Productos más rentables (top 5)
$sql_productos_rentables = $con->prepare("SELECT p.descripcion, m.nombre_marca,
                                          SUM(dv.cantidad) as cantidad_vendida,
                                          SUM(dv.precio * dv.cantidad) as ingresos_totales,
                                          AVG(dv.precio) as precio_promedio
                                          FROM detalles_ventas dv 
                                          JOIN productos p ON dv.id_producto = p.codigo 
                                          LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                                          JOIN ventas v ON dv.id_venta = v.id_venta 
                                          WHERE v.estado = 'Entregado' 
                                          GROUP BY dv.id_producto 
                                          ORDER BY ingresos_totales DESC 
                                          LIMIT 5");
$sql_productos_rentables->execute();
$productos_rentables = $sql_productos_rentables->fetchAll(PDO::FETCH_ASSOC);

// 18. Crecimiento mes a mes (comparar con mes anterior)
$sql_crecimiento = $con->prepare("SELECT 
    SUM(CASE WHEN DATE_FORMAT(fecha_venta, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m') THEN total_venta ELSE 0 END) as ventas_mes_actual,
    SUM(CASE WHEN DATE_FORMAT(fecha_venta, '%Y-%m') = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 MONTH), '%Y-%m') THEN total_venta ELSE 0 END) as ventas_mes_anterior
    FROM ventas 
    WHERE estado = 'Entregado' 
    AND fecha_venta >= DATE_SUB(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 1 MONTH)");
$sql_crecimiento->execute();
$crecimiento_data = $sql_crecimiento->fetch(PDO::FETCH_ASSOC);

$crecimiento_porcentaje = 0;
if ($crecimiento_data['ventas_mes_anterior'] > 0) {
    $crecimiento_porcentaje = (($crecimiento_data['ventas_mes_actual'] - $crecimiento_data['ventas_mes_anterior']) / $crecimiento_data['ventas_mes_anterior']) * 100;
}

// 19. Tiempo promedio entre pedidos por cliente
$sql_frecuencia_clientes = $con->prepare("SELECT AVG(dias_entre_compras) as promedio_dias
                                          FROM (
                                              SELECT id_cliente, 
                                                     AVG(DATEDIFF(fecha_venta, fecha_anterior)) as dias_entre_compras
                                              FROM (
                                                  SELECT id_cliente, fecha_venta,
                                                         LAG(fecha_venta) OVER (PARTITION BY id_cliente ORDER BY fecha_venta) as fecha_anterior
                                                  FROM ventas 
                                                  WHERE estado = 'Entregado'
                                              ) as subquery 
                                              WHERE fecha_anterior IS NOT NULL
                                              GROUP BY id_cliente
                                              HAVING COUNT(*) > 1
                                          ) as final_query");
$sql_frecuencia_clientes->execute();
$frecuencia_promedio = $sql_frecuencia_clientes->fetch(PDO::FETCH_ASSOC)['promedio_dias'];

// ⭐ 20. NUEVO: Ventas por hora de la última semana
$sql_ventas_horas = $con->prepare("SELECT 
    HOUR(fecha_venta) as hora,
    COUNT(*) as total_ventas,
    SUM(total_venta) as total_ingresos
    FROM ventas 
    WHERE estado = 'Entregado' 
    AND fecha_venta >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY HOUR(fecha_venta)
    ORDER BY hora");
$sql_ventas_horas->execute();
$ventas_por_hora = $sql_ventas_horas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    
    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    
    <!-- Script sweetalert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .metric-card {
            transition: transform 0.3s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
        .stock-alert {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
        }
        .stock-critico {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 8px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        .stock-bajo {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            color: #212529;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 8px;
        }
        .recent-sale {
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .admin-badge {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-custom-color sidebar sidebar-dark accordion" id="accordionSidebar">
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin-page.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin</div>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item active">
                <a class="nav-link" href="admin-page.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Opciones</div>

            <li class="nav-item">
                <a class="nav-link" href="datos_admin.php">
                    <i class="fas fa-fw fa-user-cog"></i>
                    <span>Mi Perfil</span>
                </a>
            </li>

            <hr class="sidebar-divider">
            <div class="sidebar-heading">Gestión</div>

            <li class="nav-item">
                <a class="nav-link" href="productos_admin.php">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Productos</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="clientes_admin.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="proveedores_admin.php">
                    <i class="fas fa-fw fa-truck"></i>
                    <span>Proveedores</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="marcas_admin.php">
                    <i class="fas fa-fw fa-tags"></i>
                    <span>Marcas</span>
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
                        <div class="topbar-divider d-none d-sm-block"></div>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo $nombres; ?> 
                                    <span class="admin-badge">ADMIN</span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="datos_admin.php">
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

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard de Administración</h1>
                        <a href="#" class="d-none d-sm-inline-block btn btn-sm bg-custom-color shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte
                        </a>
                    </div>

                    <!-- Content Row - Métricas principales -->
                    <div class="row">
                        <!-- Ganancias Mensuales -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Ganancias (<?php echo date('F Y'); ?>)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo MONEDA . number_format($ganancias_mes, 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ganancias Anuales -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Ganancias (<?php echo date('Y'); ?>)
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo MONEDA . number_format($ganancias_año, 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Productos -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Total Productos
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_productos; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pedidos Pendientes -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pedidos Pendientes
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pedidos_pendientes; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Métricas Adicionales -->
                    <div class="row">
                        <!-- Ticket Promedio -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Ticket Promedio
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo MONEDA . number_format($metricas_rendimiento['ticket_promedio'] ?? 0, 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Valor Inventario -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Valor Inventario
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo MONEDA . number_format($valor_inventario ?? 0, 2); ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Crecimiento Mensual -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-<?php echo $crecimiento_porcentaje >= 0 ? 'success' : 'danger'; ?> shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-<?php echo $crecimiento_porcentaje >= 0 ? 'success' : 'danger'; ?> text-uppercase mb-1">
                                                Crecimiento Mensual
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo ($crecimiento_porcentaje >= 0 ? '+' : '') . number_format($crecimiento_porcentaje, 1); ?>%
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Sin Ventas -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2 metric-card">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Productos Sin Ventas
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $productos_sin_ventas; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ⭐ NUEVO: Content Row - Gráfico de Ventas por Hora -->
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">📈 Ventas por Hora (Última Semana)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="ventasHoraChart"></canvas>
                                    </div>
                                    <div class="mt-3 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Muestra el patrón de ventas por hora durante los últimos 7 días
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ⭐ NUEVO: Lista de Productos con Stock Más Bajo -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-danger">📦 Productos con Menor Stock</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($productos_stock_critico)): ?>
                                        <?php foreach ($productos_stock_critico as $index => $producto): ?>
                                        <div class="<?php echo $producto['stock'] <= 5 ? 'stock-critico' : ($producto['stock'] <= 10 ? 'stock-bajo' : 'stock-alert'); ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 25)) . '...'; ?></strong>
                                                    <br><small>Código: <?php echo $producto['codigo']; ?></small>
                                                    <br><small>Marca: <?php echo htmlspecialchars($producto['nombre_marca'] ?? 'Sin marca'); ?></small>
                                                </div>
                                                <div class="text-right">
                                                    <div class="h5 mb-0 font-weight-bold">
                                                        <?php echo $producto['stock']; ?>
                                                    </div>
                                                    <small>unidades</small>
                                                    <?php if ($producto['stock'] <= 5): ?>
                                                        <br><small><i class="fas fa-exclamation-triangle"></i> ¡CRÍTICO!</small>
                                                    <?php elseif ($producto['stock'] <= 10): ?>
                                                        <br><small><i class="fas fa-exclamation"></i> Bajo</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                        
                                        <div class="mt-3 text-center">
                                            <small class="text-muted">
                                                <i class="fas fa-chart-bar"></i> 
                                                Top 5 productos ordenados por menor stock
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-success">✅ Todos los productos tienen stock disponible</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Gráfico de Barras Ventas por Día -->
                    <div class="row">
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">📊 Ventas por Día de la Semana (Últimos 30 días)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-bar">
                                        <canvas id="myBarChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Análisis de Clientes -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">👥 Análisis de Clientes (Este Mes)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="clientesChart"></canvas>
                                    </div>
                                    <div class="mt-4 text-center small">
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-success"></i> Nuevos: <?php echo $clientes_analisis['clientes_nuevos'] ?? 0; ?>
                                        </span>
                                        <span class="mr-2">
                                            <i class="fas fa-circle text-primary"></i> Recurrentes: <?php echo $clientes_analisis['clientes_recurrentes'] ?? 0; ?>
                                        </span>
                                    </div>
                                    <?php if ($frecuencia_promedio): ?>
                                    <div class="mt-3 text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i> Frecuencia promedio: <strong><?php echo round($frecuencia_promedio); ?> días</strong>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Gráficos -->
                    <div class="row">
                        <!-- Gráfico de Ventas -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Ventas por Mes (Últimos 6 meses)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-area">
                                        <canvas id="myAreaChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico Circular - Top Productos -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Top 5 Productos Más Vendidos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-pie pt-4 pb-2">
                                        <canvas id="myPieChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row - Información adicional -->
                    <div class="row">
                        <!-- Rankings y Top Performers -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">🏆 Top Performers</h6>
                                </div>
                                <div class="card-body">
                                    <?php if ($producto_top): ?>
                                    <div class="recent-sale" style="border-left: 4px solid #28a745;">
                                        <strong>🥇 Producto Más Vendido</strong>
                                        <span class="float-right badge badge-success">
                                            <?php echo $producto_top['total_vendido']; ?> vendidos
                                        </span>
                                        <br><small><?php echo htmlspecialchars($producto_top['descripcion']); ?></small>
                                        <br><small>Marca: <?php echo htmlspecialchars($producto_top['nombre_marca'] ?? 'Sin marca'); ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($cliente_top): ?>
                                    <div class="recent-sale" style="border-left: 4px solid #007bff;">
                                        <strong>👑 Cliente VIP</strong>
                                        <span class="float-right badge badge-primary">
                                            <?php echo $cliente_top['total_compras']; ?> compras
                                        </span>
                                        <br><small><?php echo htmlspecialchars($cliente_top['nombres'] . ' ' . $cliente_top['apellido_paterno']); ?></small>
                                        <br><small>Total gastado: <?php echo MONEDA . number_format($cliente_top['total_gastado'], 2); ?></small>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($marca_top): ?>
                                    <div class="recent-sale" style="border-left: 4px solid #ffc107;">
                                        <strong>🏷️ Marca Más Popular</strong>
                                        <span class="float-right badge badge-warning">
                                            <?php echo $marca_top['total_ventas']; ?> ventas
                                        </span>
                                        <br><small><?php echo htmlspecialchars($marca_top['nombre_marca']); ?></small>
                                        <br><small>Productos vendidos en total</small>
                                    </div>
                                    <?php endif; ?>

                                    <div class="mt-3 text-center">
                                        <small class="text-muted">Total de Clientes Registrados: <strong><?php echo $total_clientes; ?></strong></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ventas Recientes -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">💰 Ventas Recientes</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($ventas_recientes as $venta): ?>
                                    <div class="recent-sale">
                                        <strong>Pedido #<?php echo $venta['id_venta']; ?></strong>
                                        <span class="float-right badge badge-<?php echo $venta['estado'] == 'Entregado' ? 'success' : 'warning'; ?>">
                                            <?php echo $venta['estado']; ?>
                                        </span>
                                        <br><small><?php echo htmlspecialchars($venta['nombres'] . ' ' . $venta['apellido_paterno']); ?></small>
                                        <br><small><?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?> | <?php echo MONEDA . number_format($venta['total_venta'], 2); ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Proveedores -->
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">🚚 Top Proveedores</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($top_proveedores)): ?>
                                        <?php foreach ($top_proveedores as $index => $proveedor): ?>
                                        <div class="recent-sale" style="border-left: 4px solid <?php echo ['#28a745', '#17a2b8', '#ffc107'][$index]; ?>;">
                                            <strong>
                                                <?php echo ['🥇', '🥈', '🥉'][$index]; ?> <?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>
                                            </strong>
                                            <span class="float-right badge badge-<?php echo ['success', 'info', 'warning'][$index]; ?>">
                                                <?php echo MONEDA . number_format($proveedor['total_ingresos'], 2); ?>
                                            </span>
                                            <br><small>Productos vendidos: <?php echo $proveedor['total_productos_vendidos']; ?></small>
                                            <br><small>Ingresos generados por este proveedor</small>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <p class="text-muted">No hay datos de proveedores disponibles</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Productos Más Rentables -->
                        <div class="col-lg-6 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">💰 Productos Más Rentables</h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($productos_rentables as $index => $producto): ?>
                                    <div class="recent-sale" style="border-left: 4px solid <?php echo ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#6f42c1'][$index]; ?>;">
                                        <strong><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 30)) . '...'; ?></strong>
                                        <span class="float-right text-success font-weight-bold">
                                            <?php echo MONEDA . number_format($producto['ingresos_totales'], 2); ?>
                                        </span>
                                        <br><small>Marca: <?php echo htmlspecialchars($producto['nombre_marca'] ?? 'Sin marca'); ?></small>
                                        <br><small>Vendidos: <?php echo $producto['cantidad_vendida']; ?> | Precio prom: <?php echo MONEDA . number_format($producto['precio_promedio'], 2); ?></small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <small>&copy; 2024 <b>Delgado Electronic</b> - Todos los Derechos Reservados.</small>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Scripts -->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    <script src="../vendor/chart.js/Chart.js"></script>

    <!-- Datos para gráficos -->
    <script>
        // Datos para gráficos
        const ventasMeses = <?php echo json_encode($ventas_meses); ?>;
        const topProductos = <?php echo json_encode($top_productos); ?>;
        const ventasPorDia = <?php echo json_encode($ventas_por_dia); ?>;
        const clientesAnalisis = <?php echo json_encode($clientes_analisis); ?>;
        const ventasPorHora = <?php echo json_encode($ventas_por_hora); ?>;
        
        function confirmarCerrarSesion(event) {
            event.preventDefault();
            Swal.fire({
                title: "¿Estás seguro?",
                text: "Tu sesión se cerrará y volverás al inicio.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Sí, cerrar sesión",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../php/cerrar_sesion.php";
                }
            });
        }

        // ⭐ NUEVO: Gráfico de ventas por hora
        var ctx4 = document.getElementById("ventasHoraChart");
        var ventasHoraChart = new Chart(ctx4, {
            type: 'line',
            data: {
                labels: Array.from({length: 24}, (_, i) => i + ':00'),
                datasets: [{
                    label: "Ventas por Hora",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 4,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: Array.from({length: 24}, (_, i) => {
                        const horaData = ventasPorHora.find(v => parseInt(v.hora) === i);
                        return horaData ? parseInt(horaData.total_ventas) : 0;
                    }),
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 12
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            min: 0
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            return 'Ventas: ' + tooltipItem.yLabel;
                        },
                        title: function(tooltipItem, chart) {
                            return 'Hora: ' + tooltipItem[0].xLabel;
                        }
                    }
                }
            }
        });

        // Gráfico de barras - Ventas por día de la semana
        var ctx3 = document.getElementById("myBarChart");
        var myBarChart = new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: ventasPorDia.map(item => {
                    const dias = {
                        'Monday': 'Lunes', 'Tuesday': 'Martes', 'Wednesday': 'Miércoles',
                        'Thursday': 'Jueves', 'Friday': 'Viernes', 'Saturday': 'Sábado', 'Sunday': 'Domingo'
                    };
                    return dias[item.dia_semana] || item.dia_semana;
                }),
                datasets: [{
                    label: "Ventas",
                    backgroundColor: "#4e73df",
                    hoverBackgroundColor: "#2e59d9",
                    borderColor: "#4e73df",
                    data: ventasPorDia.map(item => item.total_ventas),
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        },
                        maxBarThickness: 25,
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0,
                            maxTicksLimit: 5,
                            padding: 10,
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
            }
        });

        // Gráfico de clientes nuevos vs recurrentes
        var ctx5 = document.getElementById("clientesChart");
        var clientesChart = new Chart(ctx5, {
            type: 'doughnut',
            data: {
                labels: ['Clientes Nuevos', 'Clientes Recurrentes'],
                datasets: [{
                    data: [clientesAnalisis.clientes_nuevos || 0, clientesAnalisis.clientes_recurrentes || 0],
                    backgroundColor: ['#1cc88a', '#4e73df'],
                    hoverBackgroundColor: ['#17a673', '#2e59d9'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: false
                },
                cutoutPercentage: 80,
            },
        });
    </script>

    <!-- Gráficos personalizados -->
    <script>
        // Gráfico de área - Ventas mensuales
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Gráfico de líneas - Ventas mensuales
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ventasMeses.map(item => {
                    const [year, month] = item.mes.split('-');
                    const date = new Date(year, month - 1);
                    return date.toLocaleDateString('es-ES', { month: 'short', year: 'numeric' });
                }),
                datasets: [{
                    label: "Ventas",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: ventasMeses.map(item => parseFloat(item.total)),
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                return '<?php echo MONEDA; ?>' + number_format(value);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': <?php echo MONEDA; ?>' + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });

        // Gráfico circular - Top productos
        var ctx2 = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: topProductos.map(item => item.descripcion.substring(0, 20) + '...'),
                datasets: [{
                    data: topProductos.map(item => item.cantidad),
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf', '#f4b619', '#e02d1b'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 80,
            },
        });
    </script>
</body>
</html>
