<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Admin/admin-page.php");
        exit();
    } else {
        header("Cliente/cliente-page.php");
        exit();
    }
}

require 'Config/config.php';
require 'php/database.php';
require 'php/clientesfunciones.php';

$db = new Database();
$con = $db->conectar();

$errors = [];

if (!empty($_POST)) {
    $dni = trim($_POST['dni']);
    $nombres = trim($_POST['nombres']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $celular = trim($_POST['celular']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $correo = trim($_POST['email']);
    $contrasena = trim($_POST['password']);
    $genero = isset($_POST['genero']) ? $_POST['genero'] : '';

    $token = generarToken();

    if (esNulo([$dni, $nombres, $apellido_paterno, $apellido_materno, $celular, $fecha_nacimiento, $correo, $contrasena, $genero])) {
        $errors[] = "Debe llenar todos los campos";
    }
    if (dniExiste($dni, $con)) {
        $errors[] = "El DNI ya está registrado";
    }

    if (!esEmail($correo)) {
        $errors[] = "La direccion de correo electronico no es valida";
    }

    if (validarFechaNacimiento($fecha_nacimiento)) {
        if (!validarEdad($fecha_nacimiento)) {
            $errors[] = "Debes tener al menos 18 años";
        }
    } else {
        $errors[] = "La fecha de nacimiento proporcionada no es válida.";
    }

    if (!validaPassword($contrasena, $confirmarcontrasena)) {
        $errors[] = "Las contraseñas no coinciden";
    }
    if (emailExiste($correo, $con)) {
        $errors[] = "El correo electronico $correo ya existe";
    }

    if (count($errors) == 0) {
        $contrasena = hash('sha512', $contrasena);
        registrarUsuario([$dni, $nombres, $apellido_paterno, $apellido_materno, $celular, $fecha_nacimiento, $correo, $contrasena, $genero, $token], $con);
        echo '<script>alert("Usuario almacenado exitosamente");</script>';
    } else {
        echo '<script>alert("Error al registrar usuario");</script>';
    }
}

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

$lista_carrito = array();

if ($productos != null) {
    foreach ($productos as $clave => $cantidad) {
        $sql = $con->prepare("SELECT codigo, foto, descripcion, pventa, id_categoria, $cantidad AS cantidad FROM productos WHERE codigo=?");
        $sql->execute([$clave]);
        $producto = $sql->fetch(PDO::FETCH_ASSOC);
        if ($producto) {
            $lista_carrito[] = $producto;
        }
    }
}

$categorias = array();

$consultaCategorias = "SELECT * FROM categorias";
$resultadoCategorias = $con->prepare($consultaCategorias);
$resultadoCategorias->execute();
$categoriasData = $resultadoCategorias->fetchAll(PDO::FETCH_ASSOC);

foreach ($categoriasData as $categoria) {
    $categorias[$categoria['id_categoria']] = $categoria;
}

$sqlProductosCategoria = $con->prepare("
                SELECT codigo, id_categoria, foto, descripcion, stock, pventa
                FROM productos 
                WHERE id_categoria = :id_categoria
            ");
$sqlProductosCategoria->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
$sqlProductosCategoria->execute();
$productosCategoria = $sqlProductosCategoria->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Delgado Electronic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/landing.css" />
    <link rel="stylesheet" href="css/sb-admin-2.css" />
    <link rel="stylesheet" href="css/Footer.css" />
    <link rel="stylesheet" href="css/cabeceras.css" />
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f8fafc; }
        .top-bar { background: linear-gradient(90deg, #5EBC50 0%, #489c3a 100%); color: #fff; padding: 8px 0; font-size: 0.9rem; }
        .back-button { background: #5EBC50; color: #fff; border: none; border-radius: 30px; padding: 10px 24px; font-weight: 600; margin-bottom: 20px; }
        .back-button:hover { background: #489c3a; color: #fff; transform: translateY(-2px); }
        .cart-table { background: #fff; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .cart-table thead { background: linear-gradient(135deg, #5EBC50 0%, #489c3a 100%); color: #fff; }
        .cart-table th { font-weight: 600; padding: 14px; font-size: 0.95rem; }
        .cart-table td { padding: 14px; vertical-align: middle; font-size: 0.9rem; }
        .product-img-cart { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }
        .btn-cantidad { width: 30px; height: 30px; border-radius: 50%; border: 2px solid #5EBC50; background: #fff; color: #5EBC50; font-weight: bold; transition: all 0.3s; font-size: 0.9rem; }
        .btn-cantidad:hover { background: #5EBC50; color: #fff; }
        .cantidad-box { min-width: 40px; text-align: center; font-size: 1rem; font-weight: 600; }
        .total-section { background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .btn-pagar { background: linear-gradient(135deg, #5EBC50 0%, #489c3a 100%); color: #fff; border: none; border-radius: 30px; padding: 14px 40px; font-size: 1.1rem; font-weight: 700; }
        .btn-pagar:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(94,188,80,0.3); }
        .empty-cart { text-align: center; padding: 60px 20px; }
        .empty-cart i { font-size: 5rem; color: #ccc; margin-bottom: 20px; }
    </style>
</head>



<body class="fondo" id="page-top">

    <?php include 'partials/site-header.php'; ?>

    <div style="margin-top: 8vh;">
    </div>

    <!-- Main Content -->
<div class="container mt-5" style="margin-bottom: 80px;">
    <a href="index.php" class="btn back-button">
        <i class="fas fa-arrow-left me-2"></i> Volver a la tienda
    </a>
    <h1 class="text-center mb-4" style="font-size: 1.8rem; font-weight: 700; color: #222;">Mi Carrito de Compras</h1>    
    <div class="table-responsive">
        <table class="table cart-table text-center">
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
                        $imagen = "Admin/{$foto}";
                        if (!file_exists($imagen)) {
                            $imagen = "images/nophoto.jpg";
                        }
                ?>
                    <tr>
                        <td>
                            <img src="<?php echo $imagen; ?>" alt="<?php echo $descripcion; ?>" class="product-img-cart me-3">
                            <span style="font-size: 0.9rem;"><?php echo $descripcion; ?></span>
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
                        <p class="mb-0" style="font-size: 1rem;"><strong>Total a pagar:</strong></p>
                        <p class="mb-0 fw-bold" style="font-size: 1.5rem; color: #5EBC50;" id="total">
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
            <button id="realizarPagoBtn" type="button" class="btn btn-success btn-lg    ">Realizar Pago</button>
        </div>
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

<!-- Footer -->
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
    <script src="vendor/jquery/jquery.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

   <!-- Tus scripts personalizados -->
    <script src="js/landing.js"></script>
    <script src="js/sb-admin-2.js"></script>




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

    let url = 'Clases/actualizar_carrito.php';
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

        let url = 'Clases/actualizar_carrito.php';
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
    <script src="js/validacionLogin.js"></script>
    <script src="js/validarRegistro.js"></script>
</body>

</html>