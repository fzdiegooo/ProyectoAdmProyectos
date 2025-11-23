<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    // Si no se ha iniciado un sesion se redirige al index.php
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

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

$lista_carrito = array();

if ($productos != null) {
    foreach ($productos as $clave => $cantidad) {
        // Se debe asegurar que el producto exista antes de intentar obtener sus detalles
        $sql = $con->prepare("SELECT codigo, foto, descripcion, pventa, id_categoria, $cantidad AS cantidad FROM productos WHERE codigo=?");
        $sql->execute([$clave]);
        // Se obtienen los detalles del producto de la base de datos
        $producto = $sql->fetch(PDO::FETCH_ASSOC);
        // Se verifica si el producto fue encontrado en la base de datos antes de procesarlo
        if ($producto) {
            // Se agregan los detalles del producto al array $lista_carrito
            $lista_carrito[] = $producto;
        }
    }
}
//session_destroy();


$categorias = array();

$consultaCategorias = "SELECT * FROM categorias";
$resultadoCategorias = $con->prepare($consultaCategorias);
$resultadoCategorias->execute();
$categoriasData = $resultadoCategorias->fetchAll(PDO::FETCH_ASSOC);

foreach ($categoriasData as $categoria) {
    $categorias[$categoria['id_categoria']] = $categoria;
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
    <!-- Asegúrate de incluir Font Awesome en tu proyecto -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../css/Footer.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
</head>

<body class="fondo" id="page-top">
    <!-- cabeceras -->
    <div class="content fixed-header">

        <!--Primera cabecera-->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
         style="height: 35px; background-color: #5EBC50 !important;">
        <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
            <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 945853331
            </a>
        </div>
        </nav>


        <!-- Segunda cabecera -->
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">

            <!-- Logo (visible solo en pantallas medianas y grandes) -->
            <img src="../images/logo-completo.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 75px; width: auto; margin-top: 10px">
            <!-- Logo (visible solo en pantallas celular) -->
            <img src="../images/Icons/logo-icono.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-sm-none" style="height: 50px; width: auto;">
            <!-- Fondo oscurecido -->
            <div id="overlay"></div>
            <!-- Apartado buscar -->
           
            <div class="form-container">
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="" method="post" autocomplete="off">
                    <div class="input-group search-wrapper">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Busca un producto..." aria-label="Search" aria-describedby="basic-addon2" name="campo" id="campo">
                        <div class="input-group-append">
                            <button class="btn bg-custom-color" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                    <ul id="lista" class="list-group"></ul>
                </form>
            </div>
          
            <!-- CSS -->
             
            <style>
                /* Fondo oscurecido */
                #overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    display: none;
                    z-index: 10;
                }

                /* Estilos de la barra de búsqueda */
                .form-container {
                    position: relative;
                    z-index: 20;
                }

                .navbar-search {
                    width: 300px; /* Tamaño inicial */
                    transition: width 0.3s ease-in-out;
                }

                .navbar-search.expanded {
                    width: 600px; /* Tamaño expandido */
                }

                .search-input {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 30px;
                    font-size: 16px;
                }

                .search-btn {
                    border-radius: 50%;
                    padding: 10px;
                }
                
            </style>
            
            <!-- JavaScript -->
            
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    var searchInput = document.getElementById("campo");
                    var overlay = document.getElementById("overlay");
                    var searchForm = document.querySelector(".navbar-search");

                    searchInput.addEventListener("focus", function () {
                        overlay.style.display = "block"; // Oscurecer fondo
                        searchForm.classList.add("expanded"); // Expandir barra
                    });

                    overlay.addEventListener("click", function () {
                        overlay.style.display = "none"; // Quitar oscurecimiento
                        searchForm.classList.remove("expanded"); // Contraer barra
                        searchInput.blur(); // Quitar el foco del input
                    });
               });
            </script>
            
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <!-- Nav Item - Search Redirect (Visible Only on Small Screens) -->
                <li class="nav-item d-sm-none">
                    <a class="nav-link" href="buscar.php">
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


            <!-- Detalle Cliente -->
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

        </nav>
    </div>
    <!-- cabcera 3 -->
    <div class="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow categoriasCabecera" style="padding: 35px; min-height: 90px;">

            <!-- Navbar Categories -->
            <ul class="navbar-nav mx-auto cetegoriasGrupo">
                <li class="nav-item">
                    <a class="btn btn-categoria" href="clvideos_productos.php">
                        Videos
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <div style="margin-top: 8vh;">
    </div>
        <!-- fin cabecera 3 -->

    <!-- Main Content -->
