<?php 
/*
    include 'database.php';

    $dni = $_POST['dni'];
    $nombres = $_POST['nombres'];
    $apellido_paterno = $_POST['apellido_paterno'];
    $apellido_materno = $_POST['apellido_materno'];
    $celular = $_POST['celular'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $correo = $_POST['correo'];

    $contrasena = $_POST['contrasena'];
    //encriptamiento de contraseña - investigar más metodos para que al encriptar la encriptacion sea diferente en cada contra
    $contrasena = hash('sha512', $contrasena);

    $genero = $_POST['genero'];


    $query = "INSERT INTO usuarios(dni, nombres, apellido_paterno, apellido_materno, celular, fecha_nacimiento, correo, contrasena, genero)
                VALUES('$dni', '$nombres', '$apellido_paterno', '$apellido_materno', '$celular', '$fecha_nacimiento', '$correo', '$contrasena', '$genero')";


    //verificar que el dni no se repita en la base de datos
    $verificar_dni = mysqli_query($conexion, "SELECT * FROM usuarios WHERE dni = '$dni' ");

    if(mysqli_num_rows($verificar_dni) > 0){
        echo '
            <script>
                alert("El dni ya está registrado, intentelo nuevamente");
                window.location = "../landing-page.php";
            </script>
        ';
        exit();
        mysqli_close($conexion);
    }

    //verificar que el correo no se repita en la base de datos
    $verificar_correo = mysqli_query($conexion, "SELECT * FROM usuarios WHERE correo = '$correo' ");

    if(mysqli_num_rows($verificar_correo) > 0){
        echo '
            <script>
                alert("Este correo ya está registrado, intenta con otro diferente");
                window.location = "../landing-page.php";
            </script>
        ';
        exit();
        mysqli_close($conexion);
    }

    $ejecutar = mysqli_query($conexion, $query);

    if ($ejecutar) {
        echo '
            <script>
                alert("Usuario alamacenado exitosamente");
                window.location = "../cliente-page.php";
            </script>
        ';
    } else {
        echo '
            <script>
                alert("Intentelo de nuevo, usuario no alamacenado");
                window.location = "../landing-page.php";
            </script>
        ';
    }

    mysqli_close($conexion);*/
?>

