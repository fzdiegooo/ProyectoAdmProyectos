<?php
/*
require_once '../php/database.php';
require_once '../php/repartidorfunciones.php';

$datos = [];

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        $db = new Database();
        $con = $db->conectar();

        if ($action == 'dniExiste') {
            $datos['ok'] = dniExiste($_POST['dni'], $con);
        } elseif ($action == 'emailExiste') {
            $datos['ok'] = emailExiste($_POST['correo'], $con);
        } elseif ($action == 'celularExiste') {
            $datos['ok'] = celularExiste($_POST['celular'], $con);
        } elseif ($action == 'placaExiste') {
            $datos['ok'] = placaExiste($_POST['placa'], $con);
        }elseif ($action == 'validarFechaNacimiento') {
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $datos['ok'] = validarFechaNacimiento($fecha_nacimiento);
            if (!$datos['ok']) {
                $datos['mensaje'] = 'La fecha no es válida';
            } else {
                $datos['mensaje'] = 'La fecha de nacimiento es válida.';
            }
        }
    }

// Devuelve los datos codificados en JSON
echo json_encode($datos);
*/
?>
