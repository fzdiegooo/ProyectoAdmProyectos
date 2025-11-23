//----------------------------------------------------------------------------------------------------------------------
/*Rutear para otra ventana*/
function redirectToFarmacia() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "farmacia_cliente.php";
}

function redirectToSuplementos() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "suplementos_vitaminas_cliente.php";
}

function redirectToNutricion() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "nutricion_deportiva_cliente.php";
}

function redirectToBelleza() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cuidado_belleza_cliente.php";
}

function redirectToAromaterapia() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "aromaterapia_cliente.php";
}

function redirectToCliente() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cliente-page.php";
}

function redirectToLanding() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cliente-page.php";
}

function redirectToDetallesCarrito() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "carritodetalles_cliente.php";
}

function redirectToPago() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "pago_cliente.php";
}

//----------------------------------------------------------------------------------------------------------------------
/*Carrousel de imagenes*/
document.addEventListener("DOMContentLoaded", function () {
    var slides = document.querySelectorAll('.slide');
    var currentSlide = 0;

    function showSlide(index) {
        slides.forEach(function (slide) {
            slide.style.display = 'none';
        });
        slides[index].style.display = 'block';
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    var arrowLeft = document.querySelector('.arrow-left');
    var arrowRight = document.querySelector('.arrow-right');

    arrowLeft.addEventListener('click', function () {
        prevSlide();
    });

    arrowRight.addEventListener('click', function () {
        nextSlide();
    });
    setInterval(nextSlide, 3000);
    showSlide(currentSlide);
});


//-------------------------------------------------------------------------------------------------------------
/*Funcion para agregar productos al carrito*/
function addProducto(codigo, token) {
    let url = '../Clases/carrito.php';
    let formData = new FormData();
    formData.append('codigo', codigo);
    formData.append('token', token);

    fetch(url, {
        method: 'POST',
        body: formData,
        mode: 'cors'
    }).then(response => response.json())
        .then(data => {
            if (data.ok) {
                let elemento = document.getElementById("num_cart");
                elemento.innerHTML = data.numero;
            }
            Swal.fire({
                title: "¡Producto añadido al carrito! 🛒",
                text: "¿Qué deseas hacer ahora?",
                icon: "success",
                showCancelButton: true,
                confirmButtonText: "Ver Carrito",
                cancelButtonText: "Seguir Comprando",
                showCloseButton: true, // 🔴 AGREGA EL BOTÓN DE CERRAR
                allowOutsideClick: true // Permite cerrar haciendo clic fuera
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "carritodetalles_cliente.php"; // Redirige al carrito
                }
            })
        });
        if (comprarAhora) {
            window.location.href = "carritodetalles_cliente.php"; // Redirige si es compra inmediata
        }
}

//-------------------------------------------------------------------------------------------------------------
/*Funcion buscador para paginas cliente*/
document.addEventListener("DOMContentLoaded", function () {
    var campoInput = document.getElementById("campo");
    var listaResultados = document.getElementById("lista");

    campoInput.addEventListener("input", function () {
        var campo = campoInput.value.trim();

        if (campo === "") {
            listaResultados.innerHTML = "";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "../php/getCodigos_cliente.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onload = function () {
            if (xhr.status === 200) {
                var resultados = JSON.parse(xhr.responseText);
                listaResultados.innerHTML = resultados;
            }
        };

        xhr.send("campo=" + campo);
    });

    // Agregar un listener para cada elemento li dentro de la lista
    listaResultados.addEventListener("click", function (event) {
        // Verificar si el clic se realizó en un elemento li
        if (event.target.tagName === "LI") {
            // Redirigir al usuario a la URL del href del li
            window.location.href = event.target.firstChild.href;
        }
    });

    // Cerrar la lista de resultados cuando se hace clic fuera de ella
    document.addEventListener("click", function (event) {
        // Verificar si el clic no se realizó dentro del campo de entrada ni dentro de la lista de resultados
        if (!campoInput.contains(event.target) && !listaResultados.contains(event.target)) {
            listaResultados.innerHTML = ""; // Limpiar la lista de resultados
        }
    });
});

//-------------------------------------------------------------------------------------------------------------
/*Funcion para cerrar modales dentro del CARRITO */
document.addEventListener('DOMContentLoaded', function () {

    // Obtén el botón de cierre del modal por su clase
    var closeButton = document.querySelector('#eliminaModal .modal-header .close');

    // Agrega un event listener al botón de cierre
    closeButton.addEventListener('click', function () {
        // Selecciona el modal y ciérralo
        var modal = document.querySelector('#eliminaModal');
        $(modal).modal('hide');
    });

    // Obtén el botón "Cerrar" del pie del modal por su clase
    var closeModalButton = document.querySelector('#eliminaModal .modal-footer button[data-dismiss="modal"]');

    // Agrega un event listener al botón "Cerrar" del pie del modal
    closeModalButton.addEventListener('click', function () {
        // Selecciona el modal y ciérralo
        var modal = document.querySelector('#eliminaModal');
        $(modal).modal('hide');
    });
});

