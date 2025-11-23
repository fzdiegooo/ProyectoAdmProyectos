<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Asegúrate de incluir Font Awesome en tu proyecto -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Document</title>
    <style>

        /* Contenedor del formulario */
        .form-container {
            position: relative;
            max-width: 600px;
            margin: 50px auto;
        }

        /* Contenedor de búsqueda con la flecha, input y botón */
        .search-container {
            display: flex;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 25px;
    padding: 8px 12px;
    width: 100%;
    border: 1px solid #ccc;
    transition: all 0.3s ease-in-out;
        }

        /* Efecto al hacer foco en el input */
.search-container:focus-within {
    border-color: #C24096;
    box-shadow: 0 0 10px rgba(194, 64, 150, 0.3);
}

        /* Flecha de retroceso */
        .btn-back {
            background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    padding: 8px;
    color: #555;
    transition: color 0.3s;
        }

        /* Input de búsqueda */
        .search-input {
            flex: 1;
    border: none;
    outline: none;
    padding: 10px;
    font-size: 16px;
    background: transparent;
        }

        /* Botón de búsqueda (decorativo, sin funcionalidad) */
        .search-btn {
            background-color: #C24096;
    border: none;
    padding: 10px;
    border-radius: 50%;
    cursor: pointer;
    transition: background-color 0.3s ease-in-out;
        }

        .search-btn:hover {
    background-color: #a8327d;
}

        .search-btn i {
            color: white;
            font-size: 18px;
        }


        /* Estilo para la lista de resultados */
        #lista {
            list-style-type: none;
            padding: 0;
            margin-top: 5px; /* Agrega un pequeño espacio entre la barra de búsqueda y la lista */
            width: 100%;
            max-height: 200px; /* Limita la altura máxima de la lista */
            overflow-y: auto; /* Permite desplazamiento vertical si la lista es larga */
            overflow-x: hidden; /* Oculta el desplazamiento horizontal */
            position: absolute;
            top: calc(100% + 5px); /* Espacio extra debajo del input */
            z-index: 9999; /* Asegura que la lista esté por encima de otros elementos */
            background-color: #fff; /* Color de fondo de la lista */
            border-radius: 0.25rem; /* Borde redondeado */
        }

        /* Estilo para los elementos de la lista */
        #lista li {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s; /* Transición suave */
            font-size: 14px;
            color: #333;
        }

        /* Estilo para los elementos de la lista al pasar el cursor */
        #lista li:hover {
            background-color: #48B6C1; /* Color de fondo al pasar el cursor */
            color: white; /* Color del texto al pasar el cursor */
            transform: scale(1.02); /* Pequeño efecto de escala */
            border-radius: 5px;
        }

        /* Estilo para el contenedor del formulario */
        .form-container {
            position: relative; /* Permite posicionar la lista de resultados correctamente */
            max-width: 600px; /* Ancho máximo del contenedor */
            margin: 50px auto; /* Centra el contenedor horizontalmente */
        }

        /* Estilo para el input */
        .form-control {
            border-radius: 0.25rem; /* Borde redondeado */
        }

        /* Estilo para eliminar el subrayado y cambiar el color del enlace */
        .no-decoration {
            text-decoration: none;
            color: black; /* Color del texto */
        }
/* Responsividad */
@media (max-width: 576px) {
    .form-container {
        max-width: 95%;
    }

    .search-input {
        font-size: 14px;
        padding: 8px;
    }

    .search-btn {
        padding: 8px;
    }
}
        
    </style>
</head>
<body>

<!-- Fondo oscurecido -->
<div id="overlay"></div>

<div class="form-container">
    <form action="" method="post" autocomplete="off">
        <div class="search-container">
            <!-- Flecha de retroceso -->
            <button type="button" class="btn-back" onclick="history.back()">
                <i class="fas fa-arrow-left"></i>
            </button>
            
            <!-- Campo de búsqueda -->
            <input type="text" class="search-input" name="campo" id="campo" placeholder="Busca un producto">
            
            <!-- Botón de búsqueda (solo decorativo) -->
            <button type="button" class="search-btn">
                <i class="fas fa-search"></i>
            </button>
        </div>
        
        <!-- Lista de resultados -->
        <ul id="lista" class="list-group"></ul>
    </form>
</div>

<script src="js/peticiones.js"></script>

<!-- JavaScript para mostrar/ocultar el fondo oscuro -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var searchInput = document.getElementById("campo");
        var overlay = document.getElementById("overlay");

        searchInput.addEventListener("focus", function () {
            overlay.style.display = "block"; // Mostrar fondo oscuro
        });

        overlay.addEventListener("click", function () {
            overlay.style.display = "none"; // Ocultar fondo oscuro
            searchInput.blur(); // Quitar el foco del input
        });
    });
</script>

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

    /* Para asegurarse de que el formulario esté sobre el overlay */
    .form-container {
        position: relative;
        z-index: 20;
    }
</style>

</body>

</html>