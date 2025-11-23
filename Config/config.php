<?php 
    //Se definen las constantes: Credenciales y configuraciones de pago
    
    #ID de los clientes para la API de pagos en línea, MercadoPago/Paypal
    define("CLIENT_ID","AcQUvNVsXeqtiFH2pfbuOPRw1MZZEexfVOLUgiDFdX3142j-Qy7Wh09ezG4mmQjv-_dxEAu16LLkz2VV");
    #Token de prueba de Mercado de pago/Paypal, usado para autenticación en la API
    define("TOKEN_MP","TEST-1846428242051896-022700-6e6c239af1a075541b74b8b9f41f1f48-1700233117");
    #Se define la moneda en la que se realizarán las transacciones
    define("CURRENCY","USD");
    #clave de seguridad
    define("KEY_TOKEN","AUX.LiL-143*");
    #Se define la moneda local
    define ("MONEDA","S/");

    //Inicio de sesion de usuario
    #Comprueba si la sesion no está activa
    if (session_status() == PHP_SESSION_NONE) {
        session_start();//Inicia la sesión
    }

    //Conteo de productos en el carrito
    $num_cart = 0;
    #Verifica si existe la sesion
    if(isset($_SESSION['carrito']['productos'])){
        #Se cuenta cuantos productos hay en el carrito de una determinada sesion
        $num_cart = count($_SESSION['carrito']['productos']);
    }
?>