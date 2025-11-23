<?php

//Cualquier cambio antes de esto XD
//Ya por ahora funciona con normalidad y es que eh quitado lo que es
// Las validaciones de dni, telefono y validar edad ya que hay js que manda 
//sobre esas validaciones

/*
Va verificar si hay valores vacíos
*/
function esNulo(array $paremetros)
{
    foreach ($paremetros as $parametro) {
        if (strlen(trim($parametro)) < 1) {
            return true;
        }
    }
    return false;
}

/*
Va validar con la función del php filtervalidateemail
va verificar si cumple con el formato correspondiente
*/
function esEmail($correo)
{
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        return true;
    }
    return false;
}
/*
Verifica si la fecha de nacimientos es válido
*/
function validarFechaNacimiento($fecha_nacimiento)
{
    try {
        // Intenta crear un objeto DateTime con la fecha de nacimiento proporcionada
        $fecha = new DateTime($fecha_nacimiento);

        // Verificar si la fecha de nacimiento está dentro del rango de 18 a 90 años
        $hoy = new DateTime();
        //Va hacer con la fecha ingresada menos la fecha de hoy y c
        //calculará la edad
        $edad = $hoy->diff($fecha)->y;

        return $fecha && $edad >= 18 && $edad <= 90;
    } catch (Exception $e) {
        return false; // La fecha no es válida
    }
}


/*
function validarEdad($fecha_nacimiento)
{
    // Crear objetos DateTime para la fecha actual y la fecha de nacimiento proporcionada
    $fecha_actual = new DateTime('now');
    $fecha_nacimiento = new DateTime($fecha_nacimiento);

    // Calcular la diferencia en años entre la fecha actual y la fecha de nacimiento
    $edad = $fecha_actual->diff($fecha_nacimiento)->y;

    // Verificar si la edad está dentro del rango permitido
    if ($edad >= 18 && $edad <= 100) {
        return true; // La edad es válida
    } else {
        return false; // La edad no es válida
    }
}
*/

/*
Va comparar si las contrasñas son iguales con sl strcmp de forma segura
*/
function validaPassword($password, $confirmar_contrasena)
{
    if (strcmp($password, $confirmar_contrasena) === 0) {
        return true;
    }
    return false;
}


function emailExiste($correo, $con)
{
    // Prepara la consulta para seleccionar el correo electrónico
    $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE correo = ?");
    // Ejecuta la consulta con el correo electrónico proporcionado como parámetro
    $sql->execute([$correo]);
    // Obtiene el resultado (número de filas donde el correo coincide)
    $resultado = $sql->fetchColumn();

    // Si el resultado es mayor que cero, significa que el correo ya existe
    if ($resultado > 0) {
        return true;
    } else {
        return false;
    }
}
/*
function dniExiste($dni, $con)
{
    // Verificar si el DNI tiene exactamente 8 caracteres numéricos
    if (!preg_match('/^[0-9]{8}$/', $dni)) {
        return "El DNI debe contener exactamente 8 dígitos.";
    } else {    // Prepara la consulta para seleccionar el DNI
        $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = ?");
        // Ejecuta la consulta con el DNI proporcionado como parámetro
        $sql->execute([$dni]);
        // Obtiene el resultado (número de filas donde el DNI coincide)
        $resultado = $sql->fetchColumn();
    }
    // Si el resultado es mayor que cero, significa que el DNI ya existe
    if ($resultado > 0) {
        return true;
    } else {
        return false;
    }
}
*/

function dniExiste($dni, $con)
{
    // Solo verificamos en la base de datos si ya existe el DNI
    $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE dni = ?");
    $sql->execute([$dni]);
    $resultado = $sql->fetchColumn();

    return $resultado > 0;
}

/*
function celularExiste($celular, $con)
{
    // Verifica si el celular contiene solo dígitos y tiene una longitud de 9 caracteres
    if (!preg_match('/^[0-9]{9}$/', $celular)) {
        return "El número de celular debe contener exactamente 9 dígitos.";
    } else {

        // Prepara la consulta para seleccionar el número de celular
        $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE celular = ?");
        // Ejecuta la consulta con el número de celular proporcionado como parámetro
        $sql->execute([$celular]);
        // Obtiene el resultado (número de filas donde el número de celular coincide)
        $resultado = $sql->fetchColumn();
    }
    // Si el resultado es mayor que cero, significa que el número de celular ya existe
    if ($resultado > 0) {
        return true;
    } else {
        return false;
    }
}
*/
function celularExiste($celular, $con){

     // Prepara la consulta para seleccionar el número de celular
     $sql = $con->prepare("SELECT COUNT(*) FROM usuarios WHERE celular = ?");
     // Ejecuta la consulta con el número de celular proporcionado como parámetro
     $sql->execute([$celular]);
     // Obtiene el resultado (número de filas donde el número de celular coincide)
     $resultado = $sql->fetchColumn();

     // Si el resultado es mayor que cero, significa que el número de celular ya existe
    if ($resultado > 0) {
        return true;
    } else {
        return false;
    }
}

/*
Va generar un token 
*/
function generarToken()
{
    return md5(uniqid(mt_rand(), false));
}

/*
De esta función es donde landding-page llama para registrar
*/
function registrarUsuario(array $datos, $con)
{
    $sql = $con->prepare("INSERT INTO usuarios(dni, nombres, apellido_paterno, apellido_materno, celular, fecha_nacimiento, correo, contrasena, genero, direccion, token) 
    VALUES (?,?,?,?,?,?,?,?,?,?,?)");

    if (!$sql->execute($datos)) {
        print_r($sql->errorInfo()); // Muestra los errores de SQL
    }
}

/*
Va recibir los errores del array
*/
/*
function mostrarMensajes(array $errors)
{
    // Si el error está
    if (count($errors) > 0) {
        echo '<div class="alert alert-warning alert-dismissible fade show" role="alert"><ul>';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '<ul>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    }
}
*/
?>