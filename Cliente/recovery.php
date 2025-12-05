<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/correo.css">
</head>

<body>
    <div class="container">
        <br>
        <h2 class="text-center">Recuperar Contraseña</h2>
        <p class="text-center">Introduce tu correo electrónico y te enviaremos las instrucciones para recuperar tu contraseña.</p>

        <?php
        if (isset($_GET['alert'])) {
            if ($_GET['alert'] == 1) {
                echo "<div class='alert alert-success' role='alert'>
                El formulario ha sido enviado a tu correo.
              </div>";
            } elseif ($_GET['alert'] == 2) {
                echo "<div class='alert alert-danger' role='alert'>
                Ocurrió un error al enviar el correo. Por favor, intenta nuevamente.
              </div>";
            } elseif ($_GET['alert'] == 3) {
                echo "<div class='alert alert-warning' role='alert'>
                El correo electrónico no está registrado.
              </div>";
            } 
        }
        ?>

        <form action="recoverypassword.php" method="POST">
            <div class="mb-3">
                <label id="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" placeholder="nombre@ejemplo.com" required>
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-custom">Recuperar</button>
            </div>
            <div class="text-center mt-3">
                <a href="../index.php">Volver al inicio</a>
            </div>
        </form>
    </div>
</body>

</html>
