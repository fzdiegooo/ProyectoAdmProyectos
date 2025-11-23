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

// Verificamos si se envió el formulario de agregar producto
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {

    // Ruta donde se guardarán las imágenes
    $directorioDestino = "../Admin/images/";

    // Verificar si hay una imagen subida
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $fotoNombre = basename($_FILES['foto']['name']);
        $rutaDestino = $directorioDestino . $fotoNombre;
        $rutaBD = "images/" . $fotoNombre;

        // Asegurar que la carpeta exista
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        // Mover el archivo a la carpeta destino
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
            $foto = $rutaBD; // Guardamos la ruta relativa en la BD
        } else {
            $foto = ''; // En caso de error, dejamos vacío
        }
    } else {
        $foto = ''; // Si no hay imagen, se deja vacío
    }

    // Preparar la consulta SQL de inserción
    $sql_insert = $con->prepare("INSERT INTO productos (codigo, foto, id_categoria, id_marca, id_proveedor, descripcion, stock, pventa, desc_web) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Vincular parámetros y ejecutar la consulta
    $codigo = $_POST['codigoP'];
    $id_categoria = $_POST['id_categoriaP'];
    $id_marca = $_POST['id_marcaP'];
    $id_proveedor = $_POST['id_proveedorP'];
    $descripcion = $_POST['descripcionP'];
    $stock = $_POST['stockP'];
    $pventa = $_POST['pventaP'];
    $desc_web = $_POST['desc_web'];

    $sql_insert->bindParam(1, $codigo, PDO::PARAM_INT);
    $sql_insert->bindParam(2, $foto, PDO::PARAM_STR);
    $sql_insert->bindParam(3, $id_categoria, PDO::PARAM_INT);
    $sql_insert->bindParam(4, $id_marca, PDO::PARAM_INT);
    $sql_insert->bindParam(5, $id_proveedor, PDO::PARAM_INT);
    $sql_insert->bindParam(6, $descripcion, PDO::PARAM_STR);
    $sql_insert->bindParam(7, $stock, PDO::PARAM_INT);
    $sql_insert->bindParam(8, $pventa, PDO::PARAM_STR);
    $sql_insert->bindParam(9, $desc_web, PDO::PARAM_STR);


    header('Content-Type: application/json');
    if ($sql_insert->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto agregado correctamente"]);
        exit;
        //echo "Producto agregado correctamente";
    } else {
        echo json_encode(["status" => "error", "message" => "Error al agregar el producto"]);
        exit;
        //echo "Error al agregar el producto";
    }
    //exit();
}

