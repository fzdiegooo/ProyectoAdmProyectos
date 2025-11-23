<?php
//Se llaman lo archivos de config y base de datos
require '../Config/config.php';
require '../php/database.php';
require '../php/clientesfunciones.php';
// Crear una instancia de Database y conectar a la base de datos
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

// Editar Usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'editarU') {
    $dni = trim($_POST["editDniU"]);
    $nombre = trim($_POST["editNombre"]);
    $apellido_paterno = trim($_POST["editApellidoPaterno"]);
    $apellido_materno = trim($_POST["editApellidoMaterno"]);
    $celular= trim($_POST["editCelular"]);
    $fecha_nacimiento = trim($_POST["editFechaNacimiento"]);
    $correo = trim($_POST["editCorreo"]);
    $genero = trim($_POST["editGenero"]);
    $direccion = trim($_POST["editDireccion"]);
    $rol = trim($_POST["editRol"]);

    // Preparar la consulta SQL
    $sql = "UPDATE usuarios SET 
                nombres = ?, 
                apellido_paterno = ?, 
                apellido_materno = ?, 
                celular = ?, 
                fecha_nacimiento = ?, 
                correo = ?, 
                genero = ?, 
                direccion = ? 
                rol = ?
            WHERE dni = ?";

    $stmt = $con->prepare($sql);
    $resultado = $stmt->execute([
        $nombre, $apellido_paterno, $apellido_materno, $celular, 
        $fecha_nacimiento, $correo, $genero, $direccion, $dni, $rol
    ]);

    if ($resultado) {
        echo json_encode(["status" => "success", "message" => "Usuario actualizado correctamente"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el Usuario"]);
        exit();
    }
}

// Cambiar Estado Usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dni']) && isset($_POST['estado'])) {
    $dni = $_POST['dni'];
    $nuevoEstado = ($_POST['estado'] == 1) ? 0 : 1; // Alternar estado

    $sql = $con->prepare("UPDATE usuarios SET estado = ? WHERE dni = ?");
    $resultado = $sql->execute([$nuevoEstado, $dni]);

    echo json_encode(["success" => $resultado, "nuevoEstado" => $nuevoEstado]);
    exit;
}