<div class="container mt-5" style="margin-bottom: 80px; font-family: Poppins;">
    <h1 class="titulo text-center mb-4">Carrito de Compras</h1>
    <div class="table-responsive">
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lista_carrito == null) { ?>
                    <tr>
                        <td colspan="5" class="text-center"><b>Lista vacía</b></td>
                    </tr>
                <?php } else { 
                    $total = 0;
                    foreach ($lista_carrito as $producto) {
                        $codigo = $producto['codigo'];
                        $foto = $producto['foto'];
                        $descripcion = $producto['descripcion'];
                        $pventa = $producto['pventa'];
                        $cantidad = $producto['cantidad'];
                        $precio_desc = $pventa;
                        $subtotal = $cantidad * $precio_desc;
                        $total += $subtotal;

                        // Obtener la categoría del producto
                        $id_categoria_producto = $producto['id_categoria'];
                        $nombre_categoria = isset($categorias[$id_categoria_producto]) ? $categorias[$id_categoria_producto]['nombre_categoria'] : 'Categoría Desconocida';

                        // Construir la ruta de la imagen
                        $imagen = "../Admin/{$foto}";
                        if (!file_exists($imagen)) {
                            $imagen = "../images/nophoto.jpg";
                        }
                ?>
                    <tr>
                        <td>
                            <img src="<?php echo $imagen; ?>" alt="<?php echo $descripcion; ?>" width="80" height="80" class="me-4">
                            <?php echo $descripcion; ?>
                        </td>
                        <td><?php echo MONEDA . number_format($precio_desc, 2, '.', ','); ?></td>
                        <td>
                            <div class="d-flex justify-content-center align-items-center">
                            <button class="btn-cantidad me-2" onclick="actualizarCantidad(<?php echo $codigo; ?>, -1)">−</button>
                            <span id="cantidad_<?php echo $codigo; ?>" class="cantidad-box"><?php echo $cantidad; ?></span>
                            <button class="btn-cantidad ms-2" onclick="actualizarCantidad(<?php echo $codigo; ?>, 1)">+</button>

                            </div>
                        </td>
                        <td>
                            <div id="subtotal_<?php echo $codigo; ?>" name="subtotal[]">
                                <?php echo MONEDA . number_format($subtotal, 2, '.', ','); ?>
                            </div>
                        </td>
                        <td>
                            <a id="eliminar" class="btn btn-danger btn-sm" data-bs-id="<?php echo $codigo; ?>" data-bs-toggle="modal" data-bs-target="#eliminaModal">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="3"></td>
                    <td colspan="2" class="text-end">
                        <p class="mb-0 fs-5"><strong>Total a pagar:</strong></p>
                        <p class="mb-0 fw-bold fs-4" id="total">
                            <?php echo MONEDA . number_format($total, 2, '.', ','); ?>
                        </p>
                    </td>
                </tr>

            </tbody>
            <?php } ?>
        </table>
    </div>
</div>    

    <!-- Estilos para mejorar diseño -->
<style>
    .table {
        border-radius: 10px;
        overflow: hidden;
    }

    .table th, .table td {
        padding: 12px;
        vertical-align: middle;
    }

    .btn-cantidad {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 2px solid #5EBC50; /* Color fucsia de Auxilium Farma */
    background-color: white;
    color: #5EBC50;
    font-weight: bold;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-cantidad:hover {
    background-color: #5EBC50;
    color: white;
}

.cantidad-box {
    min-width: 40px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
}

</style>

    <div class="row mt-4">
    <div class="col-md-5 offset-md-7 d-grid gap-2">
        <form action="pago_cliente.php" method="post">
            <!-- Envía los productos del carrito como un array serializado -->
            <input type="hidden" name="productos_carrito" value="<?php echo htmlspecialchars(json_encode($lista_carrito)); ?>">
            <input type="hidden" name="total_pagar" value="<?php echo $total; ?>">
            <button type="submit" class="btn btn-success btn-lg" id="realizarPagoBtn">
                Realizar Pago
            </button>
        </form>
    </div>
</div>

<style>
    #realizarPagoBtn {
    width: 250px; /* Ajusta el ancho como necesites */
    display: block;
    margin: 0 auto; /* Centra el botón */
    background-color: #20aca9; /* Fucsia */
    border-color: #20aca9;
    color: white; /* Texto en blanco */
}

#realizarPagoBtn:hover {
    background-color: #5EBC50; /* Un fucsia más oscuro para el hover */
    border-color: #5EBC50;
}

