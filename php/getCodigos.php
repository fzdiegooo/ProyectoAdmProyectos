<?php

require 'database.php';
require '../Config/config.php';

/*
$db = new Database();
$con = $db->conectar();

$campo = $_POST["campo"];

$sql = "SELECT codigo, descripcion FROM productos WHERE descripcion LIKE ? ORDER BY codigo ASC Limit 0, 10";

$query = $con->prepare($sql);
$query->execute([$campo . '%']);

$html = "";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $codigo = $row['codigo'];
    $token = hash_hmac('sha1', $codigo, KEY_TOKEN); // Generar el token HMAC
    $html .= "<li><a href='detalles.php?codigo=" . $codigo . "&token=" . $token . "' class='no-decoration'>" . $row["descripcion"] . "</a></li>";
}

echo json_encode($html, JSON_UNESCAPED_UNICODE);
*/



$db = new Database();
$con = $db->conectar();

$campo = $_POST["campo"];

$sql = "SELECT codigo, descripcion FROM productos WHERE descripcion LIKE ? AND estado = ? ORDER BY codigo ASC LIMIT 10";

$query = $con->prepare($sql);
$estado = 1; // Solo productos activos
$query->execute(['%'.$campo . '%',$estado]); // Agregado correctamente los comodines %

$html = "";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $codigo = $row['codigo'];
    $token = hash_hmac('sha1', $codigo, KEY_TOKEN); // Generar el token HMAC
    $html .= "<li><a href='detalles.php?codigo=" . $codigo . "&token=" . $token . "' class='no-decoration'>" . $row["descripcion"] . "</a></li>";
}

echo json_encode($html, JSON_UNESCAPED_UNICODE);

?>

