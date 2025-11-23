<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    // Si ya hay una sesión iniciada, redirige al usuario según su tipo
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Location: Admin/admin-page.php");
        exit();
    } else {
        header("Location: Cliente/cliente-page.php");
        exit();
    }
}

require 'Config/config.php';
require 'php/database.php';
require 'php/clientesfunciones.php';

$db = new Database();
$con = $db->conectar();

$codigo = isset($_GET['codigo']) ? $_GET['codigo'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($codigo == '' || $token == '') {
    echo '<div class="alert alert-danger">Error al procesar la petición</div>';
    exit;
} else {
    $token_tmp = hash_hmac('sha1', $codigo, KEY_TOKEN);
    if ($token == $token_tmp) {
        // Verificamos si el producto existe
        $sql = $con->prepare("SELECT count(codigo) FROM productos WHERE codigo=? AND estado=1");
        $sql->execute([$codigo]);
        if ($sql->fetchColumn() > 0) {
            // Obtenemos detalles del producto - solo campos que existen en la BD
            $sql = $con->prepare("SELECT codigo, foto, id_categoria, id_marca, id_proveedor, descripcion, stock, pventa, desc_web, estado FROM productos WHERE codigo=? AND estado=1 LIMIT 1");
            $sql->execute([$codigo]);
            $row = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                echo '<div class="alert alert-danger">Producto no encontrado</div>';
                exit;
            }

            // Asignamos los datos del producto a variables
            $descripcion = $row['descripcion'];
            $foto = $row['foto'];
            $stock = $row['stock'];
            $pventa = $row['pventa'];
            $desc_web = $row['desc_web'] ?? 'Información no disponible';
            $id_categoria = $row['id_categoria'];
            $id_marca = $row['id_marca'];
            $id_proveedor = $row['id_proveedor'];
            
            // Campos que no existen en la BD - valores por defecto
            $unimed = 'Unidad';
            $linea = 'Línea estándar';
            $descuento = 0;
            $precio_desc = $pventa;
            $modouso_web = 'Consulte las instrucciones del fabricante';
            $comp_web = 'Información de composición no disponible';
            $contraindicacion_web = 'Consulte con un especialista';
            $advertencia_web = 'Lea las instrucciones antes de usar';

            // Obtener información de marca si existe
            $nombre_marca = 'Sin marca';
            if ($id_marca) {
                $sqlMarca = $con->prepare("SELECT nombre_marca FROM marcas WHERE id_marca = ?");
                $sqlMarca->execute([$id_marca]);
                $marca = $sqlMarca->fetch(PDO::FETCH_ASSOC);
                if ($marca) {
                    $nombre_marca = $marca['nombre_marca'];
                }
            }

            // Productos similares (misma categoría)
            $sqlProductosCategoria = $con->prepare("
                SELECT codigo, id_categoria, foto, descripcion, stock, pventa
                FROM productos 
                WHERE id_categoria = :id_categoria AND codigo <> :codigo_actual AND estado = 1
                LIMIT 8
            ");
            $sqlProductosCategoria->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
            $sqlProductosCategoria->bindParam(':codigo_actual', $codigo, PDO::PARAM_STR);
            $sqlProductosCategoria->execute();
            $productosCategoria = $sqlProductosCategoria->fetchAll(PDO::FETCH_ASSOC);

        } else {
            echo '<div class="alert alert-danger">Producto no encontrado</div>';
            exit;
        }
    } else {
        echo '<div class="alert alert-danger">Error al procesar la petición</div>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($descripcion); ?> - Delgado Electronic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/landing.css" />
    <link rel="stylesheet" href="css/sb-admin-2.css" />
    <link rel="stylesheet" href="css/chatbot.css" />
    <link rel="stylesheet" href="css/Footer.css" />
    <link rel="stylesheet" href="css/cabeceras.css" />
    
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; }
        .product-image-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            height: 450px;
            border: none;
            background-color: #fff;
            border-radius: 20px;
            margin: 0 auto;
            cursor: zoom-in;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .product-image-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .product-image-container:hover img {
            transform: scale(1.1);
        }

        .product-image-container.zoomed {
            cursor: zoom-out;
        }

        .product-image-container.zoomed img {
            transform: scale(2);
            cursor: grab;
        }

        .product-info {
            padding: 20px;
        }

        .product-title {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 20px;
        }

        .product-price.discounted {
            color: #dc3545;
        }

        .original-price {
            font-size: 1.5rem;
            color: #6c757d;
            text-decoration: line-through;
            margin-right: 10px;
        }

        .stock-info {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .stock-available {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .stock-low {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .stock-out {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-add-cart {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 10px;
        }

        .btn-add-cart:hover {
            background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .btn-buy-now {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 50px;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-buy-now:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
        }

        .btn-disabled {
            background-color: #6c757d;
            color: white;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 50px;
            width: 100%;
            cursor: not-allowed;
        }

        .product-details-section {
            margin-top: 50px;
        }

        .similar-products {
            margin-top: 50px;
        }

        .similar-product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .similar-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .similar-product-card img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .brand-badge {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 10px;
        }

        @media (max-width: 768px) {
            .product-title {
                font-size: 1.5rem;
            }
            
            .product-price {
                font-size: 2rem;
            }
            
            .product-image-container {
                height: 300px;
            }
        }
    </style>
</head>

<body class="fondo" id="page-top" style="font-family: 'Poppins', Arial, sans-serif; background: #f8fafc;">
    <!-- Top Promotional Bar -->
    <div style="background: linear-gradient(90deg, #5EBC50 0%, #489c3a 100%); color: #fff; padding: 8px 0; font-size: 0.9rem;">
        <div class="container-fluid">
            <div class="row">
                <div class="col text-center">
                    <i class="fas fa-shipping-fast me-2"></i> Envío gratis en compras mayores a S/100
                    <span class="ms-4"><i class="fas fa-phone me-2"></i> 945853331</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Navbar/Header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 shadow-sm sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="images/logo-completo.png" alt="Delgado Electronic" class="d-none d-md-block me-2" style="height: 52px;">
                <img src="images/Icons/logo-icono.png" alt="Delgado Electronic" class="d-md-none me-2" style="height: 52px;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNavbar">
                <form class="d-flex mx-auto" style="max-width:400px; width:100%;" action="" method="post" autocomplete="off">
                    <input class="form-control" type="search" placeholder="Busca un producto..." aria-label="Buscar" name="campo" id="campo" style="border-radius: 30px 0 0 30px; border-right: 0; padding: 10px 18px;">
                    <button class="btn" type="submit" style="border-radius: 0 30px 30px 0; background: #5EBC50; color: #fff; border: none; padding: 10px 20px;"><i class="fas fa-search"></i></button>
                </form>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <a href="carritodetalles.php" class="nav-link position-relative">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span id="num_cart" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success"><?php echo $num_cart; ?></span>
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



    <!--MODAL LOGIN-->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title" id="loginModalLabel">Iniciar Sesión</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body pt-0">
            <form action="php/login_usuario_be.php" method="POST" id="loginForm">
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
                <button type="submit" class="btn" style="background: #5EBC50; color: #fff; border-radius: 30px; padding: 8px 24px; font-weight: 600;">Iniciar Sesión</button>
              </div>
            </form>
            <div class="text-center mt-2" style="font-family: Poppins;">
              <p class="mb-1">¿No tienes cuenta? <span data-bs-toggle="modal" data-bs-target="#registerModal" class="text-success" style="cursor:pointer;">Regístrate</span></p>
              <p class="mb-0">¿Olvidaste tu contraseña? <a href="Cliente/recovery.php">Recuperar</a></p>
            </div>
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
      });
    </script>


    <!-- Modal de Registro -->
    <!-- Modal de Registro -->
    <div id="registerModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Registro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario de registro -->
                    <form id="registerForm">
                        <div class="row separador">
                            <div class="col-md-6">
                                <label for="dni_U">DNI</label>
                                <input type="text" class="form-control" id="dni_U" name="dni_U" placeholder="Ingrese su DNI..." required>
                                <span id="validaDni_U" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="nombres_U">Nombres</label>
                                <input type="text" class="form-control" id="nombres_U" name="nombres_U" placeholder="Ingrese sus nombres..." required>
                                <span id="validaNombres_U" class="text-danger"></span>
                            </div>

                        </div>
                        <div class="row separador">
                            <div class="col-md-6">
                                <label for="apellido_paterno_U">Apellido Paterno</label>
                                <input type="text" class="form-control" id="apellido_paterno_U" name="apellido_paterno_U" placeholder="Ingrese su apellido paterno..." required>
                                <span id="validaApellidoPaterno_U" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="apellido_materno_U">Apellido Materno</label>
                                <input type="text" class="form-control" id="apellido_materno_U" name="apellido_materno_U" placeholder="Ingrese su apellido materno..." required>
                                <span id="validaApellidoMaterno_U" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6">
                                <label for="celular_U">Número de Celular</label>
                                <input type="text" class="form-control" id="celular_U" name="celular_U" placeholder="Ingrese su número de celular..." 
                                pattern="9[0-9]{8}" maxlength="9" required 
                                oninput="validarCelular(this)">
                                <span id="validaCelular_U" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="fecha_nacimiento_U">Fecha de Nacimiento</label>
                                <input type="date" class="form-control" id="fecha_nacimiento_U" name="fecha_nacimiento_U" required 
                                    oninput="validarFechaNacimiento()">
                                <span id="validaFechadenacimiento_U" class="text-danger"></span>
                            </div>
                        </div>
                        <div class="row separador">
                            <div class="col-md-6 position-relative">
                                <label for="email_U">Correo Electrónico</label>
                                <input type="email" class="form-control  w-100" id="email_U" name="email_U" placeholder="Ingrese su correo electrónico..." required>
                                <!--<span id="validaEmail_U" class="text-danger"></span>-->
                            </div>
                            <div class="col-md-6 position-relative">
                                <label for="password_U">Contraseña</label>
                                <input type="password" class="form-control" id="password_U" name="password_U" placeholder="Ingrese su contraseña..." required>
                                <i class="fa-solid fa-eye " id="verPassword" style="display: none;"></i>
                                <i class="fa-solid fa-eye-slash " id="ocultarPassword"></i>
                            </div>
                        </div>
                        <div class="row separador">
                        <span id="validaEmail_U" class="text-danger"></span>
                            <div class="col-md-6 position-relative">
                                <div class="password-icons">
                                    <label for="confirmar_contrasena_U">Confirmar contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_contrasena_U" name="confirmar_contrasena_U" placeholder="Repita la contraseña..." required>
                                    <div class="password-icons">
                                        <i class="fa-solid fa-eye" id="mostIcon" style="display: none;"></i>
                                        <i class="fa-solid fa-eye-slash" id="oculIcon"></i>
                                    </div>
                                </div>
                                <span id="validaPassword_U" class="text-danger"></span>
                            </div>
                            <div class="col-md-6">
                                <label for="genero_U">Género</label><br>
                                <select class="register-comboBox" id="genero_U" name="genero_U" required>
                                    <option value="" disabled selected>Seleccionar Género</option>
                                    <option value="masculino">Masculino</option>
                                    <option value="femenino">Femenino</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="row separador">
                                <div class="col-md-6 position-relative">
                                    <label for="distrito_U">Distrito</label>
                                    <input type="text" class="form-control" id="distrito_U" name="distrito_U" placeholder="Ingrese el distrito..." required>
                                    <span id="validaDistrito_U" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="avenida_U">Avenida</label><br>
                                    <input type="text" class="form-control" id="avenida_U" name="avenida_U" placeholder="Ingrese la avenida..." required>
                                    <span id="validaAvenida_U" class="text-danger"></span>
                                </div>
                            </div>
                            <div class="row separador">
                                <div class="col-md-6 position-relative">
                                    <label for="numero_U">Numero</label>
                                    <input type="text" class="form-control" id="numero_U" name="numero_U" placeholder="Ingrese numero de casa..." required>
                                    <span id="validaNumero_U" class="text-danger"></span>
                                </div>
                                <div class="col-md-6">
                                    <label for="descripcion_U">Dpto/Interior/Piso/Lote/Bloque</label><br>
                                    <input type="text" class="form-control" id="descripcion_U" name="descripcion_U" placeholder="Describa su casa..." required>
                                    <span id="validaDescripcion_U" class="text-danger"></span>
                                </div>
                                <!-- Puedes agregar más campos aquí -->
                            </div>
                        </div>
                        <button type="submit" class="button-register ">Crear cuenta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Contenido principal -->
    <div class="container" style="margin-top: 30px;">
        <!-- Información del producto -->
        <div class="row">
            <div class="col-md-6">
                <div class="product-image-container" onclick="toggleZoom(this)" onmousemove="moveZoom(event, this)">
                    <?php
                    $directorioImagenes = "Admin/";
                    $imagenBD = $row['foto'];

                    if (empty($imagenBD)) {
                        $imagen = "images/nophoto.jpg";
                    } else {
                        $imagen = $directorioImagenes . $imagenBD;
                        if (!file_exists($imagen)) {
                            $imagen = "images/nophoto.jpg";
                        }
                    }
                    ?>
                    <img src="<?= $imagen ?>" alt="<?php echo htmlspecialchars($descripcion); ?>" class="img-fluid">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($descripcion); ?></h1>
                    
                    <?php if ($nombre_marca != 'Sin marca'): ?>
                    <div class="brand-badge">
                        <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($nombre_marca); ?>
                    </div>
                    <?php endif; ?>
                    
                    <p class="text-muted mb-3"><strong>Código:</strong> <?php echo htmlspecialchars($codigo); ?></p>

                    <!-- Precio -->
                    <div class="mb-3">
                        <?php if ($descuento > 0): ?>
                            <span class="original-price"><?php echo MONEDA . number_format($pventa, 2); ?></span>
                            <span class="product-price discounted"><?php echo MONEDA . number_format($precio_desc, 2); ?></span>
                            <span class="badge bg-danger ms-2">-<?php echo round(($descuento/$pventa)*100); ?>%</span>
                        <?php else: ?>
                            <span class="product-price"><?php echo MONEDA . number_format($pventa, 2); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Stock -->
                    <div class="stock-info <?php 
                        if ($stock <= 0) echo 'stock-out';
                        elseif ($stock <= 5) echo 'stock-low';
                        else echo 'stock-available';
                    ?>">
                        <i class="fas fa-box"></i>
                        <?php 
                        if ($stock <= 0) {
                            echo 'Producto agotado';
                        } elseif ($stock <= 5) {
                            echo "¡Últimas {$stock} unidades disponibles!";
                        } else {
                            echo "{$stock} unidades disponibles";
                        }
                        ?>
                    </div>

                    <!-- Botones de acción -->
                    <div class="d-grid gap-2">
                        <?php if ($stock > 0): ?>
                            <button class="btn btn-add-cart" type="button" 
                                onclick="addProducto(<?php echo $codigo; ?>, '<?php echo hash_hmac('sha1', $codigo, KEY_TOKEN); ?>')">
                                <i class="fas fa-plus me-2"></i>Agregar al carrito
                            </button>
                        <?php else: ?>
                            <button class="btn btn-disabled" disabled>
                                <i class="fas fa-times me-2"></i>Sin stock disponible
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Información adicional -->
                    <div class="mt-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="fas fa-truck text-primary fa-2x"></i>
                                <p class="small mt-2">Envío gratis</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-shield-alt text-success fa-2x"></i>
                                <p class="small mt-2">Garantía</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-headset text-info fa-2x"></i>
                                <p class="small mt-2">Soporte 24/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles del producto -->
        <div class="product-details-section">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle me-2"></i>Detalles del Producto</h3>
                </div>
                <div class="card-body">
                    <div class="accordion" id="productDetailsAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDescription">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription">
                                    Descripción del Producto
                                </button>
                            </h2>
                            <div id="collapseDescription" class="accordion-collapse collapse show" data-bs-parent="#productDetailsAccordion">
                                <div class="accordion-body">
                                    <?php echo nl2br(htmlspecialchars($desc_web)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingSpecs">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSpecs">
                                    Especificaciones Técnicas
                                </button>
                            </h2>
                            <div id="collapseSpecs" class="accordion-collapse collapse" data-bs-parent="#productDetailsAccordion">
                                <div class="accordion-body">
                                    <ul class="list-unstyled">
                                        <li><strong>Código:</strong> <?php echo htmlspecialchars($codigo); ?></li>
                                        <li><strong>Marca:</strong> <?php echo htmlspecialchars($nombre_marca); ?></li>
                                        <li><strong>Stock disponible:</strong> <?php echo $stock; ?> unidades</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingUsage">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsage">
                                    Modo de Uso
                                </button>
                            </h2>
                            <div id="collapseUsage" class="accordion-collapse collapse" data-bs-parent="#productDetailsAccordion">
                                <div class="accordion-body">
                                    <?php echo nl2br(htmlspecialchars($modouso_web)); ?>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingWarranty">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWarranty">
                                    Garantía y Soporte
                                </button>
                            </h2>
                            <div id="collapseWarranty" class="accordion-collapse collapse" data-bs-parent="#productDetailsAccordion">
                                <div class="accordion-body">
                                    <p><strong>Garantía:</strong> Este producto cuenta con garantía del fabricante.</p>
                                    <p><strong>Soporte técnico:</strong> Disponible 24/7 para resolver cualquier consulta.</p>
                                    <p><strong>Devoluciones:</strong> Aceptamos devoluciones dentro de los primeros 30 días.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Productos similares -->
        <?php if (!empty($productosCategoria)): ?>
        <div class="similar-products">
            <h3 class="mb-4"><i class="fas fa-star me-2"></i>Productos Similares</h3>
            <div class="row">
                <?php foreach ($productosCategoria as $producto): ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="similar-product-card">
                        <?php
                        $imagenSimilar = "Admin/" . $producto['foto'];
                        if (empty($producto['foto']) || !file_exists($imagenSimilar)) {
                            $imagenSimilar = "images/nophoto.jpg";
                        }
                        ?>
                        <a href="detalles.php?codigo=<?php echo $producto['codigo']; ?>&token=<?php echo hash_hmac('sha1', $producto['codigo'], KEY_TOKEN); ?>">
                            <img src="<?= $imagenSimilar ?>" alt="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                        </a>
                        <h6><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . '...'; ?></h6>
                        <p class="fw-bold"><?php echo MONEDA . number_format($producto['pventa'], 2); ?></p>
                        <a href="detalles.php?codigo=<?php echo $producto['codigo']; ?>&token=<?php echo hash_hmac('sha1', $producto['codigo'], KEY_TOKEN); ?>" class="btn btn-outline-primary btn-sm">Ver Producto</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="mt-5 bg-white border-top py-4">
      <div class="container">
        <div class="row justify-content-center mb-2">
          <div class="col-auto text-center">
            <h4 class="fw-bold mb-1" style="color:#5EBC50;">Delgado Electronic</h4>
            <p class="mb-0" style="color:#555;">Tu tienda de confianza en tecnología y electrónicos</p>
          </div>
        </div>
        <div class="row">
          <div class="col text-center">
            <small class="text-muted">&copy; 2025 <b>Delgado Electronic</b> - Todos los Derechos Reservados.</small>
          </div>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <!-- Bibliotecas externas -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/efe6a408a5.js" crossorigin="anonymous"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Tus scripts personalizados -->
    <script src="js/landing.js"></script>
    <script src="js/sb-admin-2.js"></script>
    <script src="js/chatbot-ocultacion.js"></script>
    <script src="js/validacionLogin.js"></script>
    <script src="js/validarRegistro.js"></script>
</body>
</html>
