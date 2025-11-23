<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Location: ../Admin/admin-page.php");
        exit();
    }
}

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

// Obtener ventas del cliente actual
$sql_venta = $con->prepare("SELECT id_venta, id_transaccion, fecha_venta, status, email, id_cliente, direccion_cliente, total_venta, estado FROM ventas WHERE id_cliente = :dni ORDER BY fecha_venta DESC");
$sql_venta->bindParam(':dni', $_SESSION['dni']);
$sql_venta->execute();
$resultado = $sql_venta->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos del usuario
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, correo, direccion FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

$nombres = $usuario['nombres'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Compras</title>
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/sb-admin-2.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
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

<body class="fondo" id="page-top">
    <!-- cabeceras -->
    <div class="content fixed-header">
        <!-- Primera cabecera-->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
            style="height: 35px; background-color: #C24096 !important;">
            <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
                <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                    <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 919285031
                </a>
            </div>
        </nav>

        <!-- Segunda Cabecera -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">

            <!-- Logo (visible solo en pantallas medianas y grandes) -->
            <img src="../images/logo-completo.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 38px; width: auto;">
            <!-- Logo (visible solo en pantallas celular) -->
            <img src="../images/Icons/logo-icono.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-sm-none" style="height: 50px; width: auto;">
            <!-- Apartado buscar -->
            <div class="form-container">
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="" method="post" autocomplete="off">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar..." aria-label="Search" aria-describedby="basic-addon2" name="campo" id="campo">
                        <div class="input-group-append">
                            <button class="btn bg-custom-color" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                    <ul id="lista" class="list-group"></ul>
                </form>
            </div>
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <!-- Nav Item - Search Redirect (Visible Only on Small Screens) -->
                <li class="nav-item d-sm-none">
                    <a class="nav-link" href="../buscar2.php">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                </li>
            </ul>

            <!-- Botón de Carrito de Compras -->
            <ul class="navbar-nav mx-auto carro-compras">
                <li class="nav-item">
                    <a href="carritodetalles_cliente.php">
                        <img src="../images/Icons/carro.png" loading="lazy"></a>
                    <span id="num_cart" class="mr-2" style="margin-left: 0.5vh;"><?php echo $num_cart; ?></span>
                </li>
            </ul>

            <ul class="navbar-nav mx-auto">
                <li class="nav-item dropdown position-relative">
                    <a class="nav-link d-flex align-items-center dropdown-toggle custom-user-dropdown" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../images/sesion_ini.png" alt="Usuario" class="user-icon" style="height: 30px; width: auto; margin-right: 15px;">
                        <span class="user-name">Bienvenido, <?php echo $nombres; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end position-absolute" aria-labelledby="usuarioDropdown">
                        <li><a class="dropdown-item custom-dropdown-item" href="datos_usuario.php">📝 Datos Personales</a></li>
                        <li><a class="dropdown-item custom-dropdown-item" href="compras_cliente.php">🛍️ Mis Compras</a></li>
                        <li><a class="dropdown-item custom-dropdown-item" id="cerrarSesionBtn" href="#">🚪 Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>
            <style>
                /* Mejor alineación del menú */
                .custom-dropdown-menu {
                    border-radius: 12px;
                    border: none;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
                    padding: 8px 0;
                    min-width: 180px;
                    position: absolute !important;
                    left: 50%;
                    transform: translateX(-50%);
                }

                /* Icono de usuario */
                .user-icon {
                    height: 32px;
                    width: auto;
                    margin-right: 8px;
                }

                /* Nombre del usuario */
                .user-name {
                    color: #333;
                    font-size: 14px;
                    font-weight: bold;
                }

                /* Quitar la flecha del dropdown */
                #usuarioDropdown::after {
                    display: none !important;
                }

                /* Estilo para las opciones del menú */
                .custom-dropdown-item {
                    font-size: 14px;
                    color: #333;
                    font-weight: 500;
                    padding: 10px 15px;
                    transition: background 0.3s ease, color 0.3s ease;
                }

                .custom-dropdown-item:hover {
                    background-color: #d63384;
                    color: white;
                    border-radius: 8px;
                }

                /* 🌐 Estilos Responsivos: Ocultar el nombre en pantallas menores a 950px */
                @media (max-width: 950px) {
                    .user-name {
                        display: none !important;
                  }
                }
            </style>
        </nav>
    </div>

    <!-- Page Wrapper -->
    <div id="wrapper" style="margin-top: 100px;">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-custom-color sidebar sidebar-dark accordion" id="accordionSidebar">
            <hr class="sidebar-divider">
            <div class="sidebar-heading">Opciones</div>
            
            <li class="nav-item">
                <a class="nav-link" href="datos_usuario.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Datos Personales</span>
                </a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="cambio_contrasena.php">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Seguridad</span></a>
            </li>
            
            <li class="nav-item active">
                <a class="nav-link" href="compras_cliente.php">
                    <i class="fas fa-fw fa-shopping-bag"></i>
                    <span>Mis Compras</span>
                </a>
            </li>
            
            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- Content -->
        <div class="container-fluid estiloLetra">
            <h2><i class="fas fa-shopping-bag me-2"></i>Mis Compras</h2>

            <?php if (empty($resultado)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                    <h5>No tienes compras registradas</h5>
                    <p>Cuando realices tu primera compra, aparecerá aquí.</p>
                    <a href="cliente-page.php" class="btn btn-primary">
                        <i class="fas fa-shopping-cart me-1"></i> Ir a Comprar
                    </a>
                </div>
            <?php else: ?>
                <div class="row" id="pedidos-container" style="margin-top: 4vh;">
                    <?php foreach ($resultado as $venta) : ?>
                        <div class="col-lg-12 col-md-12 col-sm-12 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-0">Pedido #<?php echo $venta['id_venta']; ?></h5>
                                        <small class="text-muted">Transacción: <?php echo $venta['id_transaccion']; ?></small>
                                    </div>
                                    <div>
                                        <?php
                                        $badge_class = '';
                                        $icon = '';
                                        switch($venta['estado']) {
                                            case 'Pendiente':
                                                $badge_class = 'bg-warning text-dark';
                                                $icon = 'fas fa-clock';
                                                break;
                                            case 'Entregado':
                                                $badge_class = 'bg-success';
                                                $icon = 'fas fa-check-circle';
                                                break;
                                            default:
                                                $badge_class = 'bg-secondary';
                                                $icon = 'fas fa-question';
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <i class="<?php echo $icon; ?> me-1"></i><?php echo $venta['estado']; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Fecha de compra:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha_venta'])); ?></p>
                                            <p class="mb-1"><strong>Estado:</strong> <?php echo $venta['estado']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Total pagado:</strong> <span class="text-success fw-bold"><?php echo MONEDA . number_format($venta['total_venta'], 2); ?></span></p>
                                            <p class="mb-1"><strong>Dirección de entrega:</strong> <?php echo $venta['direccion_cliente']; ?></p>
                                        </div>
                                    </div>

                                    <hr>

                                    <!-- Detalles de la venta CON MARCA -->
                                    <h6><i class="fas fa-list-ul me-2"></i>Productos Comprados</h6>
                                    
                                    <?php
                                    $sql_detalles_venta = $con->prepare("SELECT dv.*, p.descripcion as producto_descripcion, m.nombre_marca 
                                                                         FROM detalles_ventas dv 
                                                                         LEFT JOIN productos p ON dv.id_producto = p.codigo 
                                                                         LEFT JOIN marcas m ON p.id_marca = m.id_marca 
                                                                         WHERE dv.id_venta = :id_venta");
                                    $sql_detalles_venta->bindParam(':id_venta', $venta['id_venta']);
                                    $sql_detalles_venta->execute();
                                    $detalles_venta = $sql_detalles_venta->fetchAll(PDO::FETCH_ASSOC);
                                    ?>

                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Producto</th>
                                                    <th>Marca</th>
                                                    <th>Precio</th>
                                                    <th>Cantidad</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($detalles_venta as $detalle) : ?>
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

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <?php if ($venta['estado'] == 'Pendiente'): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle me-1"></i>
                                                    Tu pedido está siendo procesado y será enviado pronto.
                                                </small>
                                            <?php elseif ($venta['estado'] == 'Entregado'): ?>
                                                <small class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Pedido entregado exitosamente.
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                                                <i class="fas fa-print me-1"></i> Imprimir
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

        <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/cliente.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    <script src="../js/chatbot-ocultacion.js"></script>
    <script src="../js/validacionLogin.js"></script>
    <script src="../js/validarRegistro.js"></script>
    <script src="../js/exit.js"></script>
</body>
</html>
