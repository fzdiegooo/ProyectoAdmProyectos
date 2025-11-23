
document.addEventListener("DOMContentLoaded", function() {
    var campoInput = document.getElementById("campo");
    var listaResultados = document.getElementById("lista");

    //En esa función se realiza la busqueda del producto mediante la lectura de lo ingresado en el campo busqueda
    campoInput.addEventListener("input", function() {
        var campo = campoInput.value.trim();

        if (campo === "") {
            listaResultados.innerHTML = "";
            return;
        }

        var xhr = new XMLHttpRequest();
        //Se envia los datos de campo al archivo getCodigos_cliente2.php para realizar la busqueda de los productos
        // que tengan relación con lo ingresado en el campo de búsqueda
        xhr.open("POST", "php/getCodigos_cliente2.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                var resultados = JSON.parse(xhr.responseText);
                listaResultados.innerHTML = resultados;
            }
        };

        xhr.send("campo=" + campo);
    });

    // Agregar un listener para cada elemento li dentro de la lista
    listaResultados.addEventListener("click", function(event) {
        // Verificar si el clic se realizó en un elemento li
        if (event.target.tagName === "LI") {
            // Redirigir al usuario a la URL del href del li
            window.location.href = event.target.firstChild.href;
        }
    });
});

