<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Location: ../Admin/admin-page.php");
        exit();
    }
}

require '../Config/config.php';
require '../php/database.php';

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

// Consulta SQL para seleccionar los datos del usuario utilizando el DNI
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, direccion FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

// Asignar los datos del usuario a variables individuales
$nombres = $usuario['nombres'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($descripcion); ?> - Delgado Electronic</title>
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/sb-admin-2.css" />
    <link rel="stylesheet" href="../css/chatbot.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../css/Footer.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
    
    <style>
        .product-image-container {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            height: 400px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            border-radius: 10px;
            margin: 0 auto;
            cursor: zoom-in;
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

        .user-welcome {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
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

<body class="fondo" id="page-top">
    <!-- Cabeceras -->
    <div class="content fixed-header">
        <!-- Primera cabecera -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
             style="height: 35px; background-color: #5EBC50 !important;">
            <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
                <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                    <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 945853331
                </a>
            </div>
        </nav>

        <!-- Segunda cabecera -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">

            <!-- Logo (visible solo en pantallas medianas y grandes) -->
            <img src="../images/logo-completo.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 75px; width: auto; margin-top: 10px">
            <!-- Logo (visible solo en pantallas celular) -->
            <img src="../images/Icons/logo-icono.png" onclick="redirectToLanding()" alt="Logo" class="navbar-brand logoPrincipal leftImage d-sm-none" style="height: 50px; width: auto;">
            <!-- Fondo oscurecido -->
            <div id="overlay"></div>
            <!-- Apartado buscar -->
           
            <div class="form-container">
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="" method="post" autocomplete="off">
                    <div class="input-group search-wrapper">
                        <input type="text" class="form-control bg-light border-0 small" placeholder="Busca un producto..." aria-label="Search" aria-describedby="basic-addon2" name="campo" id="campo">
                        <div class="input-group-append">
                            <button class="btn bg-custom-color" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                    <ul id="lista" class="list-group"></ul>
                </form>
            </div>
          
            <!-- CSS -->
             
            <style>
                /* Fondo oscurecido */
                #overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.6);
                    display: none;
                    z-index: 10;
                }

                /* Estilos de la barra de búsqueda */
                .form-container {
                    position: relative;
                    z-index: 20;
                }

                .navbar-search {
                    width: 300px; /* Tamaño inicial */
                    transition: width 0.3s ease-in-out;
                }

                .navbar-search.expanded {
                    width: 600px; /* Tamaño expandido */
                }

                .search-input {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 30px;
                    font-size: 16px;
                }

                .search-btn {
                    border-radius: 50%;
                    padding: 10px;
                }
                
            </style>
            
            <!-- JavaScript -->
            
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    var searchInput = document.getElementById("campo");
                    var overlay = document.getElementById("overlay");
                    var searchForm = document.querySelector(".navbar-search");

                    searchInput.addEventListener("focus", function () {
                        overlay.style.display = "block"; // Oscurecer fondo
                        searchForm.classList.add("expanded"); // Expandir barra
                    });

                    overlay.addEventListener("click", function () {
                        overlay.style.display = "none"; // Quitar oscurecimiento
                        searchForm.classList.remove("expanded"); // Contraer barra
                        searchInput.blur(); // Quitar el foco del input
                    });
               });
            </script>
            
            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <!-- Nav Item - Search Dropdown (Visible Only XS) -->
                <!-- Nav Item - Search Redirect (Visible Only on Small Screens) -->
                <li class="nav-item d-sm-none">
                    <a class="nav-link" href="buscar.php">
                        <i class="fas fa-search fa-fw"></i>
                    </a>
                </li>
            </ul>

            <!-- Botón de Carrito de Compras -->
            <ul class="navbar-nav mx-auto carro-compras">
                <li class="nav-item">
                    <a href="carritodetalles_cliente.php">
                        <img src="../images/Icons/carro.png" loading="lazy"></a>
                    <span id="num_cart" class="mr-2" style="margin-left: 0.5vh;"><?php echo $num_cart; ?></span>
                </li>
            </ul>


            <ul class="navbar-nav mx-auto">
                <li class="nav-item dropdown position-relative">
                    <a class="nav-link d-flex align-items-center dropdown-toggle custom-user-dropdown" href="#" id="usuarioDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../images/sesion_ini.png" alt="Usuario" class="user-icon" style="height: 30px; width: auto; margin-right: 15px;">
                        <span class="user-name">Bienvenido, <?php echo $nombres; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end position-absolute" aria-labelledby="usuarioDropdown">
                        <li><a class="dropdown-item custom-dropdown-item" href="datos_usuario.php">📝 Datos Personales</a></li>
                        <li><a class="dropdown-item custom-dropdown-item" href="compras_cliente.php">🛍️ Mis Compras</a></li>
                        <li><a class="dropdown-item custom-dropdown-item" id="cerrarSesionBtn" href="#">🚪 Cerrar Sesión</a></li>
                    </ul>
                </li>
            </ul>

            
        </nav>
    </div>

        <!-- Tercera cabecera -->
    <div class="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow categoriasCabecera" style="padding: 35px; min-height: 90px;">            
             
            <ul class="navbar-nav mx-auto cetegoriasGrupo">
                <li class="nav-item">
                    <a class="btn btn-categoria" href="videos_productos.php">
                        Videos
                    </a>
                </li>                
            </ul>
        </nav>
    </div>

    <!-- Contenido principal -->
    <div class="container" style="margin-top: 30px;">
        <!-- Información del producto -->
        <div class="row">
            <div class="col-md-6">
                <div class="product-image-container" onclick="toggleZoom(this)" onmousemove="moveZoom(event, this)">
                    <?php
                    $directorioImagenes = "../Admin/";
                    $imagenBD = $row['foto'];

                    if (empty($imagenBD)) {
                        $imagen = "../images/nophoto.jpg";
                    } else {
                        $imagen = $directorioImagenes . $imagenBD;
                        if (!file_exists($imagen)) {
                            $imagen = "../images/nophoto.jpg";
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
                                onclick="addProducto(<?= $row['codigo']; ?>, '<?= hash_hmac('sha1', $row['codigo'], KEY_TOKEN); ?>')">
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
                        $imagenSimilar = "../Admin/" . $producto['foto'];
                        if (empty($producto['foto']) || !file_exists($imagenSimilar)) {
                            $imagenSimilar = "../images/nophoto.jpg";
                        }
                        ?>
                        <a href="detalles_cliente.php?codigo=<?php echo $producto['codigo']; ?>&token=<?php echo hash_hmac('sha1', $producto['codigo'], KEY_TOKEN); ?>">
                            <img src="<?= $imagenSimilar ?>" alt="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                        </a>
                        <h6><?php echo htmlspecialchars(substr($producto['descripcion'], 0, 50)) . '...'; ?></h6>
                        <p class="fw-bold"><?php echo MONEDA . number_format($producto['pventa'], 2); ?></p>
                        <a href="detalles_cliente.php?codigo=<?php echo $producto['codigo']; ?>&token=<?php echo hash_hmac('sha1', $producto['codigo'], KEY_TOKEN); ?>" class="btn btn-outline-primary btn-sm">Ver Producto</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Chatbot -->
    <div class="chatbot-container" id="chatbot">
        <div class="chatbot-header">Nuestros canales de atención</div>
        <div class="chatbot-links">
            <a href="javascript:void(0);">📞 945853331</a>
            <a href="https://wa.me/993207538?text=Hola,%20quiero%20obtener%20más%20información." target="_blank">📱 WhatsApp</a>
            <a href="https://www.facebook.com/DelgadoElectronic/" target="_blank">📘 Facebook</a>
        </div>
    </div>
    
    <button class="chatbot-button" id="chatbotToggle">💬</button>

    <!-- Footer -->
    <footer class="pie-pagina mt-5">
        <div class="grupo-1">
            <div class="row">
                <div class="col-md-12 text-center">
                    <h3>Delgado Electronic</h3>
                    <p>Tu tienda de confianza en tecnología y electrónicos</p>
                </div>
            </div>
            <div class="grupo-2 text-center">
                <small>&copy; 2025 <b>Delgado Electronic</b> - Todos los Derechos Reservados.</small>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <!-- Bibliotecas externas -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/efe6a408a5.js" crossorigin="anonymous"></script>

    <!-- Bootstrap core JavaScript-->
    <script src="../vendor/jquery/jquery.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tus scripts personalizados -->
    <script src="../js/cliente.js"></script>
    <script src="../js/sb-admin-2.js"></script>
    <script src="../js/exit.js"></script>
    <script src="../js/chatbot-ocultacion.js"></script>

    
</body>
</html>