//Funcion para validaciones----------------------------------------------------------------------------------------------------------------------

// Obtener referencias a los campos de nombres y apellidos
const nombresInput = document.getElementById('nombres');
const apellidoPaternoInput = document.getElementById('apellido_paterno');
const apellidoMaternoInput = document.getElementById('apellido_materno');
const distritoInput = document.getElementById('distrito');
const avenidaInput = document.getElementById('avenida');

// Obtener referencias a los elementos donde se mostrarán los mensajes de error
const nombresError = document.getElementById('validaNombres');
const apellidoPaternoError = document.getElementById('validaApellidoPaterno');
const apellidoMaternoError = document.getElementById('validaApellidoMaterno');
const distritoError = document.getElementById('validaDistrito');
const avenidaError = document.getElementById('validaAvenida');

// Función para validar que solo se ingresen letras y espacios en los nombres y apellidos
function validarCampo(input, errorElement) {
    // Obtener el valor del campo
    const valor = input.value.trim();
    // Expresión regular para validar que solo se ingresen letras y espacios
    const regex = /^[a-zA-Z\s]+$/;

    // Verificar si el valor del campo coincide con la expresión regular
    if (!regex.test(valor)) {
        // Mostrar mensaje de error si contiene caracteres no deseados
        errorElement.textContent = 'Solo se permiten letras y espacios';
    } else {
        // Limpiar el mensaje de error si el formato es correcto
        errorElement.textContent = '';
    }
}

// Función de manejo de eventos para el campo de nombres
function validarNombres() {
    validarCampo(nombresInput, nombresError);
}

// Función de manejo de eventos para el campo de apellido paterno
function validarApellidoPaterno() {
    validarCampo(apellidoPaternoInput, apellidoPaternoError);
}

// Función de manejo de eventos para el campo de apellido materno
function validarApellidoMaterno() {
    validarCampo(apellidoMaternoInput, apellidoMaternoError);
}

// Función de manejo de eventos para el campo de distrito
function validarDistrito() {
    validarCampo(distritoInput, distritoError);
}

// Función de manejo de eventos para el campo de avenida
function validarAvenida() {
    validarCampo(avenidaInput, avenidaError);
}

// Agregar eventos blur a los campos de nombres y apellidos
nombresInput.addEventListener('blur', validarNombres);
apellidoPaternoInput.addEventListener('blur', validarApellidoPaterno);
apellidoMaternoInput.addEventListener('blur', validarApellidoMaterno);
distritoInput.addEventListener('blur', validarDistrito);
avenidaInput.addEventListener('blur', validarAvenida);


//CELULAR-----------------------------------------------------------------------------
// Obtener referencia al campo de celular
const celularInput = document.getElementById('celular');
const numeroInput = document.getElementById('numero');

// Obtener referencia al elemento donde se mostrará el mensaje de error
const celularError = document.getElementById('validaCelular');
const numeroError = document.getElementById('validaNumero');

// Función para validar el campo de celular
function validarCelular() {
    // Obtener el valor del campo de celular y eliminar espacios en blanco
    const valor = celularInput.value.trim();
    // Expresión regular para verificar que el campo contenga solo números
    const regex = /^\d+$/;

    // Verificar si el valor del campo coincide con la expresión regular y tiene 8 dígitos
    if (!regex.test(valor) || valor.length !== 9) {
        // Mostrar mensaje de error si no cumple con los requisitos
        celularError.textContent = 'El número de celular debe contener 9 dígitos numéricos';
    } else {
        // Limpiar el mensaje de error si el formato es correcto
        celularError.textContent = '';
    }
}

// Función para validar el campo de numero
function validarNumero() {
    // Obtener el valor del campo de celular y eliminar espacios en blanco
    const valor = numeroInput.value.trim();
    // Expresión regular para verificar que el campo contenga solo números
    const regex = /^\d+$/;

    // Verificar si el valor del campo coincide con la expresión regular
    if (!regex.test(valor)) {
        // Mostrar mensaje de error si no cumple con los requisitos
        numeroError.textContent = 'Solo se permiten valores numéricos';
    } else {
        // Limpiar el mensaje de error si el formato es correcto
        numeroError.textContent = '';
    }
}

