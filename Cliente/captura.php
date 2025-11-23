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

$payment = $_GET['payment_id'];
$status = $_GET['status'];
$payment_type = $_GET['payment_type'];
$order_id = $_GET['merchant_order_id'];

$db = new Database();
$con = $db->conectar();

$productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;
$lista_carrito = array();

if ($productos != null) {
    foreach ($productos as $clave => $cantidad) {
        // Se debe asegurar que el producto exista antes de intentar obtener sus detalles
        $sql = $con->prepare("SELECT codigo, descripcion, pventa, descuento, $cantidad AS cantidad FROM productos WHERE codigo=?");
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

$total = 0;
foreach ($lista_carrito as $producto) {
    $codigo = $producto['codigo'];
    $descripcion = $producto['descripcion'];
    $pventa = $producto['pventa'];
    $descuento = $producto['descuento'];
    $cantidad = $producto['cantidad'];
    $precio_desc = $pventa - $descuento;
    $subtotal = $cantidad * $precio_desc;
    $total += $subtotal;
}

// Consulta SQL para seleccionar los datos del usuario utilizando el DNI
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, correo, direccion FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

// Asignar los datos del usuario a variables individuales
$direccion = $usuario['direccion'];
$correo = $usuario['correo'];

// Configurar la zona horaria a 'America/Lima'
date_default_timezone_set('America/Lima');

//Valores par agregar a la tabla de ventas
$id_transaccion = $_GET['payment_id'];
$fecha_nueva = date('Y-m-d H:i:s');
$status = $_GET['status'];
$email = $correo;
$id_cliente = $_SESSION['dni'];
$direccion_cliente = $direccion;
$estado = "Pendiente";


$sql = $con->prepare("INSERT INTO ventas (id_transaccion, fecha_venta, status, email, id_cliente, direccion_cliente, total_venta, estado) VALUES (?,?,?,?,?,?,?,?)");

$sql->execute([$id_transaccion, $fecha_nueva, $status, $email, $id_cliente, $direccion_cliente, $total, $estado]);
$id = $con->lastInsertId();

if ($id > 0) {

    $productos = isset($_SESSION['carrito']['productos']) ? $_SESSION['carrito']['productos'] : null;

    if ($productos != null) {
        foreach ($productos as $clave => $cantidad) {
            // Se debe asegurar que el producto exista antes de intentar obtener sus detalles
            $sql = $con->prepare("SELECT codigo, descripcion, pventa, descuento FROM productos WHERE codigo=?");
            $sql->execute([$clave]);
            $row_prod = $sql->fetch(PDO::FETCH_ASSOC);

            $pventa = $row_prod['pventa'];
            $descuento = $row_prod['descuento'];
            $precio_desc = $pventa - $descuento;

            $sql_insert = $con->prepare("INSERT INTO detalles_ventas (id_venta, id_producto, descripcion, precio, cantidad)
                VALUES (?,?,?,?,?)");

            $sql_insert->execute([$id, $clave, $row_prod['descripcion'], $precio_desc, $cantidad]);
        }
    }
    unset($_SESSION['carrito']);
}

header("Location: cliente-page.php");
exit(); // Asegurarse de que el script se detenga después de la redirección