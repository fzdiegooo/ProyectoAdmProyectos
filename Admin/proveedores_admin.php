<?php
require '../Config/config.php';
require '../php/database.php';

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

// Verificamos si se envió el formulario de agregar proveedor
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    // Preparar la consulta SQL de inserción
    $sql_insert = $con->prepare("INSERT INTO proveedores (nombre_proveedor, telefono, email, direccion) 
                                VALUES (?, ?, ?, ?)");

    // Vincular parámetros y ejecutar la consulta
    $nombre = $_POST['nombre_proveedor'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];

    $sql_insert->bindParam(1, $nombre, PDO::PARAM_STR);
    $sql_insert->bindParam(2, $telefono, PDO::PARAM_STR);
    $sql_insert->bindParam(3, $email, PDO::PARAM_STR);
    $sql_insert->bindParam(4, $direccion, PDO::PARAM_STR);

    header('Content-Type: application/json');
    if ($sql_insert->execute()) {
        echo json_encode(["status" => "success", "message" => "Proveedor agregado correctamente"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error al agregar el proveedor"]);
        exit;
    }
}

// Verificar si se envió el formulario de actualización de proveedor
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {
    // Preparar la consulta SQL de actualización
    $sql_update = $con->prepare("UPDATE proveedores SET nombre_proveedor=?, telefono=?, email=?, direccion=? 
                                WHERE id_proveedor=?");

    // Vincular parámetros y ejecutar la consulta
    $id_proveedor = $_POST['id_proveedor'];
    $nombre = $_POST['nombre_proveedor'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];

    $sql_update->bindParam(1, $nombre, PDO::PARAM_STR);
    $sql_update->bindParam(2, $telefono, PDO::PARAM_STR);
    $sql_update->bindParam(3, $email, PDO::PARAM_STR);
    $sql_update->bindParam(4, $direccion, PDO::PARAM_STR);
    $sql_update->bindParam(5, $id_proveedor, PDO::PARAM_INT);

    header('Content-Type: application/json');
    if ($sql_update->execute()) {
        echo json_encode(["status" => "success", "message" => "Proveedor actualizado correctamente"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el proveedor"]);
        exit;
    }
}

// Verificar si se envió la solicitud para eliminar un proveedor
if (isset($_POST['eliminar']) && $_POST['eliminar'] === 'eliminar' && isset($_POST['id_proveedor'])) {
    header('Content-Type: application/json');
    
    try {
        // Primero verificamos si el proveedor tiene productos asociados
        $sql_check = $con->prepare("SELECT COUNT(*) FROM productos WHERE id_proveedor = ?");
        $sql_check->execute([$_POST['id_proveedor']]);
        $tiene_productos = $sql_check->fetchColumn() > 0;

        if ($tiene_productos) {
            echo json_encode([
                "status" => "error", 
                "message" => "No se puede eliminar: El proveedor tiene productos asociados"
            ]);
            exit;
        }

        // Si no tiene productos, procedemos a eliminar
        $sql_delete = $con->prepare("DELETE FROM proveedores WHERE id_proveedor = ?");
        $sql_delete->bindParam(1, $_POST['id_proveedor'], PDO::PARAM_INT);

        if ($sql_delete->execute()) {
            echo json_encode(["status" => "success", "message" => "Proveedor eliminado correctamente"]);
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar el proveedor"]);
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Error de base de datos: " . $e->getMessage()]);
        exit;
    }
}




// Verificar si se envió un ID para obtener datos de un proveedor específico
if (isset($_GET['obtener_proveedor']) && isset($_GET['id_proveedor'])) {
    $sql = $con->prepare("SELECT * FROM proveedores WHERE id_proveedor = ?");
    $sql->execute([$_GET['id_proveedor']]);
    $proveedor = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($proveedor) {
        header('Content-Type: application/json');
        echo json_encode($proveedor);
    } else {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Proveedor no encontrado"]);
    }
    exit;
}



// Consulta que llama todos los datos de los proveedores
$sql = $con->prepare("SELECT * FROM proveedores ORDER BY id_proveedor DESC");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Cabecera idéntica a tu ejemplo de clientes -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin - Proveedores</title>
    
    <!-- CSS y JS como en tu ejemplo -->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="../css/sb-admin-2.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css" rel="stylesheet">
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
    <!-- Page Wrapper  -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-custom-color sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="admin-page.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class=""></i>
                </div>
                <div class="sidebar-brand-text mx-3">Admin</div>
            </a>

            <!-- Divider -->
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

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn bg-custom-color d-md-none rounded-circle mr-3">
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
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Proveedores</h1>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#nuevoProveedorModal">
                            <i class="fas fa-plus"></i> Nuevo Proveedor
                        </button>
                    </div>

                    <!-- Tabla de Proveedores -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Listado de Proveedores</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Dirección</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultado as $proveedor): ?>
                                        <tr>
                                            <td><?= $proveedor['id_proveedor'] ?></td>
                                            <td><?= htmlspecialchars($proveedor['nombre_proveedor']) ?></td>
                                            <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                                            <td><?= htmlspecialchars($proveedor['email']) ?></td>
                                            <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary btn-editar" 
                                                        data-id="<?= $proveedor['id_proveedor'] ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-eliminar" 
                                                        data-id="<?= $proveedor['id_proveedor'] ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
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
    </div>

    <!-- Modal Nuevo Proveedor -->
    <div class="modal fade" id="nuevoProveedorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formNuevoProveedor">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre*</label>
                            <input type="text" name="nombre_proveedor" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <textarea name="direccion" class="form-control" rows="2"></textarea>
                        </div>
                        <input type="hidden" name="accion" value="agregar">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Proveedor -->
    <div class="modal fade" id="editarProveedorModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Proveedor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formEditarProveedor">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nombre*</label>
                            <input type="text" name="nombre_proveedor" id="edit_nombre" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Dirección</label>
                            <textarea name="direccion" id="edit_direccion" class="form-control" rows="2"></textarea>
                        </div>
                        <input type="hidden" name="id_proveedor" id="edit_id">
                        <input type="hidden" name="accion" value="actualizar">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JS y scripts -->
    <script src="../vendor/jquery/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="../js/sb-admin-2.min.js"></script>
    <script src="../vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#dataTable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            }
        });

        // Guardar nuevo proveedor - Versión mejorada
        $('#formNuevoProveedor').submit(function(e) {
            e.preventDefault();
            
            // Validación básica
            if (!$('input[name="nombre_proveedor"]').val()) {
                Swal.fire({
                    title: 'Error',
                    text: 'El nombre del proveedor es requerido',
                    icon: 'warning'
                });
                return;
            }

            $.ajax({
                url: 'proveedores_admin.php',
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(res) {
                    // Cerrar el modal primero
                    $('#nuevoProveedorModal').modal('hide');
                    
                    // Mostrar notificación
                    Swal.fire({
                        title: res.status === 'success' ? 'Éxito' : 'Error',
                        text: res.message,
                        icon: res.status
                    }).then(() => {
                        if (res.status === 'success') {
                            // Resetear el formulario
                            $('#formNuevoProveedor')[0].reset();
                            // Recargar la página
                            location.reload();
                        } else {
                            // Si hay error, volver a mostrar el modal
                            $('#nuevoProveedorModal').modal('show');
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Ocurrió un error al procesar la solicitud: ' + error,
                        icon: 'error'
                    });
                }
            });
        });

        // Editar proveedor - Versión mejorada
        $('.btn-editar').click(function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'proveedores_admin.php',
                type: 'GET',
                dataType: 'json', // Especifica que esperas JSON
                data: {
                    obtener_proveedor: true, 
                    id_proveedor: id
                },
                success: function(proveedor) {
                    // Ya viene parseado como objeto gracias a dataType: 'json'
                    $('#edit_id').val(proveedor.id_proveedor);
                    $('#edit_nombre').val(proveedor.nombre_proveedor);
                    $('#edit_telefono').val(proveedor.telefono);
                    $('#edit_email').val(proveedor.email);
                    $('#edit_direccion').val(proveedor.direccion);
                    $('#editarProveedorModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo obtener la información del proveedor',
                        icon: 'error'
                    });
                }
            });
        });

        // Actualizar proveedor - Versión mejorada
        $('#formEditarProveedor').submit(function(e) {
            e.preventDefault();
            
            // Validación básica
            if (!$('#edit_nombre').val()) {
                Swal.fire({
                    title: 'Error',
                    text: 'El nombre del proveedor es requerido',
                    icon: 'warning'
                });
                return;
            }

            $.ajax({
                url: 'proveedores_admin.php',
                type: 'POST',
                dataType: 'json',
                data: $(this).serialize(),
                success: function(res) {
                    Swal.fire({
                        title: res.status === 'success' ? 'Éxito' : 'Error',
                        text: res.message,
                        icon: res.status
                    }).then(() => {
                        if (res.status === 'success') {
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error:", error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al actualizar el proveedor: ' + error,
                        icon: 'error'
                    });
                }
            });
        });

        // Eliminar proveedor
        $(document).on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            
            Swal.fire({
                title: '¿Eliminar proveedor?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'proveedores_admin.php',
                        type: 'POST',
                        data: {
                            eliminar: 'eliminar',
                            id_proveedor: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire({
                                title: response.status === 'success' ? 'Éxito' : 'Error',
                                text: response.message,
                                icon: response.status
                            }).then(() => {
                                if (response.status === 'success') {
                                    location.reload();
                                }
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al eliminar el proveedor',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>