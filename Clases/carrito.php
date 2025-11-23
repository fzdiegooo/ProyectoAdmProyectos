<?php

// Se incluyen los archivos de configuración necesarios
require '../Config/config.php';

// Se verifica si se recibieron los datos necesarios en la solicitud POST
if (isset($_POST["codigo"]) && isset($_POST['token'])) {

    // Se obtienen los datos de la solicitud
    $codigo = $_POST['codigo'];
    $token = $_POST['token'];

    // Se genera un token temporal para verificar la solicitud
    $token_tmp = hash_hmac('sha1', $codigo, KEY_TOKEN);

    // Se compara el token recibido con el token generado
    if ($token == $token_tmp) {
        // Si los tokens coinciden, se procede a agregar el producto al carrito

        // Verificar si ya existe el producto en el carrito
        if (isset($_SESSION['carrito']['productos'][$codigo])) {
            // Si el producto ya está en el carrito, se incrementa la cantidad
            $_SESSION['carrito']['productos'][$codigo] += 1;
        } else {
            // Si el producto no está en el carrito, se agrega con cantidad inicial de 1
            $_SESSION['carrito']['productos'][$codigo] = 1;
        }

        // Se prepara la respuesta JSON con el número total de productos en el carrito
        $datos['numero'] = count($_SESSION['carrito']['productos']);
        $datos['ok'] = true; // Indica que la operación fue exitosa
    } else {
        // Si los tokens no coinciden, se devuelve una respuesta indicando que la operación falló
        $datos['ok'] = false;
    }
} else {
    // Si no se recibieron los datos necesarios en la solicitud, se devuelve una respuesta indicando que la operación falló
    $datos['ok'] = false;
}

// Se devuelve la respuesta JSON al cliente
echo json_encode($datos);
?>
