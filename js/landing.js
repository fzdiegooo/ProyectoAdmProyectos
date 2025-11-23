//----------------------------------------------------------------------------------------------------------------------
/*Rutear para otra ventana*/
function redirectToFarmacia() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "farmacia.php";
}

function redirectToSuplementos() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "suplementos_vitaminas.php";
}

function redirectToNutricion() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "nutricion_deportiva.php";
}

function redirectToBelleza() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cuidado_belleza.php";
}

function redirectToAromaterapia() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "aromaterapia.php";
}

function redirectToCliente() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cliente-page.php";
}

function redirectToLanding() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "index.php";
}

function redirectToDetallesCarrito() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "carritodetalles.php";
}

function redirectToCliente() {
    // Puedes cambiar la URL según la estructura de tu proyecto
    window.location.href = "cliente-page.php";
}


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
    let url = 'Clases/carrito.php';
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
                    window.location.href = "carritodetalles.php"; // Redirige al carrito
                }
            })
        });
        if (comprarAhora) {
            window.location.href = "carritodetalles.php"; // Redirige si es compra inmediata
        }
}


//-------------------------------------------------------------------------------------------------------------
/*Funcion buscador en landing */
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
        xhr.open("POST", "php/getCodigos.php", true);
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
/*Funcion para abrir login al precionar Realizar Pago en el CARRITO*/
// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    // Obtén el botón "Realizar Pago" por su ID
    var realizarPagoBtn = document.getElementById('realizarPagoBtn');

    // Agrega un evento de clic al botón "Realizar Pago"
    realizarPagoBtn.addEventListener('click', function () {
        // Obtén el modal de inicio de sesión por su ID y ábrelo
        var modalLogin = document.getElementById('loginModal');
        // Abre el modal de inicio de sesión
        $(modalLogin).modal('show');
    });
});

//-------------------------------------------------------------------------------------------------------------
/*Funcion para abrir login al precionar Realizar Pago en el CARRITO*/
// Espera a que el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    // Obtén el botón "Realizar Pago" por su ID
    var realizarPagoBtn = document.getElementById('realizarPagoBtn');

    // Agrega un evento de clic al botón "Realizar Pago"
    realizarPagoBtn.addEventListener('click', function () {
        // Obtén el modal de inicio de sesión por su ID y ábrelo
        var modalLogin = document.getElementById('loginModal');
        // Abre el modal de inicio de sesión
        $(modalLogin).modal('show');
    });
});

//-------------------------------------------------------------------------------------------------------------
/*Funcion para cerrar modales dentro del CARRITO */
document.addEventListener('DOMContentLoaded', function () {

    // Obtén el botón de cierre del modal por su clase
    var closeLogin = document.querySelector('#loginModal .modal-header .close');

    // Agrega un event listener al botón de cierre
    closeLogin.addEventListener('click', function () {
        // Selecciona el modal y ciérralo
        var modal = document.querySelector('#loginModal');
        $(modal).modal('hide');
    });

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

//Funcion para confirmas contraseña----------------------------------------------------------------------------------------------------------------------

//LOGIN
const pass = document.getElementById("contrasena");
const showIcon = document.getElementById("hidePassword");
const hideIcon = document.getElementById("showPassword");

showIcon.addEventListener("click", function () {
    pass.type = "text";
    showIcon.style.display = "none";
    hideIcon.style.display = "inline-block";
});

hideIcon.addEventListener("click", function () {
    pass.type = "password";
    hideIcon.style.display = "none";
    showIcon.style.display = "inline-block";
});

//CREAR CUENTA
const password = document.getElementById("password_U");
const ocultarIcon = document.getElementById("ocultarPassword");
const mostrarIcon = document.getElementById("verPassword");

ocultarIcon.addEventListener("click", function () {
    password.type = "text";
    //password.type = "password";
    ocultarIcon.style.display = "none";
    mostrarIcon.style.display = "inline-block";
});

mostrarIcon.addEventListener("click", function () {
    password.type = "password";
    //password.type = "text";
    mostrarIcon.style.display = "none";
    ocultarIcon.style.display = "inline-block";
});

const passrd = document.getElementById("confirmar_contrasena_U");
const oculIcon = document.getElementById("oculIcon");
const mostIcon = document.getElementById("mostIcon");

oculIcon.addEventListener("click", function () {
    passrd.type = "text";
    oculIcon.style.display = "none";
    mostIcon.style.display = "inline-block";
});

mostIcon.addEventListener("click", function () {
    passrd.type = "password";
    mostIcon.style.display = "none";
    oculIcon.style.display = "inline-block";
});


/*Funcion para validaciones AJAX */

//DATOS REGISTRAR USUARIOS

//DNI
let txtDni = document.getElementById('dni');
txtDni.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo DNI');
    dniExiste(txtDni.value);
})


let txtCorreo = document.getElementById('email');
txtCorreo.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo Correo');
    emailExiste(txtCorreo.value);
})
/*
let txtCelular = document.getElementById('celular');
txtCelular.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo celular');
    celularExiste(txtCelular.value);
})
*/
let txtPassword = document.getElementById('password');
let txtConfirmarContrasena = document.getElementById('confirmar_contrasena');

