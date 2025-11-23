<?php

require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

$json = file_get_contents('php://input');
$datos = json_decode($json, true);

print_r($datos);

if (is_array($datos)) {

    $id_transaccion = $datos['detalles']['id'];
    $total = $datos['detalles']['purchase_units'][0]['amount']['value'];
    $status = $datos['detalles']['status'];
    $fecha = $datos['detalles']['update_time'];
    $fecha_nueva = date('Y-m-d H:i:s', strtotime($fecha));
    $email = $datos['detalles']['payer']['email_address'];
    $id_cliente = $datos['detalles']['payer']['payer_id'];

    $sql = $con->prepare("INSERT INTO ventas (id_transaccion, fecha_venta, status, email, id_cliente, total_venta) VALUES (?,?,?,?,?,?)");

    $sql->execute([$id_transaccion, $fecha_nueva, $status, $email, $id_cliente, $total]);
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
}
