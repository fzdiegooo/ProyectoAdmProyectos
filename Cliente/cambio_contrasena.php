<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    // Si hay una sesión iniciada, redirigir a cliente-page.php
    header("Location: ../landing-page.php");
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

$db = new Database();
$con = $db->conectar();

// Verificar si se envió el formulario de actualización de producto
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizarPassword') {

    // Preparar la consulta SQL de actualización
    $sql_update = $con->prepare("UPDATE usuarios SET contrasena=? WHERE dni=?");

    // Vincular parámetros y ejecutar la consulta
    $contrasena = $_POST['confirmar_contrasena'];
    $contrasena_encriptada = hash('sha512', $contrasena);
    $dni = $_SESSION['dni'];

    $sql_update->bindParam(1, $contrasena_encriptada, PDO::PARAM_STR);
    $sql_update->bindParam(2, $dni, PDO::PARAM_INT);


    if ($sql_update->execute()) {
        echo "Contraseña actualizada correctamente";
    } else {
        echo "Error al actualizar el Contraseña";
    }
    exit(); // Detener la ejecución del script después de manejar la solicitud de actualización
}

// Consulta SQL para seleccionar los datos del usuario utilizando el DNI
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, direccion FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

// Asignar los datos del usuario a variables individuales
$nombres = $usuario['nombres'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/sb-admin-2.css" />
    <link rel="stylesheet" href="../css/chatbot.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <link rel="stylesheet" href="../css/Footer.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
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

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Opciones
            </div>

            <!-- Nav Item - Charts -->
            <li class="nav-item">
                <a class="nav-link" href="datos_usuario.php">
                    <i class="fas fa-fw fa-chart-area"></i>
                    <span>Datos Personales</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="cambio_contrasena.php">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Seguridad</span></a>
            </li>

            <!-- Nav Item - Tables -->
            <li class="nav-item">
                <a class="nav-link" href="compras_cliente.php">
                    <i class="fas fa-fw fa-table"></i>
                    <span>Mis Compras</span></a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div class="container" style="margin-top:50px">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form id="actualizarPassword">
                        <!-- Tu contenido del formulario aquí -->
                        <div class="row separador" style="text-align: center; margin-top: 20px !important;">
                            <div class="col-md-12 position-relative">
                                <label for="">Cambiar Contraseña</label>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="password">Antigua Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" value="<?php #echo $_SESSION['contrasena_sin_encriptar'];
                                                                                                                    ?>" required>
                                <i class="fa-solid fa-eye-slash" id="ocultarPassword"></i>
                                <i class="fa-solid fa-eye" id="verPassword" style="display: none;"></i>
                            </div>
                            <span id="lastPasswordError" class="text-danger"></span>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="confirmar_contrasena">Confirmar Nueva Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_contrasena" name="confirmar_contrasena" required>
                                <div class="password-icons">
                                    <i class="fa-solid fa-eye" id="mostIcon" style="display: none;"></i>
                                    <i class="fa-solid fa-eye-slash" id="oculIcon"></i>
                                </div>
                                <span id="confirmarContrasenaError" class="text-danger"></span>
                            </div>
                        </div>
                        <button type="submit" class="button-register" onclick="return cambiarContrasena()">Cambiar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="oferta">
        <img src="../images/oferta4.jpg" class="img-oferta" loading="lazy" style="margin-top:-50px">
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
                                    <h3 class="label-black title mb-lg-3">Sobre Auxilium Farma</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1"  >Catálogo del mes</a><br>
                                        <a target="_blank" class="paragraph--1"  >Boticas 24 horas</a><br>
                                        <a target="_blank" class="paragraph--1"  >Farmacia Vecina</a><br>
                                        <a target="_blank" class="paragraph--1"  >Apoyo al Paciente</a><br>
                                        <a target="_blank" class="paragraph--1"  >Productos Equivalentes</a><br>
                                        <a target="_blank" class="paragraph--1"  >Derechos Arco</a><br>
                                        <a target="_blank" class="paragraph--1"  >Intercorp y socios estratégicos</a><br>
                                        <a target="_blank" class="paragraph--1"  >WhatsApp - Términos y Condiciones</a><br>
                                        <a target="_blank" class="paragraph--1"  >Call Center - Términos y Condiciones</a><br>
                                        <a target="_blank" class="paragraph--1"  >AuxiliumClub</a><br>
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
                                    <h3 class="label-black title mb-lg-3">Auxilium-Farma Digital</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1" >Blog Auxilium-Farma</a><br>
                                        <a target="_blank" class="paragraph--1" >Legales de Campañas</a><br>
                                        <a target="_blank" class="paragraph--1" >Retiro en Tienda</a><br>
                                        <a target="_blank" class="paragraph--1" >Servicio Express</a><br>
                                        <a target="_blank" class="paragraph--1" >Zonas de cobertura</a><br>
                                        <a target="_blank" class="paragraph--1" >Términos y Condiciones Generales</a><br>
                                        <a target="_blank" class="paragraph--1" >Políticas de privacidad</a><br>
                                        <a target="_blank" class="paragraph--1" >Comprobante electrónico</a><br>
                                        <a target="_blank" class="paragraph--1" >Terceros encargados de tratamiento</a><br>
                                        <a target="_blank" class="paragraph--1" >Términos y condiciones de otros sellers</a><br>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
            </div>
            <!-- Tercera Parte: Contáctanos e Inkafono -->
            <div class="col-11 col-md-3 d-none d-md-block">
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3">Contáctanos</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body">
                                <mat-panel-description>
                                    <div class="link">
                                        <a target="_blank" class="paragraph--1" >Preguntas Frecuentes</a><br>
                                        <a target="_blank" class="paragraph--1" >Información Médica</a><br>
                                        <a target="_blank" class="paragraph--1" >Plan de Referidos</a><br>
                                        <div class="paragraph--1">
                                            <span class="font-weight-bold">Auxiliumfono (Lima)</span>
                                            <span>(511) 314 2020</span>
                                        </div>
                                    </div>
                                </mat-panel-description>
                            </div>
                        </div>
                    </mat-expansion-panel>
                </mat-accordion>
            </div>

            <!-- Cuarta Parte: Síguenos -->
            <div class="col-11 col-md-3 d-none d-md-block">
                <mat-accordion displaymode="flat" class="mat-accordion row footer mat-accordion-multi">
                    <mat-expansion-panel class="mat-expansion-panel">
                        <mat-expansion-panel-header role="button" class="mat-expansion-panel-header mat-focus-indicator mat-expansion-toggle-indicator-after">
                            <span class="mat-content">
                                <mat-panel-title class="mat-expansion-panel-header-title">
                                    <h3 class="label-black title mb-lg-3 text">Síguenos</h3>
                                </mat-panel-title>
                            </span>
                        </mat-expansion-panel-header>
                        <div class="mat-expansion-panel-content">
                            <div class="mat-expansion-panel-body text-center">
                                <a target="_blank" class="social-item d-flex align-items-center" href="https://www.facebook.com/profile.php?id=61552772167929">
                                    <i class="fab fa-facebook-f fa-2x mr-2"></i> Facebook
                                </a>
                                <a target="_blank" class="social-item d-flex align-items-center" href="https://wa.me/993207538?text=Hola,%20quiero%20obtener%20más%20información.">
                                    <i class="fab fa-whatsapp fa-2x mr-2"></i> WhatsApp
                                </a>
                                <a target="_blank" class="social-item d-flex align-items-center" href="https://www.instagram.com/auxiliumfarma.oficial/?next=https%3A%2F%2Fwww.instagram.com%2F">
                                    <i class="fab fa-instagram fa-2x mr-2"></i> Instagram
                                </a>
                                    <a target="_blank" class="social-item d-flex align-items-center" href="../libro_reclamaciones.php">
                                        <i class="fa fa-book fa-2x mr-2" aria-hidden="true"></i> 
                                        <div>
                                            <span>Libro de</span><br>
                                            <span>Reclamaciones</span>
                                        </div>
                                    </a>
                                <div class="social-item d-flex align-items-center">
                                    <i class="fa fa-credit-card fa-2x mr-2"></i> Métodos de Pago
                                </div>
                                <!-- Contenedor Flex para Yape y Plin juntos -->
                                <div class="d-flex justify-content-start">
                                    <a target="_blank" class="social-item d-flex align-items-center"  style="margin-right: 15px;">
                                        <img src="https://play-lh.googleusercontent.com/y5S3ZIz-ohg3FirlISnk3ca2yQ6cd825OpA0YK9qklc5W8MLSe0NEIEqoV-pZDvO0A8" alt="Yape" style="width: 30px; height: 30px; margin-right: 10px;">
                                        <span>Yape</span>
                                    </a>
                                    <a target="_blank" class="social-item d-flex align-items-center" >
                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQN94zsmpdqN7p2ugqBBrPygthpvfIsDB4QJA&s" alt="Plin" style="width: 30px; height: 30px; margin-right: 10px;">
                                        <span>Plin</span>
                                    </a>
                                </div>
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
                                    <a target="_blank" class="dropdown-item" >Catálogo del mes</a>
                                    <a target="_blank" class="dropdown-item" >Boticas 24 horas</a>
                                    <a target="_blank" class="dropdown-item" >Farmacia Vecina</a>
                                    <a target="_blank" class="dropdown-item" >Apoyo al Paciente</a>
                                    <a target="_blank" class="dropdown-item" >Productos Equivalentes</a>
                                    <a target="_blank" class="dropdown-item" >Derechos Arco</a>
                                    <a target="_blank" class="dropdown-item" >Intercorp y socios estratégicos</a>
                                    <a target="_blank" class="dropdown-item" >Call Center - Términos y Condiciones</a>
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
                                <a target="_blank" class="dropdown-item"  >Legales de Campañas</a>
                                <a target="_blank" class="dropdown-item"  >Retiro en Tienda</a>
                                <a target="_blank" class="dropdown-item"  >Servicio Express</a>
                                <a target="_blank" class="dropdown-item"  >Zonas de cobertura</a>
                                <a target="_blank" class="dropdown-item"  >Términos y Condiciones Generales</a>
                                <a target="_blank" class="dropdown-item"  >Políticas de privacidad</a>
                                <a target="_blank" class="dropdown-item"  >Comprobante electrónico</a>
                                <a target="_blank" class="dropdown-item"  >Terceros encargados de tratamiento</a>
                                <a target="_blank" class="dropdown-item"  >Términos y condiciones de otros sellers</a>
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
                                <a target="_blank" class="dropdown-item"  >Información Médica</a>
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
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center" href="https://wa.me/993207538?text=Hola,%20quiero%20obtener%20más%20información.">
                                    <i class="fab fa-whatsapp fa-lg mr-2"></i> WhatsApp
                                </a>
                                <!-- Libro de Reclamaciones -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center" href="../libro_reclamaciones.php"  >
                                    <i class="fa fa-book fa-lg mr-2"></i> 
                                    <div>
                                        <span>Libro de</span><br>
                                        <span>Reclamaciones</span>
                                    </div>
                                </a>
                                <!-- Métodos de pago -->
                                <a target="_blank" class="dropdown-item d-flex align-items-center justify-content-center">
                                    <i class="fa fa-credit-card fa-lg mr-2" aria-hidden="true"></i> Métodos de Pago
                                </a>
                                <!-- Medios de pago como Yape y Plin -->
                                <div class="mt-3">
                                    <h6>Medios de pago:</h6>
                                    <a   class="dropdown-item">
                                        <img src="https://play-lh.googleusercontent.com/y5S3ZIz-ohg3FirlISnk3ca2yQ6cd825OpA0YK9qklc5W8MLSe0NEIEqoV-pZDvO0A8" alt="Yape" class="social-icon"> Yape
                                    </a>
                                    <a   class="dropdown-item">
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
            <small>&copy; 2024 <b>Auxilium Farma</b> - Todos los Derechos Reservados.</small>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Tus scripts personalizados -->
    <script src="../js/cliente.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    <script src="../js/exit.js"></script>
    <script>
        // Obtener referencias a los elementos de contraseña y los íconos de ojo
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmar_contrasena');
        const showPasswordIcon = document.getElementById('ocultarPassword');
        const hidePasswordIcon = document.getElementById('verPassword');
        const showconfirmPasswordInput = document.getElementById('oculIcon');
        const hideconfirmPasswordInput = document.getElementById('mostIcon');

        // Función para mostrar la contraseña
        function mostrarContrasena() {
            // Cambiar el tipo de input a 'text' para mostrar la contraseña
            passwordInput.type = 'text';
            // Ocultar el ícono de mostrar contraseña y mostrar el ícono de ocultar contraseña
            showPasswordIcon.style.display = 'none';
            hidePasswordIcon.style.display = 'inline-block';
        }

        // Función para ocultar la contraseña
        function ocultarContrasena() {
            // Cambiar el tipo de input a 'password' para ocultar la contraseña
            passwordInput.type = 'password';
            // Ocultar el ícono de ocultar contraseña y mostrar el ícono de mostrar contraseña
            showPasswordIcon.style.display = 'inline-block';
            hidePasswordIcon.style.display = 'none';
        }

        // Función para mostrar la contraseña
        function mostrarConfirmarContrasena() {
            // Cambiar el tipo de input a 'text' para mostrar la contraseña
            confirmPasswordInput.type = 'text';
            // Ocultar el ícono de mostrar contraseña y mostrar el ícono de ocultar contraseña
            showconfirmPasswordInput.style.display = 'none';
            hideconfirmPasswordInput.style.display = 'inline-block';
        }

        // Función para ocultar la contraseña
        function ocultarConfirmarContrasena() {
            // Cambiar el tipo de input a 'password' para ocultar la contraseña
            confirmPasswordInput.type = 'password';
            // Ocultar el ícono de ocultar contraseña y mostrar el ícono de mostrar contraseña
            showconfirmPasswordInput.style.display = 'inline-block';
            hideconfirmPasswordInput.style.display = 'none';
        }

        // Agregar eventos clic a los íconos de ojo para alternar la visibilidad de la contraseña
        showPasswordIcon.addEventListener('click', mostrarContrasena);
        hidePasswordIcon.addEventListener('click', ocultarContrasena);

        showconfirmPasswordInput.addEventListener('click', mostrarConfirmarContrasena);
        hideconfirmPasswordInput.addEventListener('click', ocultarConfirmarContrasena);
    </script>

    <script>
        //VALIDA CONTRASEÑA QUE NO SEA LA MISMA DE ANTES
        function validarContrasenaActual(input, errorElement) {
            const inputPassword = input.value.trim();
            const storedPassword = '<?php echo $_SESSION['contrasena_sin_encriptar']; ?>';

            if (inputPassword !== storedPassword) {
                errorElement.textContent = 'Las contraseñas no coinciden.';
            } else {
                errorElement.textContent = '';
            }
        }

        function validarPassword() {
            validarContrasenaActual(passwordInput2, lastPasswordError);
        }

        const passwordInput2 = document.getElementById('password');
        const lastPasswordError = document.getElementById('lastPasswordError');

        passwordInput2.addEventListener('blur', validarPassword);
    </script>

    <script>
        function validarContrasena(input, errorElement) {
            const nuevaContrasena = input.value.trim();
            const contrasenaAnterior = document.getElementById('password').value.trim();

            if (nuevaContrasena === contrasenaAnterior) {
                errorElement.textContent = 'Las contraseñas no deben ser iguales.';
            } else {
                errorElement.textContent = '';
            }
        }

        function validarConfirmarContrasena() {
            validarContrasena(confirmarContrasenaInput, confirmarContrasenaError);
        }

        const confirmarContrasenaInput = document.getElementById('confirmar_contrasena');
        const confirmarContrasenaError = document.getElementById('confirmarContrasenaError');

        confirmarContrasenaInput.addEventListener('blur', validarConfirmarContrasena);
    </script>

    <script>
        //---------------------------------------------------------------------------------------------------------------
        /Funcion para guardar los datos CONTRASEÑA en la base/
        $(document).ready(function() {
            // Manejar el evento de envío del formulario de edición
            $('#actualizarPassword').submit(function(e) {
                // Detener el envío normal del formulario
                e.preventDefault();

                // Obtener los datos del formulario
                var formData = $(this).serialize() + '&accion=actualizarPassword'; // Añadir un parámetro para indicar que es una actualización

                // Realizar una petición AJAX para enviar los datos al servidor
                $.ajax({
                    type: 'POST',
                    url: 'cambio_contrasena.php', // Archivo PHP que procesará la actualización
                    data: formData,
                    success: function(response) {
                        // Manejar la respuesta del servidor
                        alert(response); // Puedes mostrar un mensaje de éxito o hacer otras acciones
                        // Actualizar la página o realizar otras acciones si es necesario
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Manejar los errores en caso de que ocurran
                        alert("Error al actualizar el contraseña: " + error);
                    }
                });
            });
        });
    </script>

</body>

</html>