<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['usuario'])) {
    // Si ya hay una sesión iniciada, redirige al usuario según su tipo
    if (isset($_SESSION['tipo']) && $_SESSION['tipo'] == 'admin') {
        header("Admin/productos_admin.php");
        exit();
    } else {
        header("Cliente/cliente-page.php");
        exit();
    }
}

//
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

// Realizar consultas y obtener datos necesarios
$sql = $con->prepare("SELECT codigo, foto, id_categoria, descripcion, stock, pventa FROM productos WHERE estado = ?;");

$activo = 1;
$sql->bindParam(1, $activo, PDO::PARAM_INT);
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener las categorías
$categorias = array();
$consultaCategorias = "SELECT * FROM categorias";
$resultadoCategorias = $con->prepare($consultaCategorias);
$resultadoCategorias->execute();
$categoriasData = $resultadoCategorias->fetchAll(PDO::FETCH_ASSOC);
foreach ($categoriasData as $categoria) {
    $categorias[$categoria['id_categoria']] = $categoria;
}

// Función para convertir URL de YouTube a embed
function convertirYoutubeEmbed($url) {
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);
    $id = isset($matches[1]) ? $matches[1] : '';
    return 'https://www.youtube.com/embed/'.$id.'?rel=0&showinfo=0';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videos de Productos - Delgado Electronic</title>
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
    <link rel="stylesheet" href="css/videos.css" />
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f8fafc; }
        .top-bar { background: linear-gradient(90deg, #5EBC50 0%, #489c3a 100%); color: #fff; padding: 8px 0; font-size: 0.9rem; }
        .page-title { font-size: 2.5rem; font-weight: 700; color: #222; margin-bottom: 3rem; position: relative; }
        .page-title::after { content: ''; display: block; width: 100px; height: 4px; background: #5EBC50; margin: 16px auto 0; border-radius: 2px; }
        .back-button { background: #5EBC50; color: #fff; border: none; border-radius: 30px; padding: 10px 24px; font-weight: 600; margin-bottom: 30px; }
        .back-button:hover { background: #489c3a; color: #fff; transform: translateY(-2px); }
        .video-card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); transition: all 0.3s; background: #fff; margin-bottom: 30px; }
        .video-card:hover { transform: translateY(-8px); box-shadow: 0 12px 40px rgba(94,188,80,0.15); }
        .video-card .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; }
        .video-card .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        .video-card .video-info { padding: 20px; }
        .video-card .video-info h3 { font-size: 1.2rem; font-weight: 600; color: #222; margin-bottom: 12px; }
        .video-card .video-info p { color: #666; font-size: 0.95rem; margin-bottom: 16px; }
        .video-card .btn-outline-primary { border-color: #5EBC50; color: #5EBC50; border-radius: 25px; padding: 8px 24px; font-weight: 600; }
        .video-card .btn-outline-primary:hover { background: #5EBC50; color: #fff; }
    </style>
</head>

<body class="fondo" id="page-top">

    <?php include 'partials/site-header.php'; ?>

    <!-- Sección de Videos de Productos -->
    <div class="video-section" style="padding: 60px 0;">
        <div class="container">
            <a href="index.php" class="btn back-button">
                <i class="fas fa-arrow-left me-2"></i> Volver a la tienda
            </a>
            
            <h1 class="text-center page-title">Videos de Nuestros Productos</h1>
            
            <div class="row">
                <!-- Ejemplo de video 1 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=fxqNYaxoSpU') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>Tarjeta RAM - Kingston FURY BEAST RGB 16GB 5600MHz</h3>
                            <p>Memoria RAM DDR5 con iluminación RGB, ideal para gamers y usuarios exigentes que buscan alto rendimiento y estética visual llamativa.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>               


                <!-- Ejemplo de video 2 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=hTNxiqsiGtY') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>DUAL-RX580-8G</h3>
                            <p>Tarjeta de video con dos ventiladores para mejor refrigeración, usada para gaming, diseño gráfico, edición de video y tareas de alto rendimiento gráfico.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>
                
                <!-- Ejemplo de video 3 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=VXSV1XJu3nc') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>Mouse Gamer Inalámbrico Xtrike Me GW-611</h3>
                            <p>Mouse óptico con luces LED RGB. Diseñado para videojuegos, ofrece mejor precisión y ergonomía en comparación con un mouse convencional.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>
                
                <!-- Ejemplo de video 4 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=1lf3L_oN0mI') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>Micrófono Sktome BM 800</h3>
                            <p>Micrófono de condensador con filtro antipop y brazo ajustable. Usado para grabación, streaming, podcasts o videollamadas profesionales.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo de video 5 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=-o2EJ5qjdtU') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>PLACA GIGABYTE H410M H V3 LGA1200/ DDR4 ( H410M H V3 G10 )</h3>
                            <p>Placa madre micro ATX diseñada para procesadores Intel de 10ª generación. Ofrece buena estabilidad, ranuras de expansión PCIe, soporte para memorias DDR4 y conexiones básicas para montar una PC de escritorio eficiente y económica.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo de video 6 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=-PwUAxhwzWM') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>PROCESADOR INTEL CORE i7-10700F</h3>
                            <p>Procesador Intel Core i7 de 10ª generación diseñado para alto rendimiento. Cuenta con múltiples núcleos e hilos, ideal para tareas exigentes como videojuegos, edición de video, diseño 3D, programación y virtualización.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo de video 7 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=PxmT828QseI') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>AUDÍFONOS GAMER XTRIKE ME GH-711 MICRÓFONO RGB CONSOLA</h3>
                            <p>Audífonos tipo diadema con almohadillas acolchadas y micrófono ajustable. Pensados para videojuegos o videollamadas con buena calidad de sonido.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Ejemplo de video 8 -->
                <div class="col-lg-6">
                    <div class="card video-card">
                        <div class="video-container">
                            <iframe src="<?= convertirYoutubeEmbed('https://www.youtube.com/watch?v=PSdZfFbpOhs') ?>" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>
                        <div class="video-info">
                            <h3>KLIM Chroma Teclado inalámbrico</h3>
                            <p>Teclado tipo mecánico o membrana con retroiluminación RGB, incluye adaptador USB inalámbrico. Ideal para gaming o ambientes con poca luz.</p>
                            <a class="btn btn-outline-primary" href="index.php">Ver producto</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Opcional: Carga perezosa para iframes
        document.addEventListener("DOMContentLoaded", function() {
            const iframes = document.querySelectorAll('iframe');
            
            const lazyLoadIframe = (iframe) => {
                if(iframe.getAttribute('data-src')) {
                    iframe.setAttribute('src', iframe.getAttribute('data-src'));
                }
            };
            
            const iframeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if(entry.isIntersecting) {
                        lazyLoadIframe(entry.target);
                        iframeObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            iframes.forEach(iframe => {
                iframe.setAttribute('data-src', iframe.getAttribute('src'));
                iframe.removeAttribute('src');
                iframeObserver.observe(iframe);
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