// Verificar si se envió el formulario de actualización de producto
if (isset($_POST['accion']) && $_POST['accion'] === 'actualizar') {

    // Ruta donde se guardarán las imágenes
    $directorioDestino = "../Admin/images/";

    // Obtener el código del producto
    $codigo = $_POST['codigoEditarP'];

    // Obtener el resto de los valores del formulario
    $id_categoria = $_POST['id_categoriaEditarP'];
    $id_marca = $_POST['id_marcaEditarP'];
    $id_proveedor = $_POST['id_proveedorEditarP'];
    $descripcion = $_POST['descripcionEditarP'];
    $stock = $_POST['stockEditarP'];
    $pventa = $_POST['pventaEditarP'];
    $desc_web = $_POST['desc_webEditar'];

    // Consulta para obtener la imagen actual del producto
    $sql_get_image = $con->prepare("SELECT foto FROM productos WHERE codigo = ?");
    $sql_get_image->execute([$codigo]);
    $producto = $sql_get_image->fetch(PDO::FETCH_ASSOC);
    $imagenActual = $producto['foto']; // Imagen actual en la BD

    // Manejo de la nueva imagen
    if (!empty($_FILES['fotoEditar']['name']) && $_FILES['fotoEditar']['error'] === UPLOAD_ERR_OK) {
        $fotoNombre = basename($_FILES['fotoEditar']['name']);
        $rutaDestino = $directorioDestino . $fotoNombre;
        $rutaBD = "images/" . $fotoNombre;

        // Asegurar que la carpeta de destino existe
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0777, true);
        }

        if (move_uploaded_file($_FILES['fotoEditar']['tmp_name'], $rutaDestino)) {

            
            //echo "✅ Imagen subida correctamente: " . $rutaDestino . "<br>";
            // Verificar si se debe eliminar la imagen anterior
            if (!empty($imagenActual) && file_exists("../Admin/" . $imagenActual) && $imagenActual !== $rutaBD) {
                unlink("../Admin/" . $imagenActual);
                //echo "✅ Imagen anterior eliminada: " . $imagenActual . "<br>";
                
            }
            $foto = $rutaBD; // Guardamos la nueva ruta de imagen en la BD
        } else {
            /*
            echo "❌ Error al mover la imagen.<br>";
            echo "Temp: " . $_FILES['fotoEditar']['tmp_name'] . "<br>";
            echo "Destino: " . $rutaDestino . "<br>";
            $foto = $imagenActual;
            */
            //echo json_encode(["status" => "error", "message" => "Error al mover la imagen"]);
        }
        
    } else {
        $foto = $imagenActual; // No se subió nueva imagen, se mantiene la anterior
        //echo json_encode(["status" => "error", "message" => "Error al mover la imagen"]);
    }

    // Preparar la consulta SQL de actualización
    $sql_update = $con->prepare("UPDATE productos SET foto=?, id_categoria=?, id_marca=?, id_proveedor=?, descripcion=?, stock=?, pventa=?, 
    desc_web =? WHERE codigo=?");

    // Vincular parámetros y ejecutar la consulta
    $sql_update->bindParam(1, $foto, PDO::PARAM_STR);
    $sql_update->bindParam(2, $id_categoria, PDO::PARAM_INT);
    $sql_update->bindParam(3, $id_marca, PDO::PARAM_INT);
    $sql_update->bindParam(4, $id_proveedor, PDO::PARAM_INT);
    $sql_update->bindParam(5, $descripcion, PDO::PARAM_STR);
    $sql_update->bindParam(6, $stock, PDO::PARAM_INT);
    $sql_update->bindParam(7, $pventa, PDO::PARAM_STR);
    $sql_update->bindParam(8, $desc_web, PDO::PARAM_STR);
    $sql_update->bindParam(9, $codigo, PDO::PARAM_INT);

    header('Content-Type: application/json');
    if ($sql_update->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto actualizado correctamente"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error al actualizar el producto"]);
        exit;
    }
    //echo "Producto actualizado correctamente.";
        //echo "Filas afectadas: " . $sql_update->rowCount();
      //echo "Error al actualizar el producto.";
}


// Verificar si se envió la solicitud para eliminar un producto
if (isset($_POST['elimina']) && $_POST['elimina'] === 'eliminar' && isset($_POST['codigo'])) {

    // Preparar la consulta SQL de eliminación
    $sql_delete = $con->prepare("UPDATE productos SET estado = ? WHERE codigo = ?;");

    // Vincular el código del producto y ejecutar la consulta de eliminación
    $codigo = $_POST['codigo'];
    $estado = 0;
    $sql_delete->bindParam(1, $estado, PDO::PARAM_INT);
    $sql_delete->bindParam(2, $codigo, PDO::PARAM_INT);

    header('Content-Type: application/json');
    if ($sql_delete->execute()) {
        echo json_encode(["status" => "success", "message" => "Producto eliminado correctamente"]);
        exit;
        //echo "Producto eliminado correctamente";
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar el producto"]);
        exit;
        //echo "Error al eliminar el producto";
    }
    exit(); // Detener la ejecución del script después de manejar la solicitud de eliminación
}