// Agregar evento blur al campo de celular y numero para activar la validación
celularInput.addEventListener('blur', validarCelular);
numeroInput.addEventListener('blur', validarNumero);


//FUNCION PARA NO PASAR EL FORMULARIO DE CAMBIO DE CONTRA SI SE ENCUENTRAN ERRORES------------------------
function guardarCambios() {
    // Verificar si hay algún span de alerta activo
    var spansDeAlerta = document.querySelectorAll('.text-danger');
    for (var i = 0; i < spansDeAlerta.length; i++) {
        if (spansDeAlerta[i].innerText !== "") {
            // Mostrar mensaje de error
            alert("Hay errores en el formulario. Por favor, verifica y vuelve a intentarlo.");
            // Evitar que se envíe el formulario
            return false;
        }
    }

    // Si no hay spans de alerta activos, permitir que se ejecute la acción del botón "Cambiar Contraseña"
    return true;
}


//---------------------------------------------------------------------------------------------------------------
/*Funcion para guardar los datos USUARIO en la base*/
$(document).ready(function () {
    // Manejar el evento de envío del formulario de edición
    $('#actualizarUsuario').submit(function (e) {
        // Detener el envío normal del formulario
        e.preventDefault();

        // Obtener los datos del formulario
        var formData = $(this).serialize() + '&accion=actualizarUsuario'; // Añadir un parámetro para indicar que es una actualización

        // Realizar una petición AJAX para enviar los datos al servidor
        $.ajax({
            type: 'POST',
            url: 'datos_usuario.php', // Archivo PHP que procesará la actualización
            data: formData,
            success: function (response) {
                // Manejar la respuesta del servidor
                alert(response); // Puedes mostrar un mensaje de éxito o hacer otras acciones
                // Actualizar la página o realizar otras acciones si es necesario
                location.reload();
            },
            error: function (xhr, status, error) {
                // Manejar los errores en caso de que ocurran
                alert("Error al actualizar el usuario: " + error);
            }
        });
    });
});


//FUNCION PARA NO PASAR EL FORMULARIO DE CAMBIO DE CONTRA SI SE ENCUENTRAN ERRORES------------------------
function cambiarContrasena() {
    // Verificar si hay algún span de alerta activo
    var spansDeAlerta = document.querySelectorAll('.text-danger');
    for (var i = 0; i < spansDeAlerta.length; i++) {
        if (spansDeAlerta[i].innerText !== "") {
            // Mostrar mensaje de error
            alert("Hay errores en el formulario. Por favor, verifica y vuelve a intentarlo.");
            // Evitar que se envíe el formulario
            return false;
        }
    }

    // Si no hay spans de alerta activos, permitir que se ejecute la acción del botón "Cambiar Contraseña"
    return true;
}


function guardarCambiosDireccion() {
    // Verificar si hay errores en el formulario
    var spansDeAlerta = document.querySelectorAll('.text-danger');
    for (var i = 0; i < spansDeAlerta.length; i++) {
        if (spansDeAlerta[i].innerText !== "") {
            // Mostrar mensaje de error
            alert("Hay errores en el formulario. Por favor, verifica y vuelve a intentarlo.");
            // Evitar que se envíe el formulario
            return false;
        }
    }

    // Obtener los valores del formulario
    var distritoPago = document.getElementById("distritoPago").value;
    var avenidaPago = document.getElementById("avenidaPago").value;
    var numeroPago = document.getElementById("numeroPago").value;
    var descripcionPago = document.getElementById("descripcionPago").value;

    // Verificar si los campos están llenos
    if (distritoPago.trim() === '' || avenidaPago.trim() === '' || numeroPago.trim() === '' || descripcionPago.trim() === '') {
        alert("Por favor completa todos los campos");
        return false;
    }

    // Crear un objeto FormData para enviar los datos al servidor
    var formData = new FormData();
    formData.append('accion', 'actualizarDireccion');
    formData.append('distritoPago', distritoPago);
    formData.append('avenidaPago', avenidaPago);
    formData.append('numeroPago', numeroPago);
    formData.append('descripcionPago', descripcionPago);

    // Realizar una solicitud AJAX para enviar los datos al servidor
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'pago_cliente.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            // Manejar la respuesta del servidor
            alert(xhr.responseText);
            // Recargar la página o realizar alguna otra acción si es necesario
            window.location.reload();
        } else {
            alert('Error al actualizar la dirección');
        }
    };
    xhr.send(formData);

    // Evitar que el formulario se envíe de forma convencional
    return false;
}