// Agregar Usuario
if (isset($_POST['accion']) && $_POST['accion'] === 'agregarU') {

    $dni = trim($_POST["dni_U"]);
    $nombre = trim($_POST["nombres_U"]);
    $apellido_paterno = trim($_POST["apellido_paterno_U"]);
    $apellido_materno = trim($_POST["apellido_materno_U"]);
    $celular = trim($_POST["celular_U"]);
    $fecha_nacimiento = trim($_POST["fecha_nacimiento_U"]);
    $correo = trim($_POST["email_U"]);
    $contrasena = trim($_POST["password_U"]);
    $genero = trim($_POST["genero_U"]);
    $direccion = trim($_POST["direccion_U"]);
    $contrasena_encriptada = password_hash($contrasena, PASSWORD_DEFAULT);
    $token = generarToken();

    header('Content-Type: application/json');

    $sql = $con->prepare("INSERT INTO usuarios (dni, nombres, apellido_paterno, apellido_materno, celular, fecha_nacimiento, 
    correo, contrasena, genero, direccion, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $resultado = $sql->execute([$dni, $nombre, $apellido_paterno, $apellido_materno, $celular, $fecha_nacimiento, $correo, $contrasena_encriptada, $genero, $direccion, $token]);


    if ($resultado) {
        echo json_encode(["status" => "success", "message" => "Usuario agregado correctamente"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error al agregar el usuario"]);
        exit();
    }
}

//Se crea la consulta
$sql = $con->prepare("SELECT dni, nombres, apellido_paterno, apellido_materno, celular, fecha_nacimiento, 
correo, contrasena, genero, direccion, estado FROM usuarios");
//Se ejecuta la consulta
$sql->execute();
//Recupera los resultados de la consulta
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Admin - Dashboard</title>

    <!-- fuentes-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!--estilo-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <!-- libreria que permite establecer ventanas emergentes -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>        
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

    <!-- Page Wrapper - contenedor principal-->
    <div id="wrapper">

        <!-- Sidebar - barra lateral izquierdo-->
        <ul class="navbar-nav bg-custom-color sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Admin - Sidebar -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin-page.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class=""></i>
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
        <!-- End of Sidebar -->

        <!--Contenido gestion - footer -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Contenido gestion - principal -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!--Boton desplegable: oculta el sidebar-->
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn bg-custom-color d-md-none rounded-circle mr-3">
                        <!--icono-->
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Search -->
                    <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Buscar..." aria-label="Search" aria-describedby="basic-addon2">
                            <div class="input-group-append">
                                <button class="btn bg-custom-color" type="button">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Topbar Navbar -->
                                        
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
                            <script>
                                //Función que habilita la ventana emergente para decidir si queremos cerrar sesión o no
                                function confirmarCerrarSesion(event) {
                                    event.preventDefault(); // Evita que el enlace redirija de inmediato

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
                                            window.location.href = "../php/cerrar_sesion.php"; // Redirige si confirma
                                        }
                                    });
                                }
                            </script>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Contenedor de información-->
                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Encabezado - Generate Report -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"></h1><!--No se visualiza -->
                        <a href="#" id="exportUsuarios" class="btn btn-sm bg-custom-color shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte de Usuarios
                        </a>
                    </div>


                    <!-- Contenido de Usuarios -->
                    <div class="container-fluid">
                        <h2>Listado de Usuarios</h2>

                       <!-- Botón Agregar Usuario-->
                        <div class="mb-3">
                            <button type="button" class="btn btn-success me-3" data-bs-toggle="modal" data-bs-target="#registerModal">
                                Agregar Usuario
                            </button>
                        </div>


                    <!-- Modal Agregar Usuario -->
                    <div id="registerModal" class="modal fade"  tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="registerModalLabel">Agregar nuevo Usuario</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body">
                                    <!-- Formulario agregar Usuario -->
                                    <form id="registerForm2">
                                    
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="dni_U">DNI</label>
                                                <input type="text" class="form-control" id="dni_U" name="dni_U" placeholder="Ingrese su DNI..." required>
                                                <span id="validaDni_U" class="text-danger"></span>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="nombres_U">Nombres</label>
                                                <input type="text" class="form-control" id="nombres_U" name="nombres_U" placeholder="Ingrese sus nombres..." required>
                                                <span id="validaNombres_U" class="text-danger"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="apellido_paterno_U">Apellido Paterno</label>
                                                <input type="text" class="form-control" id="apellido_paterno_U" name="apellido_paterno_U" placeholder="Ingrese su apellido paterno..." required>
                                                <span id="validaApellidoPaterno_U" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="apellido_materno_U">Apellido Materno</label>
                                                <input type="text" class="form-control" id="apellido_materno_U" name="apellido_materno_U" placeholder="Ingrese su apellido materno..." required>
                                                <span id="validaApellidoMaterno_U" class="text-danger"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="celular_U">Número de Celular</label>
                                                <input type="text" class="form-control" id="celular_U" name="celular_U" placeholder="Ingrese su número de celular..." pattern="9[0-9]{8}" maxlength="9" required>
                                                <span id="validaCelular_U" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="fecha_nacimiento_U">Fecha de Nacimiento</label>
                                                <input type="date" class="form-control" id="fecha_nacimiento_U" name="fecha_nacimiento_U" required>
                                                <span id="validaFechadenacimiento_U" class="text-danger"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="email_U">Correo Electrónico</label>
                                                <input type="email" class="form-control" id="email_U" name="email_U" placeholder="Ingrese su correo electrónico..." required>
                                                <span id="validaEmail_U" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="genero_U">Género</label>
                                                <select class="form-control" id="genero_U" name="genero_U" required>
                                                    <option value="" disabled selected>Seleccionar Género</option>
                                                    <option value="Masculino">Masculino</option>
                                                    <option value="Femenino">Femenino</option>
                                                    <option value="Otro">Otro</option>
                                                </select>                                           
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label for="direccion_U">Dirección:</label>
                                                <input type="text" class="form-control" id="direccion_U" name="direccion_U" required>
                                                <span id="errorAgreDireccion_U" class="texto-error-agre_U text-danger"></span>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="password_U">Contraseña:</label>
                                                <div style="position: relative;">
                                                    <input type="password" class="form-control" id="password_U" name="password_U"  placeholder="Ingrese su contraseña..." required>
                                                    <!-- Boton del ojo para visualizar la contraseña -->
                                                    <button type="button" id="verContraUsuario" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none;">
                                                        <i id="eyeIcon" class="fa-solid fa-eye"></i><!--Icono de ojo que cambiar al darle clic -->
                                                    </button>
                                                </div>
                                                <span id="errorAgreContrasena_U" class="texto-error-agre_U text-danger"></span>
                                            </div>
                                        </div>
                                        <!--
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="password_U">Contraseña</label>
                                                <input type="password" class="form-control" id="password_U" name="password_U" placeholder="Ingrese su contraseña..." required>
                                            </div>
                                        </div>
                                        -->
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Agregar Nuevo Usuario</button>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Campos de búsqueda -->
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <input type="text" id="buscarDNI" class="form-control" placeholder="Buscar por DNI">
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" id="buscarNombre" class="form-control" placeholder="Buscar por nombre">
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" id="buscarApellidoP" class="form-control" placeholder="Buscar por apellido paterno">
                        </div>
                        <div class="col-md-3 mb-3">
                            <input type="text" id="buscarApellidoM" class="form-control" placeholder="Buscar por apellido materno">
                        </div>
                        <!-- Filtro de Estado -->
                        <div class="col-md-3">
                            <label for="filtroEstado">Filtrar por Estado:</label>
                            <select id="filtroEstado" class="form-control">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                            </select>
                        </div>

                        <!-- Filtro de Género -->
                        <div class="col-md-3">
                            <label for="filtroGenero">Filtrar por Género:</label>
                            <select id="filtroGenero" class="form-control">
                                <option value="">Todos</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                    </div>


                         <!-- Tabla de usuarios -->
                         <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="tablaUsuarios">
                            <thead class="table-dark">
                                <tr>
                                    <th>DNI</th>
                                    <th>Nombre</th>
                                    <th>Apellido Paterno</th>
                                    <th>Apellido Materno</th>
                                    <th>Celular</th>
                                    <th>Fecha Nacimiento</th>
                                    <th>Correo</th>
                                    <th>Género</th>
                                    <th>Direccion</th>
                                    <th>Estado</th>
                                    <th>Acciones</th> <!-- Nueva columna para botones -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultado as $usuario) : ?>
                                    <tr>
                                        <td class="dni"><?php echo htmlspecialchars($usuario['dni']); ?></td>
                                        <td class="nombre"><?php echo htmlspecialchars($usuario['nombres']); ?></td>
                                        <td class="apellido_paterno"><?php echo htmlspecialchars($usuario['apellido_paterno']); ?></td>
                                        <td class="apellido_materno"><?php echo htmlspecialchars($usuario['apellido_materno']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['celular']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                        <td class="genero"><?php echo htmlspecialchars($usuario['genero']); ?></td>
                                        <td><?php echo htmlspecialchars($usuario['direccion']); ?></td>
                                        <td class="estado <?= ($usuario['estado'] == 1) ? 'text-success' : 'text-danger'; ?>" data-dni="<?= $usuario['dni']; ?>">
                                            <?= ($usuario['estado'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                        </td>

                                        <td class="d-flex flex-column" style="gap: 10px;">
                                                <button class="btn btn-warning btnEditar" 
                                                    data-toggle="modal" 
                                                    data-target="#modalEditarUsuario" 
                                                    data-dni="<?php echo $usuario['dni']; ?>"
                                                    data-nombre="<?php echo $usuario['nombres']; ?>"
                                                    data-apellido-paterno="<?php echo $usuario['apellido_paterno']; ?>"
                                                    data-apellido-materno="<?php echo $usuario['apellido_materno']; ?>"
                                                    data-celular="<?php echo $usuario['celular']; ?>"
                                                    data-fecha-nacimiento="<?php echo $usuario['fecha_nacimiento']; ?>"
                                                    data-correo="<?php echo $usuario['correo']; ?>"
                                                    data-genero="<?php echo $usuario['genero']; ?>"
                                                    data-direccion="<?php echo $usuario['direccion']; ?>">
                                                    Editar
                                                </button>
                                                <!-- <button class="btn btn-danger btnEliminar" data-dni="--><!--< x" ?= $usuario['dni']; ?>">Eliminar</button>-->
                                                <button class="btn btnCambiarEstado <?= ($usuario['estado'] == 1) ? 'btn-secondary' : 'btn-success'; ?>" 
                                                    data-dni="<?= $usuario['dni']; ?>" 
                                                    data-estado="<?= $usuario['estado']; ?>">
                                                    <?= ($usuario['estado'] == 1) ? 'Desactivar' : 'Activar'; ?>
                                                </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                </div>
            
            <!-- Modal para Editar Usuario -->
                <div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditarLabel">Editar Usuario</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <!--Registro editar usuario -->
                                    <form id="formEditarUsuario2">
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="editDniU">DNI:</label>
                                                <input type="text" class="form-control" id="editDniU" name="editDniU" readonly>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label for="editNombre">Nombre:</label>
                                                <input type="text" class="form-control" id="editNombre" name="editNombre" required>
                                                <span id="errorEditNombre" class="texto-error-edit text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="editApellidoPaterno">Apellido Paterno:</label>
                                                <input type="text" class="form-control" id="editApellidoPaterno" name="editApellidoPaterno" required>
                                                <span id="errorEditApellidoPaterno" class="texto-error-edit text-danger"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="editApellidoMaterno">Apellido Materno:</label>
                                                <input type="text" class="form-control" id="editApellidoMaterno" name="editApellidoMaterno" required>
                                                <span id="errorEditApellidoMaterno" class="texto-error-edit text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="editCelular">Celular:</label>
                                                <input type="text" class="form-control" id="editCelular" name="editCelular" required>
                                                <span id="errorEditCelular" class="texto-error-edit text-danger"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="editFechaNacimiento">Fecha de Nacimiento:</label>
                                                <input type="date" class="form-control" id="editFechaNacimiento" name="editFechaNacimiento" required>
                                                <span id="errorEditFechaNacimiento" class="texto-error-edit text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6" style="margin-bottom:15px;">
                                                <label for="editCorreo">Correo:</label>
                                                <input type="email" class="form-control" id="editCorreo" name="editCorreo" required>
                                                <span id="errorEditCorreo" class="texto-error-edit text-danger"></span>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="editGenero">Género:</label>
                                                <select class="form-control" id="editGenero" name="editGenero" required>
                                                    <option value="Masculino">Masculino</option>
                                                    <option value="Femenino">Femenino</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                                <span id="errorEditGenero" class="texto-error-edit text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="editDireccion">Dirección:</label>
                                            <input type="text" class="form-control" id="editDireccion" name="editDireccion" required>
                                            <span id="errorEditDireccion" class="texto-error-edit text-danger"></span>
                                        </div>
                                        <div class="col-md-6">
                                        <label for="editRol">Rol:</label>
                                                <select class="form-control" id="editRol" name="editRol" required>
                                                    <option value="cliente">Cliente</option>
                                                    <option value="trabajador">Trabajador</option>                                                    
                                                </select>
                                                <span id="errorEditRol" class="texto-error-edit text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                        <div class="col-md-6">
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

            </div>
            <!-- Final del contenido -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <small>&copy; 2024 <b>Delgado Eletronic</b> - Todos los Derechos Reservados.</small>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="../js/sb-admin-2.js"></script>

    <!-- Page level plugins -->
    <script src="../vendor/chart.js/Chart.js"></script>

    <!-- Page level custom scripts -->
    <!--<script src="../js/demo/chart-area-demo.js"></script>
    <script src="../js/demo/chart-pie-demo.js"></script>-->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="../js/reporte-usuarios.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/filtro_usuario.js"></script>
    
    <!-- SCRIPT para agregar usuario -->
    <!--
    <script>
        $(document).ready(function() {
            $("#registerForm").submit(function(event) {
                event.preventDefault(); // Evita el envío tradicional

                $.ajax({
                    type: "POST",
                    url: "clientes_admin.php",
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            alert("Usuario agregado correctamente");
                            location.reload(); // Refresca la tabla
                        } else {
                            alert("Error: " + response.message);
                        }
                    }
                });
            });
        });
    </script>
                                -->
    <!-- SCRIPT para editar usuario -->
     <!--
    <script>

            $(document).ready(function () {
                $(".btnEditar").click(function () {
                    // Obtener los datos del usuario desde los atributos del botón
                    let dni = $(this).data("dni");
                    let nombre = $(this).data("nombre");
                    let apellidoPaterno = $(this).data("apellido-paterno");
                    let apellidoMaterno = $(this).data("apellido-materno");
                    let celular = $(this).data("celular");
                    let fechaNacimiento = $(this).data("fecha-nacimiento");
                    let correo = $(this).data("correo");
                    let genero = $(this).data("genero");
                    let direccion = $(this).data("direccion");

                    // Llenar los campos del formulario de edición
                    $("#editDniU").val(dni);
                    $("#editNombre").val(nombre);
                    $("#editApellidoPaterno").val(apellidoPaterno);
                    $("#editApellidoMaterno").val(apellidoMaterno);
                    $("#editCelular").val(celular);
                    $("#editFechaNacimiento").val(fechaNacimiento);
                    $("#editCorreo").val(correo);
                    $("#editGenero").val(genero);
                    $("#editDireccion").val(direccion);
                });

                // Enviar el formulario para actualizar los datos en la base de datos
                $("#formEditarUsuario").submit(function (e) {
                    e.preventDefault(); // Evitar el envío normal del formulario

                    let formData = $(this).serialize() + "&accion=editar"; // Serializar los datos del formulario

                    $.ajax({
                        type: "POST",
                        url: "clientes_admin.php", // Cambia esto por la ruta real de tu archivo PHP
                        data: formData,
                        dataType: "json",
                        success: function (response) {
                            if (response.status === "success") {
                                alert("Usuario actualizado correctamente");
                                location.reload(); // Recargar la página para ver los cambios en la tabla
                            } else {
                                alert("Error al actualizar el usuario: " + response.message);
                            }
                        },
                        error: function () {
                            alert("Hubo un error al procesar la solicitud");
                        }
                    });
                });
            });

    </script>
                                -->
    <!-- SCRIPT para eliminar usuario -->
     <!--
    <script>
                $(document).on("click", ".btnEliminar", function () {
                let dni = $(this).data("dni");
                
                if (confirm("¿Estás seguro de que quieres eliminar este usuario?")) {
                    $.ajax({
                        url: "clientes_admin.php",
                        type: "POST",
                        data: { accion: "eliminar", dni: dni },
                        dataType: "json",
                        success: function (response) {
                            if (response.status === "success") {
                                alert(response.message);
                                location.reload(); // Recargar la página para actualizar la tabla
                            } else {
                                alert("Error: " + response.message);
                            }
                        },
                        error: function () {
                            alert("Error en la solicitud.");
                        }
                    });
                }
            });

    </script>
                                -->
    <!-- SCRIPT para cambiar estado del usuario -->
    <script>
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".btnCambiarEstado").forEach(button => {
                    button.addEventListener("click", function() {
                        let dni = this.getAttribute("data-dni");
                        let estadoActual = this.getAttribute("data-estado");
                        let nuevoEstado = (estadoActual == 1) ? 0 : 1;

                        fetch("clientes_admin.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `dni=${encodeURIComponent(dni)}&estado=${encodeURIComponent(estadoActual)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Actualizar la celda del estado
                                let estadoCelda = document.querySelector(`.estado[data-dni='${dni}']`);
                                if (estadoCelda) {
                                    estadoCelda.textContent = (data.nuevoEstado == 1) ? "Activo" : "Inactivo";
                                    // Remueve clases antiguas y agrega la nueva según el estado
                                    estadoCelda.classList.remove("text-success", "text-danger");
                                    estadoCelda.classList.add(data.nuevoEstado == 1 ? "text-success" : "text-danger");
                                }

                                // Actualizar botón
                                this.setAttribute("data-estado", data.nuevoEstado);
                                this.textContent = (data.nuevoEstado == 1) ? "Desactivar" : "Activar";

                                // Cambiar la clase del botón
                                this.classList.toggle("btn-secondary", data.nuevoEstado == 1);
                                this.classList.toggle("btn-success", data.nuevoEstado == 0);
                            } else {
                                alert("Error al cambiar el estado");
                            }
                        })
                        .catch(error => console.error("Error:", error));
                    });
                });
            });
    </script>
    <script src="../js/validarRegistro.js"></script>
</body>

</html>