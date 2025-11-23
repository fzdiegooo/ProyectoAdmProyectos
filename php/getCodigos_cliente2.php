<?php

require '../php/database.php';
require '../Config/config.php';


$db = new Database();
$con = $db->conectar();

$campo = $_POST["campo"];
//Se crea la sentencia correspondiente en la busqueda de los productos mediante la descripción
$sql = "SELECT codigo, descripcion FROM productos WHERE descripcion LIKE ? AND estado = ? ORDER BY codigo ASC LIMIT 10";

$query = $con->prepare($sql);
$estado = 1; // Solo productos activos
$query->execute(['%'.$campo . '%',$estado]); // Permite buscar el campo ubicado en cualquier parte del contenido (descripción del producto/ nombre del producto)

$html = "";
while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $codigo = $row['codigo'];
    $token = hash_hmac('sha1', $codigo, KEY_TOKEN); // Generar el token HMAC
    $html .= "<li><a href='Cliente/detalles_cliente.php?codigo=" . $codigo . "&token=" . $token . "' class='no-decoration'>" . $row["descripcion"] . "</a></li>";
}

echo json_encode($html, JSON_UNESCAPED_UNICODE);

?>