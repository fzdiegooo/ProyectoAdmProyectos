<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="../css/contraseña.css" />
</head>

<body>
    <div class="container">
        <h2 class="text-center">Recuperar Contraseña</h2>
        <p class="text-center">Introduce tu nueva contraseña.</p>

        <?php
        if (isset($_GET['alert']) && $_GET['alert'] == 2) {
            echo "<div class='alert alert-danger' role='alert'>
            Ocurrió un error al actualizar la contraseña. Intenta nuevamente.
            </div>";
        }
        ?>
        <!-- formulario para el cambio de contraseña -->
        <form action="../Cliente/resetpassword.php" method="POST">
            <div class="mb-3 password-container">
                <label for="email" class="form-label">Nueva Contraseña</label>
                <input type="password" class="form-control" id="newclave" name="newclave" required>
                <input type="hidden" name="dni" value="<?php echo $_GET['dni']; ?>">
                <i class="fa fa-eye eye-icon" id="togglePassword"></i>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-custom">Recuperar</button>
            </div>

            <div class="text-center mt-3">
                <a href="http://localhost/ProyectoCapstone/Cliente/cliente-page.php">Volver al inicio de sesión</a>
            </div>
        </form>
    </div>

    <script>
        //Funcion que permite observar la contraseña ingresada
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("newclave");

        togglePassword.addEventListener("click", function () {
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;

            if (type === "password") {
                togglePassword.classList.remove("fa-eye-slash");
                togglePassword.classList.add("fa-eye");
            } else {
                togglePassword.classList.remove("fa-eye");
                togglePassword.classList.add("fa-eye-slash");
            }
        });
    </script>
</body>

</html>
