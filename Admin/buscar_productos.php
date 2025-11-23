<?php /*
require '../Config/config.php';
require '../php/database.php';

$db = new Database();
$con = $db->conectar();

if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $busqueda = "%" . htmlspecialchars($query, ENT_QUOTES, 'UTF-8') . "%";

    $sql = $con->prepare("SELECT codigo, descripcion, pventa FROM productos WHERE descripcion LIKE ? OR codigo LIKE ?");
    $sql->execute([$busqueda, $busqueda]);

    $productos = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (count($productos) > 0) {
        foreach ($productos as $producto) {
            echo "
            <tr>
                <td>{$producto['codigo']}</td>
                <td>{$producto['descripcion']}</td>
                <td>{$producto['pventa']}</td>
                <td>
                    <button class='btn btn-primary btn-sm'>Editar</button>
                    <button class='btn btn-danger btn-sm'>Eliminar</button>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='text-center'>No se encontraron productos</td></tr>";
    }
    exit();
}
    */
?>