txtPassword.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo Contraseña');
    validaPassword(txtPassword.value, txtConfirmarContrasena.value);
})

txtConfirmarContrasena.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo Confirmar Contraseña');
    validaPassword(txtPassword.value, txtConfirmarContrasena.value);
})

let txtFechaNacimiento = document.getElementById('fecha_nacimiento');
txtFechaNacimiento.addEventListener("blur", function () {
    validarFechaNacimiento(txtFechaNacimiento.value);
})

//VALIDACIONES PARA EDITAR UN REPARTIDOR

// AGREGAR

//LLamado del DNI
let agreDNI = document.getElementById('agreDNI');

agreDNI.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo DNI');
    dniExiste(agreDNI.value);
});



// MODIFICAR


//LLamado del Telefono/celular
let editTelefono = document.getElementById('editTelefono');

editTelefono.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo Telefono');
    celularExiste(editTelefono.value);
});

//LLamado del correo
let editCorreo = document.getElementById('editCorreo');

editCorreo.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo correo');
    emailExiste(editCorreo.value);
});

//LLamado del Fecha Nacimiento
let editFechaNacimiento = document.getElementById('editFechaNacimiento');

editFechaNacimiento.addEventListener("blur", function () {
    console.log('Evento blur activado en el campo correo');
    emailExiste(editFechaNacimiento.value);
});

function dniExiste(dni) {
    console.log('Iniciando la función dniExiste');

    // Verificar si el valor ingresado tiene exactamente 8 dígitos y no contiene caracteres no numéricos
    if (/^\d{8}$/.test(dni)) {
        document.getElementById('validaDni').innerHTML = '';
        let url = "Clases/clientesAjax.php";
        let formData = new FormData();
        formData.append("action", "dniExiste");
        formData.append("dni", dni);

        fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
            .then(data => {
                if (data.ok) {
                    document.getElementById('validaDni').innerHTML = 'El DNI ya existe';
                } else {
                    document.getElementById('validaDni').innerHTML = '';
                }
            });
    } else {
        // Mostrar retroalimentación al usuario indicando que solo se permiten 8 dígitos
        document.getElementById('validaDni').innerHTML = 'Ingrese solo 8 dígitos numéricos';
    }
}



function emailExiste(email) {
    console.log('Iniciando la función emailExiste');
    let url = "Clases/repartidorAjax.php"
    let formData = new FormData()
    formData.append("action", "emailExiste")
    formData.append("correo", email)

    fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => response.json())
        .then(data => {
            if (data.ok) {
                document.getElementById('errorEditCorreo').innerHTML = 'El correo ya existe'
            } else {
                document.getElementById('errorEditCorreo').innerHTML = ''
            }
        });
}

function celularExiste(celular) {
    console.log('Iniciando la función celularExiste');

    // Verifica si el celular contiene solo dígitos y tiene una longitud de 9 caracteres
    if (/^\d{9}$/.test(celular)) { // Expresión regular actualizada
        let url = "Clases/repartidorAjax.php"
        let formData = new FormData()
        formData.append("action", "celularExiste")
        formData.append("celular", celular)

        fetch(url, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
            .then(data => {
                if (data.ok) {
                    document.getElementById('errorEditTelefono').innerHTML = 'El celular ya existe';
                } else {
                    document.getElementById('errorEditTelefono').innerHTML = '';
                }
            });
    } else {
        // Mostrar retroalimentación al usuario indicando que solo se permiten 9 dígitos
        document.getElementById('errorEditTelefono').innerHTML = 'Ingrese solo 9 dígitos numéricos';
    }
}



function validaPassword(password, confirmar_contrasena) {
    console.log('Iniciando la función validaPassword');
    let url = "Clases/clientesAjax.php";
    let formData = new FormData();
    formData.append("action", "validaPassword");
    formData.append("password", password);
    formData.append("confirmar_contrasena", confirmar_contrasena);

    fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => response.json())
        .then(data => {
            document.getElementById('validaPassword').innerHTML = data.mensaje;
        }).catch(error => {
            console.error('Error al realizar la solicitud:', error);
        });
}

function validarFechaNacimiento(fecha_nacimiento) {
    console.log('Iniciando la función validarFechaNacimiento');
    let url = "Clases/repartidorAjax.php";
    let formData = new FormData();
    formData.append("action", "validarFechaNacimiento");
    formData.append("fecha_nacimiento", fecha_nacimiento);

    fetch(url, {
        method: 'POST',
        body: formData
    }).then(response => response.json())
        .then(data => {
            console.log('Respuesta recibida:', data);
            if (data.ok) {
                document.getElementById('errorEditFechaNacimiento').innerHTML = '';
            } else {
                document.getElementById('errorEditFechaNacimiento').innerHTML = data.mensaje;
            }
        }).catch(error => {
            console.error('Error al realizar la solicitud:', error);
        });
}


