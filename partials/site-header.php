<?php
$num_cart = isset($num_cart) ? $num_cart : 0;
?>

<!-- Top Promotional Bar -->
<div class="top-bar">
    <div class="container-fluid">
        <div class="row">
            <div class="col text-center">
                <i class="fas fa-shipping-fast me-2"></i> Envío gratis en compras mayores a S/100
                <span class="ms-4"><i class="fas fa-phone me-2"></i> 945853331</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/logo-completo.png" alt="Delgado Electronic" class="d-none d-md-block me-2" style="height: 52px;">
            <img src="images/Icons/logo-icono.png" alt="Delgado Electronic" class="d-md-none me-2" style="height: 45px;">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <form class="d-flex mx-auto position-relative" style="max-width:400px; width:100%;" action="" method="post" autocomplete="off">
                <input class="form-control search-input" type="search" placeholder="Busca un producto..." aria-label="Buscar" name="campo" id="campo">
                <button class="btn search-btn" type="submit"><i class="fas fa-search"></i></button>
                <ul id="lista" class="list-group position-absolute w-100" style="top:100%; z-index:1050; display:none;"></ul>
            </form>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item">
                    <a href="videos_productos.php" class="nav-link d-flex align-items-center gap-1">
                        <i class="fas fa-video"></i>
                        <span>Videos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="carritodetalles.php" class="nav-link position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span id="num_cart" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                            <?php echo $num_cart; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-user-circle fa-lg me-1"></i> Iniciar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <form method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo electrónico..." required>
                    </div>
                    <div class="mb-3">
                        <label for="contrasena" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Contraseña..." required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="fa-solid fa-eye-slash"></i></button>
                        </div>
                    </div>
                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-custom">Iniciar Sesión</button>
                    </div>
                </form>
                <div class="text-center mt-2" style="font-family: Poppins;">
                    <p class="mb-1">¿No tienes cuenta? <span data-bs-toggle="modal" data-bs-target="#registerModal" class="registrate text-success" style="cursor:pointer;">Regístrate</span></p>
                    <p class="mb-0">¿Olvidaste tu contraseña? <a href="#" id="recoveryLink">Recuperar</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Manejo del formulario de login
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el enlace de recuperación de contraseña
    const recoveryLink = document.getElementById('recoveryLink');
    if (recoveryLink) {
        recoveryLink.addEventListener('click', function(e) {
            e.preventDefault();
            const pathParts = window.location.pathname.split('/');
            const projectIndex = pathParts.indexOf('ProyectoAdmProyectos');
            let basePath = '/';
            if (projectIndex !== -1) {
                basePath = '/' + pathParts.slice(1, projectIndex + 1).join('/') + '/';
            }
            window.location.href = basePath + 'Cliente/recovery.php';
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Obtener la ruta base del proyecto
            const pathParts = window.location.pathname.split('/');
            const projectIndex = pathParts.indexOf('ProyectoAdmProyectos');
            let basePath = '/';
            if (projectIndex !== -1) {
                basePath = '/' + pathParts.slice(1, projectIndex + 1).join('/') + '/';
            }
            
            fetch(basePath + 'php/login_usuario_be.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.requires_2fa) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = data.redirect;
                    }
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al iniciar sesión. Por favor, intente nuevamente.');
            });
        });
    }
});
</script>

<!-- Register Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="registerModalLabel">Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-0">
                <form id="registerForm">
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label for="dni_U" class="form-label">DNI</label>
                            <input type="text" class="form-control" id="dni_U" name="dni_U" placeholder="Ingrese su DNI..." required>
                            <span id="validaDni_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="nombres_U" class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="nombres_U" name="nombres_U" placeholder="Ingrese sus nombres..." required>
                            <span id="validaNombres_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="apellido_paterno_U" class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="apellido_paterno_U" name="apellido_paterno_U" placeholder="Ingrese su apellido paterno..." required>
                            <span id="validaApellidoPaterno_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="apellido_materno_U" class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="apellido_materno_U" name="apellido_materno_U" placeholder="Ingrese su apellido materno..." required>
                            <span id="validaApellidoMaterno_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="celular_U" class="form-label">Número de Celular</label>
                            <input type="text" class="form-control" id="celular_U" name="celular_U" placeholder="Ingrese su número de celular..." pattern="9[0-9]{8}" maxlength="9" required oninput="validarCelular(this)">
                            <span id="validaCelular_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="fecha_nacimiento_U" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento_U" name="fecha_nacimiento_U" required oninput="validarFechaNacimiento()">
                            <span id="validaFechadenacimiento_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="email_U" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" id="email_U" name="email_U" placeholder="Ingrese su correo electrónico..." required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="password_U" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_U" name="password_U" placeholder="Ingrese su contraseña..." required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordReg"><i class="fa-solid fa-eye-slash"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="confirmar_contrasena_U" class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar_contrasena_U" name="confirmar_contrasena_U" placeholder="Repita la contraseña..." required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordReg2"><i class="fa-solid fa-eye-slash"></i></button>
                            </div>
                            <span id="validaPassword_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="genero_U" class="form-label">Género</label>
                            <select class="form-select" id="genero_U" name="genero_U" required>
                                <option value="" disabled selected>Seleccionar Género</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="distrito_U" class="form-label">Distrito</label>
                            <input type="text" class="form-control" id="distrito_U" name="distrito_U" placeholder="Ingrese el distrito..." required>
                            <span id="validaDistrito_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="avenida_U" class="form-label">Avenida</label>
                            <input type="text" class="form-control" id="avenida_U" name="avenida_U" placeholder="Ingrese la avenida..." required>
                            <span id="validaAvenida_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="numero_U" class="form-label">Número</label>
                            <input type="text" class="form-control" id="numero_U" name="numero_U" placeholder="Ingrese número de casa..." required>
                            <span id="validaNumero_U" class="text-danger"></span>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="descripcion_U" class="form-label">Dpto/Interior/Piso/Lote/Bloque</label>
                            <input type="text" class="form-control" id="descripcion_U" name="descripcion_U" placeholder="Describa su casa..." required>
                            <span id="validaDescripcion_U" class="text-danger"></span>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-custom">Crear cuenta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('contrasena');
        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
                this.querySelector('i').classList.toggle('fa-eye-slash');
                this.querySelector('i').classList.toggle('fa-eye');
            });
        }

        const togglePasswordReg = document.getElementById('togglePasswordReg');
        const passwordInputReg = document.getElementById('password_U');
        if (togglePasswordReg && passwordInputReg) {
            togglePasswordReg.addEventListener('click', function() {
                const type = passwordInputReg.type === 'password' ? 'text' : 'password';
                passwordInputReg.type = type;
                this.querySelector('i').classList.toggle('fa-eye-slash');
                this.querySelector('i').classList.toggle('fa-eye');
            });
        }

        const togglePasswordReg2 = document.getElementById('togglePasswordReg2');
        const passwordInputReg2 = document.getElementById('confirmar_contrasena_U');
        if (togglePasswordReg2 && passwordInputReg2) {
            togglePasswordReg2.addEventListener('click', function() {
                const type = passwordInputReg2.type === 'password' ? 'text' : 'password';
                passwordInputReg2.type = type;
                this.querySelector('i').classList.toggle('fa-eye-slash');
                this.querySelector('i').classList.toggle('fa-eye');
            });
        }
    });
</script>
