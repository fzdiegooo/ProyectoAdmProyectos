<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario'])) {
    // Si no hay sesión, redirigir a la página de inicio
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

// Consulta SQL para seleccionar los datos del usuario utilizando el DNI
$sql = $con->prepare("SELECT nombres, apellido_paterno, apellido_materno, celular, direccion FROM usuarios WHERE dni = :dni");
$sql->bindParam(':dni', $_SESSION['dni']);
$sql->execute();
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

// Asignar los datos del usuario a variables individuales
$nombres = $usuario['nombres'];

// Inicializar variable de carrito
$num_cart = 0;
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
    <link rel="stylesheet" href="../css/landing.css" />
    <link rel="stylesheet" href="../css/sb-admin-2.css" />
    <link rel="stylesheet" href="../css/chatbot.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Asegúrate de incluir Font Awesome en tu proyecto -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="../css/Footer.css" />
    <link rel="stylesheet" href="../css/cabeceras.css" />
    <link rel="stylesheet" href="../css/videos.css" />
</head>

<body class="fondo" id="page-top">
    <!-- cabeceras -->
    <div class="content fixed-header">

        <!--Primera cabecera-->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top primeraCabecera" 
         style="height: 35px; background-color: #5EBC50 !important;">
        <div class="navbar-nav" style="padding: 10px 20px; text-align: left;">
            <a class="telefono" style="color: white; font-weight: bold; text-decoration: none; font-size: 15px; margin-left: 30px;">
                <i class="fas fa-phone" style="margin-right: 8px;"></i> Llámanos al: 945853331
            </a>
        </div>
        </nav>


        <!-- Segunda cabecera -->
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top segundaCabecera">

            <!-- Logo (visible solo en pantallas medianas y grandes) -->
            <img src="../images/logo-completo.png" onclick="location.href='cliente-page.php'" alt="Logo" class="navbar-brand logoPrincipal leftImage d-none d-sm-flex" style="height: 75px; width: auto; margin-top: 10px">
            <!-- Logo (visible solo en pantallas celular) -->
            <img src="../images/Icons/logo-icono.png" onclick="location.href='cliente-page.php'" alt="Logo" class="navbar-brand logoPrincipal leftImage d-sm-none" style="height: 50px; width: auto;">
            <!-- Fondo oscurecido -->
            <div id="overlay"></div>
            <!-- Apartado buscar -->
           
            <div class="form-container">
                <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search" action="../buscar.php" method="post" autocomplete="off">
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
                    <a class="nav-link" href="../buscar.php">
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
    <!-- cabcera 3 -->
    <div class="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow categoriasCabecera" style="padding: 35px; min-height: 90px;">            
              
            <ul class="navbar-nav mx-auto cetegoriasGrupo">
                <li class="nav-item">
                    <a class="btn btn-categoria" href="clvideos_productos.php">
                        Videos
                    </a>
                </li>                
            </ul>
        </nav>
    </div>
        <!-- fin cabecera 3 -->
    <!-- fin de las cabeceras -->
   

    <!-- Sección de Videos de Productos -->
    <div class="video-section">
        <div class="container">
            
            <h1 class="text-center page-title">Videos de Nuestros Productos</h1>
            
            <div class="row">
                <!-- Video 1 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>               

                <!-- Video 2 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>
                
                <!-- Video 3 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>
                
                <!-- Video 4 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Video 5 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Video 6 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Video 7 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>

                <!-- Video 8 -->
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
                            <a class="btn btn-outline-primary" href="cliente-page.php">Ver producto</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


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
            let footer = document.querySelector("footer");

            if (chatbotButton && chatbotContainer && footer) {
                let observer = new IntersectionObserver(
                    function (entries) {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                chatbotButton.classList.add("fadeOut");
                                chatbotContainer.classList.add("fadeOut");
                            } else {
                                chatbotButton.classList.remove("fadeOut");
                                chatbotContainer.classList.remove("fadeOut");
                            }
                        });
                    },
                    { threshold: 0.1 }
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

    <!-- Script para carga perezosa de iframes -->
    <script>
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

</body>
</html>
