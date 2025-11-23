<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    // Si hay una sesión iniciada, redirigir a cliente-page.php
    header("Location: ../index.php");
    exit(); // Asegurarse de que el script se detenga después de la redirección
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Location: ../Admin/admin-page.php");
        exit();
    }
}

require '../Config/config.php';
require '../php/database.php';
require '../php/GoogleAuthenticator.php';

$db = new Database();
$con = $db->conectar();

$mensaje_2fa = '';
$error_2fa = '';

// Manejar acciones de 2FA
if (isset($_POST['accion'])) {
    if ($_POST['accion'] === 'activar_2fa') {
        try {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = $ga->createSecret();
            
            // Guardar el secret en la base de datos
            $sql_2fa = $con->prepare("UPDATE usuarios SET secret = ? WHERE dni = ?");
            if ($sql_2fa->execute([$secret, $_SESSION['dni']])) {
                $mensaje_2fa = "2FA activado correctamente. Escanea el código QR con Google Authenticator.";
            } else {
                $error_2fa = "Error al activar 2FA. Inténtalo de nuevo.";
            }
        } catch (Exception $e) {
            $error_2fa = "Error al generar el código 2FA: " . $e->getMessage();
        }
    } elseif ($_POST['accion'] === 'desactivar_2fa') {
        $sql_2fa = $con->prepare("UPDATE usuarios SET secret = NULL WHERE dni = ?");
        if ($sql_2fa->execute([$_SESSION['dni']])) {
            $mensaje_2fa = "2FA desactivado correctamente.";
        } else {
            $error_2fa = "Error al desactivar 2FA. Inténtalo de nuevo.";
        }
    } elseif ($_POST['accion'] === 'actualizarUsuario') {
        // Preparar la consulta SQL de actualización
        $sql_update = $con->prepare("UPDATE usuarios SET nombres=?, apellido_paterno=?, apellido_materno=?, celular=?, correo=?, direccion=? WHERE dni=?");

        // Vincular parámetros y ejecutar la consulta
        $nombres = $_POST['nombres'];
        $apellido_paterno = $_POST['apellido_paterno'];
        $apellido_materno = $_POST['apellido_materno'];
        $celular = $_POST['celular'];
        $correo = $_POST['correo'];
        $direccion = $_POST['distrito']. ',' . $_POST['avenida']. ',' . $_POST['numero']. ',' . $_POST['descripcion'];
        $dni = $_POST['dni'];

        $sql_update->bindParam(1, $nombres, PDO::PARAM_STR);
        $sql_update->bindParam(2, $apellido_paterno, PDO::PARAM_STR);
        $sql_update->bindParam(3, $apellido_materno, PDO::PARAM_STR);
        $sql_update->bindParam(4, $celular, PDO::PARAM_INT);
        $sql_update->bindParam(5, $correo, PDO::PARAM_STR);
        $sql_update->bindParam(6, $direccion, PDO::PARAM_STR);
        $sql_update->bindParam(7, $dni, PDO::PARAM_INT);

        if ($sql_update->execute()) {
            echo "Usuario actualizado correctamente";
        } else {
            echo "Error al actualizar el usuario";
        }
        exit(); // Detener la ejecución del script después de manejar la solicitud de actualización
    }
}

// Consulta SQL para seleccionar los datos del usuario utilizando el DNI (incluyendo secret)
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

// Utilizamos la función explode() para dividir la cadena en subcadenas utilizando la coma como delimitador
$partesDireccion = explode(",", $direccion);

// Ahora, $partesDireccion es un array que contiene las partes de la dirección separadas
$distrito = $partesDireccion[0] ?? '';
$avenida = $partesDireccion[1] ?? '';
$numero = $partesDireccion[2] ?? '';
$descripcion = $partesDireccion[3] ?? '';

