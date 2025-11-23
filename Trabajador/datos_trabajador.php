<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['tipo'] !== 'vendedor') {
    header("Location: ../index.php");
    exit();
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
    } elseif ($_POST['accion'] === 'actualizarTrabajador') {
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
            echo "Trabajador actualizado correctamente";
        } else {
            echo "Error al actualizar el trabajador";
        }
        exit();
    }
}

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
            'Delgado Electronic - Trabajador'
        );
    } catch (Exception $e) {
        $error_2fa = "Error al generar código QR: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Trabajador - Datos Personales</title>
    
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .bg-custom-color { background-color: #4e73df; }
        .security-card {
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
            margin-top: 30px;
        }
        .security-header {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
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
            border-left: 4px solid #1cc88a;
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
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="pedidos.php">
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
            
            <li class="nav-item">
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
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-user mr-2"></i>Datos del Trabajador
                        </h1>
                    </div>

                    <!-- FORMULARIO DE DATOS PERSONALES -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-user-edit mr-2"></i>Información Personal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="actualizarTrabajador">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="dni" class="form-label">DNI</label>
                                                <input type="text" class="form-control" id="dni" name="dni" value="<?php echo $_SESSION['dni']; ?>" readonly>
                                                <span id="validaDni" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="nombres" class="form-label">Nombres</label>
                                                <input type="text" class="form-control" id="nombres" name="nombres" value="<?php echo $nombres; ?>" required>
                                                <span id="validaNombres" class="text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="apellido_paterno" class="form-label">Apellido Paterno</label>
                                                <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" value="<?php echo $apellido_paterno; ?>" required>
                                                <span id="validaApellidoPaterno" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                                <input type="text" class="form-control" id="apellido_materno" name="apellido_materno" value="<?php echo $apellido_materno; ?>" required>
                                                <span id="validaApellidoMaterno" class="text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="celular" class="form-label">Número de Celular</label>
                                                <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $celular; ?>" required>
                                                <span id="validaCelular" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="correo" class="form-label">Correo Electrónico</label>
                                                <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $correo; ?>" readonly>
                                                <span id="validaEmail" class="text-danger"></span>
                                            </div>
                                        </div>
                                        
                                        <hr class="my-4">
                                        <h6 class="mb-3">
                                            <i class="fas fa-map-marker-alt mr-2"></i>Dirección
                                        </h6>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="distrito" class="form-label">Distrito</label>
                                                <input type="text" class="form-control" id="distrito" name="distrito" value="<?php echo $distrito; ?>" required>
                                                <span id="validaDistrito" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="avenida" class="form-label">Avenida</label>
                                                <input type="text" class="form-control" id="avenida" name="avenida" value="<?php echo $avenida; ?>" required>
                                                <span id="validaAvenida" class="text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="numero" class="form-label">Número</label>
                                                <input type="text" class="form-control" id="numero" name="numero" value="<?php echo $numero; ?>" required>
                                                <span id="validaNumero" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="descripcion" class="form-label">Dpto/Interior/Piso/Lote/Bloque</label>
                                                <input type="text" class="form-control" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>" required>
                                                <span id="validaDescripcion" class="text-danger"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save mr-2"></i>Guardar Cambios
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2FA -->
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card security-card">
                                <div class="security-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-shield-alt mr-2"></i>
                                        Autenticación en dos pasos (2FA)
                                    </h5>
                                    <p class="mb-0 mt-2 opacity-75">
                                        Protege tu cuenta de trabajo con una capa adicional de seguridad
                                    </p>
                                </div>
                                <div class="card-body p-4">
                                    
                                    <!-- Mensajes de estado -->
                                    <?php if ($mensaje_2fa): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle mr-2"></i><?php echo $mensaje_2fa; ?>
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($error_2fa): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="fas fa-exclamation-triangle mr-2"></i><?php echo $error_2fa; ?>
                                            <button type="button" class="close" data-dismiss="alert">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Estado actual -->
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div>
                                            <h6 class="mb-1">Estado actual:</h6>
                                            <?php if ($secret_actual): ?>
                                                <span class="status-badge status-active">
                                                    <i class="fas fa-check-circle mr-1"></i>2FA Activado
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-inactive">
                                                    <i class="fas fa-times-circle mr-1"></i>2FA Desactivado
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($secret_actual): ?>
                                        <!-- 2FA está activado -->
                                        <div class="alert alert-success" role="alert">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            <strong>¡Excelente!</strong> Tu cuenta de trabajo está protegida con autenticación en dos pasos.
                                        </div>
                                        
                                        <div class="qr-container">
                                            <h6 class="mb-3">
                                                <i class="fas fa-qrcode mr-2"></i>Código QR para Google Authenticator
                                            </h6>
                                            <?php if ($qrCodeUrl): ?>
                                                <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid mb-3" style="max-width: 200px; border-radius: 10px;">
                                                <p class="text-muted small">
                                                    <i class="fas fa-mobile-alt mr-1"></i>
                                                    Escanea este código si necesitas configurar un nuevo dispositivo
                                                </p>
                                            <?php else: ?>
                                                <p class="text-danger">Error al generar código QR</p>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="text-center">
                                            <button type="button" class="btn btn-danger btn-2fa" data-toggle="modal" data-target="#confirmDisableModal">
                                                <i class="fas fa-times mr-2"></i>Desactivar 2FA
                                            </button>
                                        </div>
                                        
                                    <?php else: ?>
                                        <!-- 2FA no está activado -->
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            <strong>Recomendado:</strong> Activa la autenticación en dos pasos para proteger tu cuenta de trabajo.
                                        </div>
                                        
                                        <div class="instruction-list">
                                            <h6 class="mb-3">
                                                <i class="fas fa-list-ol mr-2"></i>¿Cómo activar 2FA?
                                            </h6>
                                            <ol class="mb-3">
                                                <li class="mb-2">
                                                    <strong>Descarga Google Authenticator</strong> en tu teléfono móvil
                                                    <div class="download-buttons">
                                                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2" target="_blank" class="btn btn-sm btn-outline-success">
                                                            <i class="fab fa-google-play mr-1"></i>Android
                                                        </a>
                                                        <a href="https://apps.apple.com/app/google-authenticator/id388497605" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fab fa-app-store mr-1"></i>iOS
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
                                                    <i class="fas fa-shield-alt mr-2"></i>Activar 2FA
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Información adicional -->
                                    <div class="info-section">
                                        <h6 class="mb-2">
                                            <i class="fas fa-briefcase mr-2"></i>Seguridad en el trabajo
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            Como trabajador, manejas información importante de pedidos y clientes. El 2FA te ayuda a proteger esta información y mantener la confianza de la empresa y los clientes.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación para desactivar 2FA -->
    <div class="modal fade" id="confirmDisableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                        Confirmar desactivación
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres desactivar la autenticación en dos pasos?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Advertencia:</strong> Tu cuenta será menos segura sin 2FA activado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="accion" value="desactivar_2fa">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times mr-2"></i>Sí, desactivar
                        </button>
                    </form>
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
        document.getElementById('actualizarTrabajador').addEventListener('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            formData.append('accion', 'actualizarTrabajador');
            
            fetch('datos_trabajador.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                Swal.fire({
                    title: 'Éxito',
                    text: 'Datos actualizados correctamente',
                    icon: 'success'
                }).then(() => {
                    location.reload();
                });
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al actualizar los datos',
                    icon: 'error'
                });
            });
        });
    </script>
</body>
</html>