</style>

    <!-- Modal Mejorado -->
<div class="modal fade" id="eliminaModal" tabindex="-1" role="dialog" aria-labelledby="eliminaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content custom-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="eliminaModalLabel">⚠ Alerta</h5>
                <button type="button" class="close btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea eliminar este producto de la lista?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
                <button id="btn-elimina" type="button" class="btn btn-danger" onclick="eliminar()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilo del modal */
.custom-modal {
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    background: #fff;
}

/* Modal Header */
.custom-modal .modal-header {
    background: #dc3545;
    color: white;
    border-top-left-radius: 12px;
    border-top-right-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px;
}

/* Botón de cerrar */
.custom-modal .btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: white;
    cursor: pointer;
}

/* Modal Body */
.custom-modal .modal-body {
    padding: 20px;
    font-size: 16px;
    text-align: center;
}

/* Modal Footer */
.custom-modal .modal-footer {
    border-top: none;
    padding: 15px;
    display: flex;
    justify-content: space-between;
}

/* Botones */
.custom-modal .btn-outline-secondary {
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: bold;
}

.custom-modal .btn-danger {
    border-radius: 8px;
    padding: 8px 15px;
    font-weight: bold;
}

</style>

<footer class="pie-pagina mt-5">
        <div class="grupo-1">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Delgado Electronic</h3>
                    <p>Tu tienda de confianza en tecnología y electrónicos</p>
                </div>
            </div>
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
    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>

   <!-- Tus scripts personalizados -->
    <script src="../js/cliente.js"></script>
    <script src="../js/sb-admin-2.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
    /* Eliminar producto */
    let eliminaModal = document.getElementById('eliminaModal');
    eliminaModal.addEventListener('show.bs.modal', function(event) {
        let button = event.relatedTarget;
        let id = button.getAttribute('data-bs-id');
        let buttonElimina = eliminaModal.querySelector('.modal-footer #btn-elimina');
        buttonElimina.value = id;
    });

    function actualizarCantidad(codigo, cambio) {
    let cantidadElemento = document.getElementById('cantidad_' + codigo);
    let cantidadActual = parseInt(cantidadElemento.innerText);

    // Verificar que la cantidad no sea menor a 1
    let nuevaCantidad = cantidadActual + cambio;
    if (nuevaCantidad < 1) {
        nuevaCantidad = 1;
    }

    cantidadElemento.innerText = nuevaCantidad;

    let url = '../Clases/actualizar_carrito.php';
    let formData = new FormData();
    formData.append('action', 'agregar');
    formData.append('codigo', codigo);
    formData.append('cantidad', nuevaCantidad);

    fetch(url, {
        method: 'POST',
        body: formData,
        mode: 'cors'
    }).then(response => response.json())
    .then(data => {
        if (data.ok) {
            let divsubtotal = document.getElementById('subtotal_' + codigo);
            divsubtotal.innerHTML = data.sub;

            // Recalcular el total
            recalcularTotal();
        }
    });
}


    /* Función para recalcular el total */
    function recalcularTotal() {
        let total = 0.00;
        let listaSubtotales = document.getElementsByName('subtotal[]');

        // Sumar todos los subtotales
        listaSubtotales.forEach(subtotal => {
            let monto = parseFloat(subtotal.innerText.replace(/[^\d.]/g, ''));
            total += isNaN(monto) ? 0 : monto;
        });

        // Formatear el total y actualizar en la página
        document.getElementById('total').innerText = 'S/ ' + total.toFixed(2);
    }

    /* Eliminar producto */
    function eliminar() {
        let botonElimina = document.getElementById('btn-elimina');
        let codigo = botonElimina.value;

        let url = '../Clases/actualizar_carrito.php';
        let formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('codigo', codigo);

        fetch(url, {
            method: 'POST',
            body: formData,
            mode: 'cors'
        }).then(response => response.json())
        .then(data => {
            if (data.ok) {
                location.reload();
            }
        });
    }

    // Hacer globales las funciones
    window.actualizarCantidad = actualizarCantidad;
    window.eliminar = eliminar;
});

    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/exit.js"></script>
</body>

</html>