// Generar QR si hay secret
$qrCodeUrl = '';
if ($secret_actual) {
    try {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl(
            $correo, 
            $secret_actual, 
            'Tu Aplicación'
        );
    } catch (Exception $e) {
        $error_2fa = "Error al generar código QR: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de Usuario</title>
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/sb-admin-2.css" />
    <link rel="stylesheet" href="../css/chatbot.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../css/Footer.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
    
    <style>
        .security-card {
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            margin-top: 30px;
        }
        .security-header {
            background: linear-gradient(135deg, #5EBC50 0%, #4a9d42 100%);
            color: white;
            padding: 20px;
        }
        .qr-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-2fa {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        .btn-2fa:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .instruction-list {
            background: #fff;
            border-left: 4px solid #5EBC50;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .download-buttons {
            margin-top: 10px;
        }
        .download-buttons .btn {
            margin-right: 10px;
            margin-bottom: 5px;
        }
        .info-section {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body class="fondo" id="page-top">
    
    <!-- cabeceras -->
    <div class="content fixed-header">
        <!-- Primera cabecera-->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
            style="height: 35px; background-color: #5EBC50 !important;">
            <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
                <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                    <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 945853331
                </a>
            </div>
        </nav>

        <!-- Segunda Cabecera -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">

            <!-- Logo (visible solo en pantallas medianas y grandes) -->
            <img src="../images/logo-completo.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 75px; width: auto; margin-top: 10px">
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
                    <span id="num_cart" class="mr-2" style="margin-left: 0.5vh;"><?php echo $num_cart ?? 0; ?></span>
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
                    background-color: #5EBC50;
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

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Opciones
            </div>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="datos_usuario.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Datos Personales</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="cambio_contrasena.php">
                    <i class="fas fa-fw fa-key"></i>
                    <span>Seguridad</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="compras_cliente.php">
                    <i class="fas fa-fw fa-shopping-bag"></i>
                    <span>Mis Compras</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <!-- FORMULARIO DE DATOS PERSONALES -->
        <div class="container" style="margin-top:50px">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form id="actualizarUsuario">
                        <!-- Tu contenido del formulario aquí -->
                        <div class="row separador" style="text-align: center; margin-top: 20px !important;">
                            <div class="col-md-12 position-relative">
                                <h4><i class="fas fa-user-edit me-2"></i>Datos Personales</h4>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="dni">DNI</label>
                                <input type="text" class="form-control" id="dni" name="dni" value="<?php echo $_SESSION['dni']; ?>" readonly>
                                <span id="validaDni" class="text-danger"></span>
                            </div>
                            <div class="col-md-6 position-relative">
                                <label for="nombres">Nombres</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $nombres; ?>" required>
                                <span id="validaNombres" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="apellido_paterno">Apellido Paterno</label>
                                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo $apellido_paterno; ?>" required>
                                <span id="validaApellidoPaterno" class="text-danger"></span>
                            </div>
                            <div class="col-md-6 position-relative">
                                <label for="apellido_materno">Apellido Materno</label>
                                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo $apellido_materno; ?>" required>
                                <span id="validaApellidoMaterno" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="celular">Número de Celular</label>
                                <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $celular; ?>" required>
                                <span id="validaCelular" class="text-danger"></span>
                            </div>
                            <div class="col-md-6 position-relative">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" class="form-control  w-100" id="correo" name="correo" value="<?php echo $correo; ?>" readonly>
                                <span id="validaEmail" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador" style="text-align: center; margin-top: 20px !important;">
                            <div class="col-md-12 position-relative">
                                <h5><i class="fas fa-map-marker-alt me-2"></i>Dirección de Entrega</h5>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="distrito">Distrito</label>
                                <input type="text" class="form-control" id="distrito" name="distrito" value="<?php echo $distrito; ?>" required>
                                <span id="validaDistrito" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="avenida">Avenida</label><br>
                                <input type="text" class="form-control" id="avenida" name="avenida" value="<?php echo $avenida; ?>" required>
                                <span id="validaAvenida" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="numero">Numero</label>
                                <input type="text" class="form-control" id="numero" name="numero" value="<?php echo $numero; ?>" required>
                                <span id="validaNumero" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="descripcion">Dpto/Interior/Piso/Lote/Bloque</label><br>
                                <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>" required>
                                <span id="validaDescripcion" class="text-danger"></span>
                            </div>
                            <!-- Puedes agregar más campos aquí -->
                        </div>
                        <div class="text-center">
                            <button type="submit" class="button-register" onclick="return guardarCambios()">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2FA -->
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card security-card">
                        <div class="security-header">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                Autenticación en dos pasos (2FA)
                            </h5>
                            <p class="mb-0 mt-2 opacity-75">
                                Protege tu cuenta con una capa adicional de seguridad
                            </p>
                        </div>
                        <div class="card-body p-4">
                            
                            <!-- Mensajes de estado -->
                            <?php if ($mensaje_2fa): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $mensaje_2fa; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_2fa): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_2fa; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Estado actual -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1">Estado actual:</h6>
                                    <?php if ($secret_actual): ?>
                                        <span class="status-badge status-active">
                                            <i class="fas fa-check-circle me-1"></i>2FA Activado
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge status-inactive">
                                            <i class="fas fa-times-circle me-1"></i>2FA Desactivado
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($secret_actual): ?>
                                <!-- 2FA está activado -->
                                <div class="alert alert-success" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>¡Excelente!</strong> Tu cuenta está protegida con autenticación en dos pasos.
                                </div>
                                
                                <div class="qr-container">
                                    <h6 class="mb-3">
                                        <i class="fas fa-qrcode me-2"></i>Código QR para Google Authenticator
                                    </h6>
                                    <?php if ($qrCodeUrl): ?>
                                        <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px; border-radius: 10px;">
                                        <p class="text-muted small">
                                            <i class="fas fa-mobile-alt me-1"></i>
                                            Escanea este código si necesitas configurar un nuevo dispositivo
                                        </p>
                                    <?php else: ?>
                                        <p class="text-danger">Error al generar código QR</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="text-center">
                                    <button type="button" class="btn btn-danger btn-2fa" data-bs-toggle="modal" data-bs-target="#confirmDisableModal">
                                        <i class="fas fa-times me-2"></i>Desactivar 2FA
                                    </button>
                                </div>
                                
                            <?php else: ?>
                                <!-- 2FA no está activado -->
                                <div class="alert alert-warning" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Recomendado:</strong> Activa la autenticación en dos pasos para mayor seguridad.
                                </div>
                                
                                <div class="instruction-list">
                                    <h6 class="mb-3">
                                        <i class="fas fa-list-ol me-2"></i>¿Cómo activar 2FA?
                                    </h6>
                                    <ol class="mb-3">
                                        <li class="mb-2">
                                            <strong>Descarga Google Authenticator</strong> en tu teléfono móvil
                                            <div class="download-buttons">
                                                <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="btn btn-sm btn-outline-success">
                                                    <i class="fab fa-google-play me-1"></i>Android
                                                </a>
                                                <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fab fa-app-store me-1"></i>iOS
                                                </a>
                                            </div>
                                        </li>
                                        <li class="mb-2">Haz clic en <strong>"Activar 2FA"</strong> abajo</li>
                                        <li class="mb-2">Escanea el código QR que aparecerá con tu aplicación</li>
                                        <li>¡Listo! Tu cuenta estará protegida</li>
                                    </ol>
                                </div>
                                
                                <div class="text-center">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="accion" value="activar_2fa">
                                        <button type="submit" class="btn btn-success btn-2fa">
                                            <i class="fas fa-shield-alt me-2"></i>Activar 2FA
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Información adicional -->
                            <div class="info-section">
                                <h6 class="mb-2">
                                    <i class="fas fa-question-circle me-2"></i>¿Qué es la autenticación en dos pasos?
                                </h6>
                                <p class="text-muted small mb-0">
                                    Es una medida de seguridad que requiere dos formas de verificación: tu contraseña y un código temporal generado por tu teléfono. Esto hace que sea extremadamente difícil para alguien más acceder a tu cuenta, incluso si conocen tu contraseña.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para desactivar 2FA -->
    <div class="modal fade" id="confirmDisableModal" tabindex="-1" aria-labelledby="confirmDisableModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDisableModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmar desactivación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres desactivar la autenticación en dos pasos?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Advertencia:</strong> Tu cuenta será menos segura sin 2FA activado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="desactivar_2fa">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Sí, desactivar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    

    <footer class="pie-pagina">
    <div class="grupo-1">
        <div class="row">
            <!-- Primera Parte: Auxilium Farma (visible en pantallas grandes) -->
            <div class="col-11 col-md-3 d-none d-md-block">
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">La Empresa</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1"  >Quienes somos</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">Contactos</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1"  >Contáctenos</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
            </div>

            <!-- Segunda Parte: Inkafarma Digital (visible en pantallas grandes) -->
            <div class="col-11 col-md-3 d-none d-md-block">
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">Productos</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1" >Computación</a><br>
                                        <a target="_blank" class="paragraph--1" >Electronica</a><br>
                                        <a target="_blank" class="paragraph--1" >Electricidad</a><br>
                                        <a target="_blank" class="paragraph--1" >Ferretería</a><br>
                                        <a target="_blank" class="paragraph--1" >Redes y Telecomunicaciones</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
            </div>
            <!-- Tercera Parte: Contáctanos e Inkafono -->
            <div class="col-11 col-md-3 d-none d-md-block">
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">Servicio Técnico</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1" >Computadoras</a><br>
                                        <a target="_blank" class="paragraph--1" >Electrodomésticos</a><br>
                                        <a target="_blank" class="paragraph--1" >Plan de Referidos</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">Instalaciones</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1" >Redes y Telecomunicaciones</a><br>
                                        <a target="_blank" class="paragraph--1" >Eléctricas</a><br>
                                        <a target="_blank" class="paragraph--1" >Sistemas de seguridad</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
            </div>

            <!-- Cuarta Parte: Síguenos -->
<div class="col-11 col-md-3 d-none d-md-block">
    <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
        <mat-expansion-panel class="mat-expansion-panel">
            <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                <span class="mat-content">
                    <mat-panel-title class="mat-expansion-panel-header-title">
                        <h3 class="label-black title mb-lg-3">Proyectos</h3>
                    </mat-panel-title>
                </span>
            </mat-expansion-panel-header>
            <div class="mat-expansion-panel-content">
                <div class="mat-expansion-panel-body">
                    <mat-panel-description>
                        <div class="link">
                            <a target="_blank" class="paragraph--1" >Ingeniería Civil</a><br>
                            <a target="_blank" class="paragraph--1" >Ingeniería de Sistemas</a><br>
                        </div>
                    </mat-panel-description>
                </div>
            </div>
        </mat-expansion-panel>
    </mat-accordion>
    <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
        <mat-expansion-panel class="mat-expansion-panel">
            <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                <span class="mat-content">
                    <mat-panel-title class="mat-expansion-panel-header-title">
                        <h3 class="label-black title mb-lg-3">Asesoría</h3>
                    </mat-panel-title>
                </span>
            </mat-expansion-panel-header>
            <div class="mat-expansion-panel-content">
                <div class="mat-expansion-panel-body">
                    <mat-panel-description>
                        <div class="link">
                            <a target="_blank" class="paragraph--1" >Asesoría en Ing. de Sistemas</a><br>
                        </div>
                    </mat-panel-description>
                </div>
            </div>
        </mat-expansion-panel>
    </mat-accordion>
    <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
        <mat-expansion-panel class="mat-expansion-panel">
            <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                <span class="mat-content">
                    <mat-panel-title class="mat-expansion-panel-header-title">
                        <h3 class="label-black title mb-lg-3 text">Nuestras Redes</h3>
                    </mat-panel-title>
                </span>
            </mat-expansion-panel-header>
            <div class="mat-expansion-panel-content">
                <div class="mat-expansion-panel-body text-center">
                    <a target="_blank" class="social-item d-flex align-items-center" href="https://www.facebook.com/DelgadoElectronic/">
                        <i class="fab fa-facebook-f fa-2x mr-2"></i> Facebook
                    </a>
                    <a target="_blank" class="social-item d-flex align-items-center" href="https://wa.me/945853331?text=Hola,%20quiero%20obtener%20más%20información.">
                        <i class="fab fa-whatsapp fa-2x mr-2"></i> WhatsApp
                    </a>
                </div>
            </div>
        </mat-expansion-panel>
    </mat-accordion>
</div>

                <!-- Contenedor principal del acordeón -->
            <div id="accordionPadre">
                <!-- Sección de Auxilium Farma -->
                <div class="col-12 col-md-3 d-block d-md-none">
                    <div class="accordion" id="accordionSobreAuxilium">
                        <div class="card">
                            <div class="card-header" id="headingAuxilium">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseAuxilium" aria-expanded="true" aria-controls="collapseAuxilium">
                                        SOBRE AUXILIUM-FARMA
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseAuxilium" class="collapse" aria-labelledby="headingAuxilium" data-parent="#accordionPadre">
                                <div class="card-body">
                                    <a target="_blank" class="dropdown-item" >Catálogo del mes</a>
                                    <a target="_blank" class="dropdown-item" >Boticas 24 horas</a>
                                    <a target="_blank" class="dropdown-item" >Farmacia Vecina</a>
                                    <a target="_blank" class="dropdown-item" >Apoyo al Paciente</a>
                                    <a target="_blank" class="dropdown-item" >Productos Equivalentes</a>
                                    <a target="_blank" class="dropdown-item" >Derechos Arco</a>
                                    <a target="_blank" class="dropdown-item" >Intercorp y socios estratégicos</a>
                                    <a target="_blank" class="dropdown-item" >Call Center - Términos y Condiciones</a>
                                    <a target="_blank" class="dropdown-item" >Auxilium club</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- Sección de Inkafarma Digital -->
            <div class="col-12 col-md-3 d-block d-md-none">
                <div class="accordion" id="accordionInkafarmaDigital">
                    <div class="card">
                        <div class="card-header" id="headingInkafarmaDigital">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseInkafarmaDigital" aria-expanded="true" aria-controls="collapseInkafarmaDigital">
                                    AUXILIUM-FARMA DIGITAL
                                </button>
                            </h5>
                        </div>
                        <div id="collapseInkafarmaDigital" class="collapse" aria-labelledby="headingInkafarmaDigital" data-parent="#accordionPadre">
                            <div class="card-body">
                                <a target="_blank" class="dropdown-item"  >Blog Auxiliumfarma</a>
                                <a target="_blank" class="dropdown-item"  >Legales de Campañas</a>
                                <a target="_blank" class="dropdown-item"  >Retiro en Tienda</a>
                                <a target="_blank" class="dropdown-item"  >Servicio Express</a>
                                <a target="_blank" class="dropdown-item"  >Zonas de cobertura</a>
                                <a target="_blank" class="dropdown-item"  >Términos y Condiciones Generales</a>
                                <a target="_blank" class="dropdown-item"  >Políticas de privacidad</a>
                                <a target="_blank" class="dropdown-item"  >Comprobante electrónico</a>
                                <a target="_blank" class="dropdown-item"  >Terceros encargados de tratamiento</a>
                                <a target="_blank" class="dropdown-item"  >Términos y condiciones de otros sellers</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Contactanos -->
            <div class="col-12 col-md-3 d-block d-md-none">
                <div class="accordion" id="accordionContactanos">
                    <div class="card">
                        <div class="card-header" id="headingContactanos">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseContactanos" aria-expanded="true" aria-controls="collapseContactanos">
                                    CONTACTANOS
                                </button>
                            </h5>
                        </div>
                        <div id="collapseContactanos" class="collapse" aria-labelledby="headingContactanos" data-parent="#accordionPadre">
                            <div class="card-body">
                                <a target="_blank" class="dropdown-item"  >Preguntas Frecuentes</a>
                                <a target="_blank" class="dropdown-item"  >Información Médica</a>
                                <a target="_blank" class="dropdown-item"  >Plan de Referidos</a>
                                <div class="dropdown-item">
                                    <span class="font-weight-bold">Auxiliumfono (Lima)</span>
                                    <span>(511) 314 2020</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección de Síguenos -->
            <div class="col-12 col-md-3 d-block d-md-none">
                <div class="accordion" id="accordionSiguenos">
                    <div class="card">
                        <div class="card-header" id="headingSiguenos">
                            <h5 class="mb-0 text-center">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseSiguenos" aria-expanded="true" aria-controls="collapseSiguenos">
                                    SIGUENOS
                                </button>
                            </h5>
                        </div>
                        <div id="collapseSiguenos" class="collapse" aria-labelledby="headingSiguenos" data-parent="#accordionPadre">
                            <div class="card-body text-center">
                                <!-- Ícono de Facebook -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center" href="https://www.facebook.com/profile.php?id=61552772167929">
                                    <i class="fab fa-facebook-f fa-lg mr-2"></i> Facebook
                                </a>
                                <!-- Ícono de Instagram -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center" href="https://www.instagram.com/auxiliumfarma.oficial/?next=https%3A%2F%2Fwww.instagram.com%2F">
                                    <i class="fab fa-instagram fa-lg mr-2"></i> Instagram
                                </a>
                                <!-- Ícono de WhatsApp -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center" href="https://wa.me/993207538?text=Hola,%20quiero%20obtener%20más%20información.">
                                    <i class="fab fa-whatsapp fa-lg mr-2"></i> WhatsApp
                                </a>
                                <!-- Métodos de pago -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center">
                                    <i class="fa fa-credit-card fa-lg mr-2" aria-hidden="true"></i> Métodos de Pago
                                </a>
                                <!-- Medios de pago como Yape y Plin -->
                                <div class="mt-3">
                                    <h6>Medios de pago:</h6>
                                    <a class="dropdown-item">
                                        <img src="https://play-lh.googleusercontent.com/y5S3ZIz-ohg3FirlISnk3ca2yQ6cd825OpA0YK9qklc5W8MLSe0NEIEqoV-pZDvO0A8" alt="Yape" class="social-icon"> Yape
                                    </a>
                                    <a class="dropdown-item">
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQN94zsmpdqN7p2ugqBBrPygthpvfIsDB4QJA&s" alt="Plin" class="social-icon"> Plin
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Derechos reservados -->
        <div class="grupo-2 text-center">
            <small>&copy; 2025 <b>Delgado Electronic</b> - Todos los Derechos Reservados.</small>
        </div>
    </div>

</footer>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bibliotecas externas -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/efe6a408a5.js" crossorigin="anonymous"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Tus scripts personalizados -->
    <script src="../js/cliente.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    <script src="../js/chatbot-ocultacion.js"></script>
    <script src="../js/validacionLogin.js"></script>
    <script src="../js/validarRegistro.js"></script>
    <script src="../js/exit.js"></script>

</body>

</html>