<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Admin/productos_admin.php");
        exit();
    } else {
        header("Cliente/cliente-page.php");
        exit();
    }
}

require 'Config/config.php';
require 'php/database.php';
require 'php/clientesfunciones.php';

$db = new Database();
$con = $db->conectar();

if (isset($_POST['accion']) && $_POST['accion'] === 'agregarU') {
    $dni = trim($_POST["dni_U"]);
    $nombre = trim($_POST["nombres_U"]);
    $apellido_paterno = trim($_POST["apellido_paterno_U"]);
    $apellido_materno = trim($_POST["apellido_materno_U"]);
    $telefono = trim($_POST["celular_U"]);
    $fecha_nacimiento = trim($_POST["fecha_nacimiento_U"]);
    $correo = trim($_POST["email_U"]);
    $contrasena = trim($_POST["password_U"]);
    $genero = trim($_POST["genero_U"]);
    $distrito = trim($_POST['distrito_U']);
    $avenida = trim($_POST['avenida_U']);
    $numero = trim($_POST['numero_U']);
    $descripcion = trim($_POST['descripcion_U']);
    $contrasena_encriptada = password_hash($contrasena, PASSWORD_DEFAULT);
    $token = generarToken();
    $direccion = "$distrito, $avenida, $numero, $descripcion";

    header('Content-Type: application/json');
    $sql = $con->prepare("INSERT INTO usuarios (dni, nombres, apellido_paterno, apellido_materno, celular, fecha_nacimiento, correo, contrasena, genero, direccion, token, rol) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $resultado = $sql->execute([$dni, $nombre, $apellido_paterno, $apellido_materno, $telefono, $fecha_nacimiento, $correo, $contrasena_encriptada, $genero, $direccion, $token, 'cliente']);

    if ($resultado) {
        echo json_encode(["status" => "success", "message" => "Usuario agregado correctamente"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error al agregar el usuario"]);
        exit;
    }
}

$sql = $con->prepare("SELECT codigo, foto, id_categoria, descripcion, stock, pventa FROM productos WHERE estado = ?;");

$activo = 1;
$sql->bindParam(1, $activo, PDO::PARAM_INT);
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

$categorias = array();
$consultaCategorias = "SELECT * FROM categorias";
$resultadoCategorias = $con->prepare($consultaCategorias);
$resultadoCategorias->execute();
$categoriasData = $resultadoCategorias->fetchAll(PDO::FETCH_ASSOC);
foreach ($categoriasData as $categoria) {
    $categorias[$categoria['id_categoria']] = $categoria;
}

$sqlProductosCategoria = $con->prepare("
                SELECT codigo, id_categoria, foto, descripcion, stock, pventa
                FROM productos 
                WHERE id_categoria = :id_categoria
            ");
$sqlProductosCategoria->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
$sqlProductosCategoria->execute();
$productosCategoria = $sqlProductosCategoria->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delgado Electronic</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/landing.css" />
    <link rel="stylesheet" href="css/sb-admin-2.css" />
    <link rel="stylesheet" href="css/chatbot.css" />
    <link rel="stylesheet" href="css/Footer.css" />
    <link rel="stylesheet" href="css/cabeceras.css" />
    <link rel="stylesheet" href="css/videos.css" />
    <style>
        body, html { font-family: 'Poppins', Arial, sans-serif; background: #f8fafc; }
        .top-bar { background: linear-gradient(90deg, #5EBC50 0%, #489c3a 100%); color: #fff; padding: 8px 0; font-size: 0.9rem; }
        .navbar-brand img { height: 52px; }
        .navbar { box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .nav-link, .navbar-brand { font-weight: 600; }
        .btn-custom { background: #5EBC50; color: #fff; border-radius: 30px; padding: 8px 24px; font-weight: 600; }
        .btn-custom:hover { background: #489c3a; color: #fff; transform: translateY(-2px); }
        .search-input { border-radius: 30px 0 0 30px; border-right: 0; padding: 10px 18px; }
        .search-btn { border-radius: 0 30px 30px 0; background: #5EBC50; color: #fff; border: none; padding: 10px 20px; }
        .search-btn:hover { background: #489c3a; }
        .navbar-nav .nav-item .nav-link { color: #222; }
        .navbar-nav .nav-item .nav-link:hover { color: #5EBC50; }
        .hero-banner { background: linear-gradient(135deg, #5EBC50 0%, #3a9e30 100%); color: #fff; padding: 60px 0; text-align: center; }
        .hero-banner h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem; }
        .hero-banner p { font-size: 1.2rem; margin-bottom: 1.5rem; }
        .badge-category { background: #e9f7ef; color: #5EBC50; font-size: 0.75em; border-radius: 12px; padding: 4px 10px; margin-bottom: 8px; font-weight: 600; display: inline-block; }
        .badge-stock { background: #ffe5e5; color: #ff4444; font-size: 0.75em; border-radius: 8px; padding: 2px 8px; font-weight: 600; }
        .badge-stock.in-stock { background: #e9f7ef; color: #5EBC50; }
        .shadow-sm { box-shadow: 0 2px 12px rgba(0,0,0,0.08)!important; }
        .product-card { border-radius: 16px; overflow: hidden; background: #fff; }
        .product-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(94,188,80,0.15)!important; }
        .product-card .card-img-top { height: 220px; object-fit: cover; transition: transform 0.3s; }
        .product-card:hover .card-img-top { transform: scale(1.05); }
        .product-rating { color: #ffa500; font-size: 0.9rem; }
        .price-tag { font-size: 1.4rem; font-weight: 700; color: #5EBC50; }
        .price-old { text-decoration: line-through; color: #999; font-size: 1rem; margin-left: 8px; }
        .section-title { font-size: 2rem; font-weight: 700; color: #222; margin-bottom: 2rem; text-align: center; position: relative; }
        .section-title::after { content: ''; display: block; width: 80px; height: 4px; background: #5EBC50; margin: 12px auto 0; border-radius: 2px; }
        .category-pill { background: #fff; border-radius: 25px; padding: 8px 20px; margin: 0 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); color: #222; text-decoration: none; font-weight: 600; display: inline-block; transition: all 0.2s; }
        .category-pill:hover { background: #5EBC50; color: #fff; transform: translateY(-2px); }
        @media (max-width: 767.98px) {
            .navbar-brand span { font-size: 1rem; }
            .hero-banner h1 { font-size: 1.8rem; }
            .hero-banner p { font-size: 1rem; }
            .product-card { max-width: 100%!important; }
            .card-body { padding: 1rem 0.8rem; }
            .section-title { font-size: 1.5rem; }
        }
        @media (max-width: 575.98px) {
            .top-bar { font-size: 0.8rem; padding: 6px 0; }
            .navbar { padding-left: 0.5rem; padding-right: 0.5rem; }
            .hero-banner { padding: 40px 0; }
        }
    </style>
</head>

<body class="fondo" id="page-top">
    <?php include 'partials/site-header.php'; ?>

    <!-- Hero Banner Section -->
    <div class="hero-banner">
        <div class="container">
            <h1>Tecnología de vanguardia al mejor precio</h1>
            <p>Encuentra los mejores productos electrónicos para tu hogar y oficina</p>
            <a href="#productos" class="btn btn-light btn-lg px-5" style="border-radius:30px; font-weight:600;">Ver Productos</a>
        </div>
    </div>


    <!--<div class="carousel-container">
        <div class="carousel">
            <div class="slide"><img src="images/carrusel1.jpg" alt="Slide 1" loading="lazy"></div>
            <div class="slide"><img src="images/carrusel2.jpg" alt="Slide 2" loading="lazy"></div>
            <div class="slide"><img src="images/carrusel3.jpg" alt="Slide 3" loading="lazy"></div>
            <div class="slide"><img src="images/carrusel4.jpg" alt="Slide 4" loading="lazy"></div>
        </div>

        <div class="arrow arrow-left">&#9664;</div>
        <div class="arrow arrow-right">&#9654;</div>
    </div>-->


    <!-- Apartado de las tarjetas/productos -->
    <div class="container py-5" id="productos">
        <h2 class="section-title">Productos Destacados</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($resultado as $row) : ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0 product-card" style="max-width: 290px; margin:auto; transition: all 0.3s;">
                        <?php
                        $id_categoria_producto = $row['id_categoria'];
                        if (isset($categorias[$id_categoria_producto])) {
                            $categoria_producto = $categorias[$id_categoria_producto];
                            $nombre_categoria = $categoria_producto['nombre_categoria'];
                        } else {
                            $nombre_categoria = 'Categoría Desconocida';
                        }
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
                        $stock = $row['stock'];
                        ?>
                        <div class="position-relative">
                            <?php if ($stock < 5 && $stock > 0): ?>
                                <span class="badge badge-stock position-absolute top-0 end-0 m-2">Últimas unidades</span>
                            <?php elseif ($stock >= 5): ?>
                                <span class="badge badge-stock in-stock position-absolute top-0 end-0 m-2">Disponible</span>
                            <?php endif; ?>
                            <a href="detalles.php?codigo=<?php echo $row['codigo']; ?>&token=<?php echo hash_hmac('sha1', $row['codigo'], KEY_TOKEN); ?>">
                                <img src="<?= $imagen ?>" class="card-img-top" alt="Imagen del producto" loading="lazy">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column" style="padding:1.2rem;">
                            <span class="badge badge-category mb-2"><?= $nombre_categoria ?></span>
                            <a href="detalles.php?codigo=<?php echo $row['codigo']; ?>&token=<?php echo hash_hmac('sha1', $row['codigo'], KEY_TOKEN); ?>" style="text-decoration:none; color:inherit;">
                                <h5 class="card-title mb-2" style="font-size:1rem; font-weight:600; color:#222; min-height:42px; line-height:1.4;"><?= $row['descripcion'] ?></h5>
                            </a>
                            <div class="product-rating mb-2">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                                <span class="text-muted" style="font-size:0.85rem;">(4.5)</span>
                            </div>
                            <div class="mb-3">
                                <span class="price-tag">S/<?= $row['pventa'] ?></span>
                                <span class="price-old">S/<?= number_format($row['pventa'] * 1.2, 2) ?></span>
                            </div>
                            <div class="mt-auto d-grid">
                                <button class="btn btn-custom btn-productos w-100" type="button" onclick="addProducto(<?= $row['codigo']; ?>, '<?= hash_hmac('sha1', $row['codigo'], KEY_TOKEN); ?>')" style="border-radius:12px;">
                                    <i class="fas fa-cart-plus me-2"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Why Choose Us Section -->
    <div class="container py-5">
        <div class="row text-center g-4">
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-shipping-fast fa-3x mb-3" style="color:#5EBC50;"></i>
                    <h5 style="font-weight:600;">Envío Rápido</h5>
                    <p class="text-muted" style="font-size:0.9rem;">Entrega en 24-48 horas</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-shield-alt fa-3x mb-3" style="color:#5EBC50;"></i>
                    <h5 style="font-weight:600;">Compra Segura</h5>
                    <p class="text-muted" style="font-size:0.9rem;">Pago 100% protegido</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-undo-alt fa-3x mb-3" style="color:#5EBC50;"></i>
                    <h5 style="font-weight:600;">Garantía</h5>
                    <p class="text-muted" style="font-size:0.9rem;">30 días de devolución</p>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="p-3">
                    <i class="fas fa-headset fa-3x mb-3" style="color:#5EBC50;"></i>
                    <h5 style="font-weight:600;">Soporte 24/7</h5>
                    <p class="text-muted" style="font-size:0.9rem;">Atención personalizada</p>
                </div>
            </div>
        </div>
    </div>
    

    
        
        <!-- Puedes agregar más filas según necesites -->
    </section>

    <script>
        function mostrarVideosProductos() {
            const seccionVideos = document.getElementById('videos-productos');
            if (seccionVideos.style.display === 'none' || seccionVideos.style.display === '') {
                seccionVideos.style.display = 'block';
                // Hacer scroll suave a la sección
                seccionVideos.scrollIntoView({ behavior: 'smooth' });
            } else {
                seccionVideos.style.display = 'none';
            }
        }

        // Opcional: Reproducir video al hacer hover
        document.addEventListener('DOMContentLoaded', function() {
            const videos = document.querySelectorAll('.video-producto');
            
            videos.forEach(video => {
                video.addEventListener('mouseover', function() {
                    this.play();
                });
                
                video.addEventListener('mouseout', function() {
                    this.pause();
                    this.currentTime = 0;
                });
            });
        });
    </script>

    <script src="chatbot-ocultacion.js"></script>

    <!--Contenedor del chatbot -->
    <div class="chatbot-container" id="chatbot">
        <div class="chatbot-header">Nuestros canales de atención</div>
        <div class="chatbot-links">
            <a href="javascript:void(0);">📞 945853331</a>
            <a href="https://wa.me/993207538?text=Hola,%20quiero%20obtener%20más%20información." target="_blank">📱 WhatsApp</a>
            <a href="https://www.facebook.com/profile.php?id=61552772167929" target="_blank">📘 Facebook</a>
        </div>
    </div>
    
    <button class="chatbot-button" id="chatbotToggle">💬</button>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatbotButton = document.querySelector(".chatbot-button");
            const chatbotContainer = document.querySelector(".chatbot-container");

            chatbotButton.addEventListener("click", function () {
                chatbotContainer.classList.toggle("show");
            });
        });
    </script>
    

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let chatbotButton = document.querySelector(".chatbot-button");
            let chatbotContainer = document.querySelector(".chatbot-container");
            let footer = document.querySelector("footer"); // Asegúrate de que el footer tenga esta etiqueta

            if (chatbotButton && chatbotContainer && footer) {
                let observer = new IntersectionObserver(
                    function (entries) {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                // Oculta el botón y el contenedor del chatbot
                                chatbotButton.classList.add("fadeOut");
                                chatbotContainer.classList.add("fadeOut");
                            } else {
                                // Muestra el botón y el contenedor del chatbot cuando el footer deja de estar visible
                                chatbotButton.classList.remove("fadeOut");
                                chatbotContainer.classList.remove("fadeOut");
                            }
                        });
                    },
                    { threshold: 0.1 } // Se activa cuando el 10% del footer es visible
                );

                observer.observe(footer);
            }
        });
    </script>


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
        <style>
            .chatbot-container {
                border-radius: 18px;
                box-shadow: 0 4px 24px rgba(94,188,80,0.10);
                background: #fff;
                border: 1px solid #e9f7ef;
                font-family: 'Poppins', Arial, sans-serif;
            }
            .chatbot-header {
                background: #5EBC50;
                color: #fff;
                border-radius: 18px 18px 0 0;
                padding: 10px 18px;
                font-weight: 600;
            }
            .chatbot-links a {
                color: #5EBC50;
                font-weight: 500;
                text-decoration: none;
                display: block;
                margin: 8px 0;
                transition: color 0.2s;
            }
            .chatbot-links a:hover {
                color: #489c3a;
                text-decoration: underline;
            }
            .chatbot-button {
                background: #5EBC50;
                color: #fff;
                border-radius: 50%;
                border: none;
                box-shadow: 0 2px 8px rgba(94,188,80,0.15);
                font-size: 1.5rem;
                width: 56px;
                height: 56px;
                position: fixed;
                bottom: 32px;
                right: 32px;
                z-index: 9999;
                transition: background 0.2s;
            }
            .chatbot-button:hover {
                background: #489c3a;
            }
        </style>

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

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