// Verificar si se envió un código a través de AJAX para verificar su existencia
if (isset($_POST['codigo'])) {
    // Obtener el código enviado por AJAX
    $codigo = $_POST['codigo'];

    // Realizar la consulta para verificar si el código existe
    $sql = $con->prepare("SELECT COUNT(*) AS count FROM productos WHERE codigo = ?");
    $sql->execute([$codigo]);
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);

    // Devolver respuesta al cliente
    echo json_encode(['exists' => $resultado['count'] > 0]);
    exit(); // Detener la ejecución del script después de manejar la solicitud AJAX
}

//Consulta que llama todos los datos de los productos
$sql = $con->prepare("SELECT p.codigo, p.foto, p.id_categoria, p.id_marca, p.id_proveedor, 
       p.descripcion, p.stock, p.pventa, p.desc_web, 
       m.nombre_marca, pr.nombre_proveedor 
FROM productos p
LEFT JOIN marcas m ON p.id_marca = m.id_marca
LEFT JOIN proveedores pr ON p.id_proveedor = pr.id_proveedor
WHERE p.estado = ?");
$estado = 1;
$sql->bindParam(1, $estado, PDO::PARAM_INT);
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Admin - Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../css/sb-admin-2.css" rel="stylesheet">
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

    <!-- Page Wrapper -->
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
                <div>
                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"></h1>
                        <a href="#" id="exportProductos" class="btn btn-sm bg-custom-color shadow-sm">
                            <i class="fas fa-download fa-sm text-white-50"></i> Generar Reporte de Productos
                        </a>
                    </div>

                    <!-- Content Productos -->
                    <div class="container-fluid">
                        <h2>Listado de Productos</h2>

                        <!-- Botón Agregar Producto -->
                        <button type="button" class="btn btn-success btn_agregar_Padmin" data-toggle="modal" data-target="#agregarProductoModal">
                            Agregar Producto
                        </button>


                        <!-- Modal Agregar Producto -->
                        <div class="modal fade" id="agregarProductoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Agregar Nuevo Producto</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formulario para ingresar detalles del nuevo producto -->
                                        <form id="formAgregarProducto" method="POST" enctype="multipart/form-data">
                                            <!-- Agrega los campos necesarios para el nuevo producto -->
                                            <div class="row separador" style="margin-bottom: 7px">
                                                <div class="col-md-6">
                                                    <label for="codigoP">Código:</label>
                                                    <input type="text" class="form-control" id="codigoP" name="codigoP" readonly>
                                                    <span id="codigoErrorP" class="text-danger"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="id_categoriaP">ID Categoría:</label>
                                                    <select class="form-control" id="id_categoriaP" name="id_categoriaP" required>
                                                    <option value="">Seleccione una categoría</option>
                                                        <option value="1">1 - Computacion</option>
                                                        <option value="2">2 - Electronica</option>
                                                        <option value="3">3 - Electricidad</option>
                                                        <option value="4">4 - Ferreteria</option>
                                                        <option value="5">5 - Redes y Telec</option>
                                                    </select>
                                                    <span id="validaIdCategoriaP" class="text-danger"></span>
                                                </div>                                                
                                            </div>
                                            <div class="row separador" style="margin-bottom: 7px">
                                                <div class="col-md-6">
                                                    <label for="id_marcaP">Marca:</label>
                                                    <select class="form-control" id="id_marcaP" name="id_marcaP" required>
                                                        <option value="">Seleccione una marca</option>
                                                        <?php
                                                        $sql_marcas = $con->prepare("SELECT id_marca, nombre_marca FROM marcas");
                                                        $sql_marcas->execute();
                                                        $marcas = $sql_marcas->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($marcas as $marca) {
                                                            echo "<option value='{$marca['id_marca']}'>{$marca['id_marca']} - {$marca['nombre_marca']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="id_proveedorP">Proveedor:</label>
                                                    <select class="form-control" id="id_proveedorP" name="id_proveedorP" required>
                                                        <option value="">Seleccione un proveedor</option>
                                                        <?php
                                                        $sql_proveedores = $con->prepare("SELECT id_proveedor, nombre_proveedor FROM proveedores");
                                                        $sql_proveedores->execute();
                                                        $proveedores = $sql_proveedores->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($proveedores as $proveedor) {
                                                            echo "<option value='{$proveedor['id_proveedor']}'>{$proveedor['id_proveedor']} - {$proveedor['nombre_proveedor']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">
                                                    <label for="descripcionP">Nombre:</label>
                                                    <input type="text" class="form-control" id="descripcionP" name="descripcionP" required>
                                                    <span id="validaDescripcionP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">
                                                    <label for="stockP">Stock:</label>
                                                    <input type="text" class="form-control" id="stockP" name="stockP" required>
                                                    <span id="validaStockP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">
                                                    <label for="pventaP">Precio de Venta:</label>
                                                    <input type="text" class="form-control" id="pventaP" name="pventaP" required>
                                                    <span id="validaPventaP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">  
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                            </div>
                                            <div class="form-group">
                                                <label>Adjuntar imagen 📂: </label>
                                                <!--<label for="foto" id="icon-image" ></label>
                                                <span id="icon-cerrar"></span>-->
                                                <input type="file" id="foto" name="foto" class ="form-control" required onchange="preview(event)">
                                                <img class="img-thumbnail" id="img-preview">
                                            </div>
                                            
                                            <div class="form-group text-center">
                                                <label>¿Deseas colocar información en la web?</label><br>
                                                <div class="d-inline-block">
                                                    <input type="radio" name="mostrarCampos" value="si" id="mostrarSi"> Sí
                                                    <input type="radio" name="mostrarCampos" value="no" id="mostrarNo" checked> No
                                                </div>
                                            </div>

                                            <!-- Campos ocultos -->
                                            <div id="camposWeb" style="display: none;">
                                                
                                                    <div class="form-group">
                                                        <label for="desc_web">Descripción Web</label>
                                                        <textarea type="text" class="form-control" id="desc_web" name="desc_web"style="resize: none;"></textarea>
                                                        <span id="validaDesc_web" class="text-danger"></span>
                                                    </div>
                                            </div>
                                            <!-- Agrega mas campos segun sea necesario -->
                                            <button type="submit" class="btn btn-primary">Agregar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>


                                <!-- Campos de búsqueda -->
                            <div class="row mb-3">
                                <div class="col-md-4 mb-3">
                                    <input type="text" id="buscarCodigo" class="form-control" placeholder="Buscar por Código">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <input type="text" id="buscarDescripcion" class="form-control" placeholder="Buscar por Descripción">
                                </div>
                            </div>

                                <!-- Filtro por Categoría -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="filtroCategoria">Filtrar por Categoría:</label>
                                        <select id="filtroCategoria" class="form-control" multiple>
                                            <option value="0">Todas</option>
                                            <option value="1">Computacion</option>
                                            <option value="2">Electronica</option>
                                            <option value="3">Electricidad</option>
                                            <option value="4">Ferretería</option>
                                            <option value="5">Redes y Telec.</option>
                                        </select>
                                    </div>
                                </div>

                            <!-- TABLA PRODUCTOS -->
                            <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                                <table id="tablaProductos" class="table table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Código</th>
                                            <th>Foto</th>
                                            <th>Descripción</th>
                                            <th>Marca</th>
                                            <th>Proveedor</th>
                                            <th>Stock</th>
                                            <th>Precio Venta</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resultado as $producto) : ?>
                                            <tr data-id-categoria="<?php echo $producto['id_categoria']; ?>"
                                                data-id-marca="<?php echo $producto['id_marca']; ?>"
                                                data-id-proveedor="<?php echo $producto['id_proveedor']; ?>">       

                                                <td class="codigo"><?php echo htmlspecialchars($producto['codigo']); ?></td>
                                                <td class="foto">
                                                    <?php if (!empty($producto['foto'])): ?>
                                                        <img src="<?php echo htmlspecialchars($producto['foto']); ?>" width="100"><br>
                                                        <small><?php echo "images/". basename(htmlspecialchars($producto['foto'])); ?></small>
                                                    <?php else: ?>
                                                        Sin imagen
                                                    <?php endif; ?>
                                                </td>
                                                <td class="descripcion"><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                                                <td><?php echo htmlspecialchars($producto['nombre_marca'] ?? 'Sin marca'); ?></td>
                                                <td><?php echo htmlspecialchars($producto['nombre_proveedor'] ?? 'Sin proveedor'); ?></td>
                                                <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                                                <td><?php echo htmlspecialchars($producto['pventa']); ?></td>
                                                
                                                <td class="text-center">
                                                 <div class="d-flex flex-column align-items-center">
                                                    <button type="button" class="btn btn-primary btn-editar-producto mb-3" 
                                                        data-toggle="modal" 
                                                        data-target="#editarProductoModal"
                                                        data-codigo="<?php echo $producto['codigo']; ?>" 
                                                        data-id-categoria="<?php echo $producto['id_categoria']; ?>"
                                                        data-id-marca="<?php echo $producto['id_marca']; ?>"
                                                        data-id-proveedor="<?php echo $producto['id_proveedor']; ?>"
                                                        data-descripcion="<?php echo $producto['descripcion']; ?>" 
                                                        data-stock="<?php echo $producto['stock']; ?>"
                                                        data-pventa="<?php echo $producto['pventa']; ?>" 
                                                        data-desc-web="<?php echo $producto['desc_web']; ?>">
                                                        Editar
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-eliminar-producto mb-3" data-codigo="<?php echo $producto['codigo']; ?>">
                                                        Eliminar
                                                    </button>
                                                 </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal Editar Producto -->
                        <div class="modal fade" id="editarProductoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Editar Producto</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formulario para editar detalles del producto -->
                                        <form id="formEditarProducto" enctype="multipart/form-data">

                                            <!-- Agrega los campos necesarios para editar el producto -->
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">
                                                    <label for="codigoEditarP">Código:</label>
                                                    <input type="text" class="form-control" id="codigoEditarP" name="codigoEditarP" readonly>
                                                </div>
                                                <div class="col-md-6">                                                
                                                    <label for="id_categoriaEditarP">ID Categoría:</label>
                                                    <select class="form-control" id="id_categoriaEditarP" name="id_categoriaEditarP" required>
                                                        <option value="">Seleccione una categoría</option>
                                                        <option value="1">1 - Computacion</option>
                                                        <option value="2">2 - Electronica</option>
                                                        <option value="3">3 - Electricidad</option>
                                                        <option value="4">4 - Ferreteria</option>
                                                        <option value="5">5 - Redes</option>
                                                    </select>
                                                    <span id="validaIdCategoriaEditarP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <!-- Dentro del formulario #formEditarProducto -->
                                            <div class="row separador" style="margin-bottom: 7px">
                                                <div class="col-md-6">
                                                    <label for="id_marcaEditarP">Marca:</label>
                                                    <select class="form-control" id="id_marcaEditarP" name="id_marcaEditarP" required>
                                                        <option value="">Seleccione una marca</option>
                                                        <?php
                                                        $sql_marcas = $con->prepare("SELECT id_marca, nombre_marca FROM marcas");
                                                        $sql_marcas->execute();
                                                        $marcas = $sql_marcas->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($marcas as $marca) {
                                                            echo "<option value='{$marca['id_marca']}'>{$marca['id_marca']} - {$marca['nombre_marca']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="id_proveedorEditarP">Proveedor:</label>
                                                    <select class="form-control" id="id_proveedorEditarP" name="id_proveedorEditarP" required>
                                                        <option value="">Seleccione un proveedor</option>
                                                        <?php
                                                        $sql_proveedores = $con->prepare("SELECT id_proveedor, nombre_proveedor FROM proveedores");
                                                        $sql_proveedores->execute();
                                                        $proveedores = $sql_proveedores->fetchAll(PDO::FETCH_ASSOC);
                                                        foreach ($proveedores as $proveedor) {
                                                            echo "<option value='{$proveedor['id_proveedor']}'>{$proveedor['id_proveedor']} - {$proveedor['nombre_proveedor']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">                                                
                                                    <label for="descripcionEditarP">Nombre:</label>
                                                    <input type="text" class="form-control" id="descripcionEditarP" name="descripcionEditarP" required>
                                                    <span id="validaDescripcionEditarP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                                <div class="col-md-6">                                                
                                                    <label for="stockEditarP">Stock:</label>
                                                    <input type="text" class="form-control" id="stockEditarP" name="stockEditarP" required>
                                                    <span id="validaStockEditarP" class="text-danger"></span>
                                                </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                            <div class="col-md-6">                                                
                                                <label for="pventaEditarP">Precio de Venta:</label>
                                                <input type="text" class="form-control" id="pventaEditarP" name="pventaEditarP" required>
                                                <span id="validaPventaEditarP" class="text-danger"></span>
                                            </div>
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                            </div>
                                            <div class="row separador" style="margin-bottom:7px">
                                            </div>
                                            
                                            
                                            <div class="form-group">
                                                <label for="fotoEditar">Imagen del Producto 📂:</label>
                                                
                                                <input type="file" class="form-control" id="fotoEditar" name="fotoEditar" accept="image/*">
                                                <img id="previewImagenEditar" src="" width="350">
                                                            
                                            </div>
                                            <div class="form-group text-center">
                                                <label>¿Deseas editar la información en la web?</label><br>
                                                <div class="d-inline-block">
                                                    <input type="radio" name="mostrarCampos" value="si" id="mostrarSi2"> Sí
                                                    <input type="radio" name="mostrarCampos" value="no" id="mostrarNo2" checked> No
                                                </div>
                                            </div>
                                            <div id="camposWeb2" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="desc_webEditar">Descripción</label>
                                                        <textarea type="text" class="form-control" id="desc_webEditar" name="desc_webEditar"style="resize: none;"></textarea>
                                                        <span id="validaDesc_webEditar" class="text-danger"></span>
                                                    </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

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

    <script src="../js/reporte-productos.js"></script>
    <script src="../js/funciones.js"></script>
    <script src="../js/filtroPRODUCTOS.js"></script>
    <script>
        //Función que permite mostrar los campos de descripción de los productos 
        document.addEventListener("DOMContentLoaded", function() {
            let mostrarSi = document.getElementById("mostrarSi");
            let mostrarNo = document.getElementById("mostrarNo");
            let camposWeb = document.getElementById("camposWeb");
            let mostrarSi2 = document.getElementById("mostrarSi2");
            let mostrarNo2 = document.getElementById("mostrarNo2");
            let camposWeb2 = document.getElementById("camposWeb2");

            if (mostrarSi && mostrarNo && camposWeb) {
                mostrarSi.addEventListener("change", function() {
                    if (this.checked) {
                        camposWeb.style.display = "block"; // Mostrar los campos
                    }
                });

                mostrarNo.addEventListener("change", function() {
                    if (this.checked) {
                        camposWeb.style.display = "none"; // Ocultar los campos
                    }
                });
            }
            if (mostrarSi2 && mostrarNo2 && camposWeb2) {
                mostrarSi2.addEventListener("change", function() {
                    if (this.checked) {
                        camposWeb2.style.display = "block"; // Mostrar los campos
                    }
                });

                mostrarNo2.addEventListener("change", function() {
                    if (this.checked) {
                        camposWeb2.style.display = "none"; // Ocultar los campos
                    }
                });
            }
        });
    </script>

</body>

</html>