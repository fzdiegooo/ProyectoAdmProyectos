<?php
require 'php/clientesfunciones.php';
echo "Contrasena: ";
echo password_hash("delgado123", PASSWORD_DEFAULT);//Reemplazar lo que esta  en comillas
//GENERA un token que se puede usar para el usuario
$token = generarToken();
echo "<br> token: ";
echo $token;

?>