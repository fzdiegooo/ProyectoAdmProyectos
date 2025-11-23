$(document).ready(function () {
    // Manejar el evento de envío del formulario de edición
    $('#formEditarRepartidor').submit(function (e) {
        
        // Detener el envío normal del formulario
        e.preventDefault();
        //Si todo esta conforme en los campos se envia los datos para la acutalizacion
        if (validarFormularioEdit()) {
            // Obtener los datos del formulario
            var formData = $(this).serialize() + '&accion=editar'; // Añadir un parámetro para indicar que es una actualización
        
            // Realizar una petición AJAX para enviar los datos al servidor
            $.ajax({
                type: 'POST',
                url: '../Admin/repartidores_admin.php', // Archivo PHP que procesará la actualización
                data: formData,
                dataType: 'json', // Asegurar que la respuesta sea tratada como JSON
                success: function (data) {
                    if (data.status === "success") { // Si la respuesta es positiva
                        Swal.fire({
                            icon: "success",
                            title: "Éxito",
                            text: data.message,
                            confirmButtonColor: "#3085d6",
                            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({//Si la respuesta es negativa
                            icon: "error",
                            title: "Error",
                            text: data.message,
                            confirmButtonColor: "#d33",
                            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al actualizar el repartidor: " + error,
                        confirmButtonColor: "#d33",
                        allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                    });
                }
            });
        }          

    });

    // Asigna eventos de validación en tiempo real
    // Esto permite visualizar las validaciones ni bien se llenen los campos correspondientes del formulario

    document.getElementById("editNombre").addEventListener("input", function () {
        validarTexto("editNombre", "errorEditNombre");
    });

    document.getElementById("editApellidoPaterno").addEventListener("input", function () {
        validarTexto("editApellidoPaterno", "errorEditApellidoPaterno");
    });

    document.getElementById("editApellidoMaterno").addEventListener("input", function () {
        validarTexto("editApellidoMaterno", "errorEditApellidoMaterno");
    });

    document.getElementById("editTelefono").addEventListener("input", function () {
        validarTelefonoEdit("editTelefono", "errorEditTelefono");
    });

    document.getElementById("editFechaNacimiento").addEventListener("input", function () {
        validarFechaNacimiento("editFechaNacimiento", "errorEditFechaNacimiento");
    });

    document.getElementById("editCorreo").addEventListener("input", function () {
        validarCorreoEdit("editCorreo", "errorEditCorreo");
    });

    
    document.getElementById("editGenero").addEventListener("input", function () {
        validarCampoVacio("editGenero", "errorEditGenero");
    });

    document.getElementById("editDireccion").addEventListener("input", function () {
        validarCampoVacio("editDireccion", "errorEditDireccion");
    });

    document.getElementById("editPlaca").addEventListener("input", function () {
        validarPlacaEdit("editPlaca", "errorEditPlaca");
    });

    document.getElementById("editVehiculo").addEventListener("input", function () {
        validarCampoVacio("editVehiculo", "errorEditVehiculo");
    });


    //Función que permite establecer el correcto llenado de los datos de todos los campos de su respectivo formulario
    function validarFormularioEdit() {
        let valido = true;//Variable de validez para los campos

        //Se recupera toda la informacion de los campos del registro editar a través de sus id's
        // Validar Nombre
        valido &= validarTexto("editNombre", "errorEditNombre");

        // Validar Apellidos
        valido &= validarTexto("editApellidoPaterno", "errorEditApellidoPaterno");
        valido &= validarTexto("editApellidoMaterno", "errorEditApellidoMaterno");

        // Validar Celular
        valido &= validarTelefonoEdit("editTelefono", "errorEditTelefono");

        // Validar Fecha de Nacimiento
        valido &= validarFechaNacimiento("editFechaNacimiento", "errorEditFechaNacimiento");

        // Validar Correo Electrónico
        valido &= validarCorreoEdit("editCorreo", "errorEditCorreo");

        //Validar Genero
        valido &= validarCampoVacio("editGenero", "errorEditGenero");
        // Validar Dirección
        valido &= validarCampoVacio("editDireccion", "errorEditDireccion");

        // Validar Placa
        valido &= validarPlacaEdit("editPlaca", "errorEditPlaca");

        // Validar Vehículo
        valido &= validarCampoVacio("editVehiculo", "errorEditVehiculo");

        return !!valido; // Convertir a booleano
    }

    
    
    function validarTexto(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        //Formato que permite establecer solo letras
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;

        if (!campo.value.trim()) {
            error.textContent = "Este campo no puede estar vacío.";
            return false;
        }
        if (!regex.test(campo.value)) {
            error.textContent = "Solo se permiten letras.";
            return false;
        }
        // El campo span resulta vacio si todo esta en orden
        error.textContent = "";
        return true;
    }

    function validarTelefonoEdit(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^9\d{8}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe comenzar con 9 y tener 9 dígitos.";
            return false;
        }
        // Verificar si el celular es único
        return verificarUnicidadEdit("telefono", campo.value, error);
    }


    function validarFechaNacimiento(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const fechaIngresada = new Date(campo.value);
        const hoy = new Date();
        const edad = hoy.getFullYear() - fechaIngresada.getFullYear();

        if (isNaN(fechaIngresada.getTime())) {
            error.textContent = "Debe seleccionar una fecha válida.";
            return false;
        }

        if (edad < 18 || (edad === 18 && hoy < new Date(hoy.getFullYear(), fechaIngresada.getMonth(), fechaIngresada.getDate()))) {
            error.textContent = "El repartidor debe tener al menos 18 años.";
            return false;
        }

        error.textContent = "";
        return true;
    }

    function validarCorreoEdit(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe ingresar un correo válido.";
            return false;
        }
        return verificarUnicidadEdit("correo", campo.value, error);
    }

    function validarPlacaEdit(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^\d{4}-[A-Z]{3}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Formato incorrecto (Ejemplo: 1234-ABC).";
            return false;
        }
        return verificarUnicidadEdit("placa", campo.value, error);
    }

   

    function validarCampoVacio(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);

        if (!campo.value.trim()) {
            error.textContent = "Este campo no puede estar vacío.";
            return false;
        }else{
            error.textContent = "";
        }

        
        return true;
    }
    //Función que verifica que los datos sean unicos para determinados campos, dicha gestiòn lo hace el archivo verificar_unicidad.php
    function verificarUnicidadEdit(tipo, valor, errorElemento) {
        let resultado = false;
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "../Clases/verificar_unicidad2.php", false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
        let dniRepartidor = document.getElementById("editDni").value.trim(); // Obtener el DNI del repartidor en edición
        let action = "edit";
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                console.log(xhr.responseText); // Para depuración
    
                if (xhr.status === 200) {
                    if (xhr.responseText.trim() === "existe") {
                        errorElemento.textContent = `El ${tipo} ya está registrado.`;
                        resultado = false;
                    } else {
                        errorElemento.textContent = "";
                        resultado = true;
                    }
                }
            }
        };
    
        xhr.send(`tipo=${tipo}&valor=${encodeURIComponent(valor)}&dni=${dniRepartidor}&action=${action}`);
        return resultado;
    }

   
    

});

// Función que permite ver la contraseña de los REPARTIDORES
document.getElementById('verContraRepartidor').addEventListener('click', function () {
    var passwordField = document.getElementById('agreContrasena');// Se captura el Id del input contraseña
    var eyeIcon = document.getElementById('eyeIcon');// se captura el Id icono del ojo

    if (passwordField.type === "password") {// Si es de tipo password el input de la contraseña
        passwordField.type = "text";// Al darle clic se transforma en tipo text el cual permitara visualizar la contraseña
        eyeIcon.classList.remove("fa-eye");// se eliminar el icono del ojo
        eyeIcon.classList.add("fa-eye-slash"); // y se reemplaza por el icono ojo tachado
    } else {//Si no es de tipo password el input de la contraseña
        passwordField.type = "password";//Se establece de tipo password (para ocultar la contraseña nuevamente)
        eyeIcon.classList.remove("fa-eye-slash");// Se remueve icono del ojo tachado
        eyeIcon.classList.add("fa-eye"); // Se reemplaza por el icono ojo
    }
});


//VALIDAR AGREGAR REGISTRO REPARTIDOR
document.addEventListener("DOMContentLoaded", function () {

    $('#validar').submit(function (e) {
    
        e.preventDefault();
        if (validarFormularioAgre()) {
            
            var formData = $(this).serialize() + '&accion=agregar';
            
            $.ajax({
                type: 'POST',
                url: '../Admin/repartidores_admin.php',
                data: formData,
                dataType: 'json',
                success: function (data) {
                    if (data.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Éxito",
                            text: data.message,
                            confirmButtonColor: "#3085d6",
                            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: data.message,
                            confirmButtonColor: "#d33",
                            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al agregar el repartidor: " + error,
                        confirmButtonColor: "#d33",
                        allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                    });
                }
            });
        } else {
            console.log("Validación fallida, no se envían datos.");
        }
    });


    // Asigna eventos de validación en tiempo real


    document.getElementById("agreDni").addEventListener("input", function () {
        validarDni("agreDni", "errorAgreDni");
    });

    document.getElementById("agreNombre").addEventListener("input", function () {
        validarTexto("agreNombre", "errorAgreNombre");
    });

    document.getElementById("agreApellidoPaterno").addEventListener("input", function () {
        validarTexto("agreApellidoPaterno", "errorAgreApellidoPaterno");
    });

    document.getElementById("agreApellidoMaterno").addEventListener("input", function () {
        validarTexto("agreApellidoMaterno", "errorAgreApellidoMaterno");
    });

    document.getElementById("agreTelefono").addEventListener("input", function () {
        validarTelefonoAgre("agreTelefono", "errorAgreTelefono");
    });

    document.getElementById("agreFechaNacimiento").addEventListener("input", function () {
        validarFechaNacimiento("agreFechaNacimiento", "errorAgreFechaNacimiento");
    });

    document.getElementById("agreCorreo").addEventListener("input", function () {
        validarCorreoAgre("agreCorreo", "errorAgreCorreo");
    });
    

    document.getElementById("agreContrasena").addEventListener("input", function () {
        validarContrasenaAgre("agreContrasena", "errorAgreContrasena");
    });

    document.getElementById("agreGenero").addEventListener("input", function () {
        validarTexto("agreGenero", "errorAgreGenero");
    });

    document.getElementById("agreDireccion").addEventListener("input", function () {
        validarCampoVacio("agreDireccion", "errorAgreDireccion");
    });

    document.getElementById("agrePlaca").addEventListener("input", function () {
        validarPlacaAgre("agrePlaca", "errorAgrePlaca");
    });

    document.getElementById("agreVehiculo").addEventListener("input", function () {
        validarCampoVacio("agreVehiculo", "errorAgreVehiculo");
    });
   
    //Función que permite establecer el correcto llenado de los datos de todos los campos de su respectivo formulario
    function validarFormularioAgre() {
        console.log("Ejecutando validación del formulario de agregar...");
        let valido = true; // Inicia como válido
    
        // Ejecuta cada validación y actualiza 'valido' correctamente
        valido = validarDni("agreDni","errorAgreDni") && valido;
        valido = validarTexto("agreNombre", "errorAgreNombre") && valido;
        valido = validarTexto("agreApellidoPaterno", "errorAgreApellidoPaterno") && valido;
        valido = validarTexto("agreApellidoMaterno", "errorAgreApellidoMaterno") && valido;
        valido = validarTelefonoAgre("agreTelefono", "errorAgreTelefono") && valido;
        valido = validarFechaNacimiento("agreFechaNacimiento", "errorAgreFechaNacimiento") && valido;
        valido = validarCorreoAgre("agreCorreo", "errorAgreCorreo") && valido;
        valido = validarContrasenaAgre("agreContrasena", "errorAgreContrasena") && valido;
        valido = validarCampoVacio("agreContrasena","errorAgreContrasena") && valido;
        valido = validarCampoVacio("agreGenero", "errorAgreGenero") && valido;
        valido = validarCampoVacio("agreDireccion", "errorAgreDireccion") && valido;
        valido = validarPlacaAgre("agrePlaca", "errorAgrePlaca") && valido;
        valido = validarCampoVacio("agreVehiculo", "errorAgreVehiculo") && valido;
    
        console.log("Resultado de validación:", valido);
        return valido;
    }
    

    function validarDni(campoId, errorId){

        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex =/^\d{8}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "DNI debe contener 8 dígitos numéricos.";
            return false;
        }

        return verificarUnicidadAgre("dni", campo.value, error);
    }


    function validarTexto(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        //Formato que permite establecer solo letras
        const regex = /^[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+$/;

        if (!campo.value.trim()) {
            error.textContent = "Este campo no puede estar vacío.";
            return false;
        }
        if (!regex.test(campo.value)) {
            error.textContent = "Solo se permiten letras.";
            return false;
        }
        // El campo span resulta vacio si todo esta en orden
        error.textContent = "";
        return true;
    }

    function validarTelefonoAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^9\d{8}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe comenzar con 9 y tener 9 dígitos.";
            return false;
        }
        // Verificar si el celular es único
        return verificarUnicidadAgre("telefono", campo.value, error);
    }

    function validarCorreoAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe ingresar un correo válido.";
            return false;
        }
        return verificarUnicidadAgre("correo", campo.value, error);
    }


    function validarContrasenaAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
    
        // Expresión regular que valida la contraseña
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/;
    
        if (!regex.test(campo.value)) {
            error.textContent = "Debe tener más de 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.";
            return false;
        }else{
            error.textContent = "";
        }
    
        // Si cumple los requisitos, borrar mensaje de error

        return true;
    }

    function validarPlacaAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^\d{4}-[A-Z]{3}$/;
    
        if (!regex.test(campo.value)) {
            error.textContent = "Formato incorrecto (Ejemplo: 1234-ABC).";
            return false;
        }
        
        error.textContent = ""; // Asegurar que si está bien, el error desaparece
        return verificarUnicidadAgre("placa", campo.value, error);
    }
    

    function validarFechaNacimiento(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const fechaIngresada = new Date(campo.value);
        const hoy = new Date();
        const edad = hoy.getFullYear() - fechaIngresada.getFullYear();

        if (isNaN(fechaIngresada.getTime())) {
            error.textContent = "Debe seleccionar una fecha válida.";
            return false;
        }

        if (edad < 18 || (edad === 18 && hoy < new Date(hoy.getFullYear(), fechaIngresada.getMonth(), fechaIngresada.getDate()))) {
            error.textContent = "El repartidor debe tener al menos 18 años.";
            return false;
        }

        error.textContent = "";
        return true;
    }


    function validarCampoVacio(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);

        if (!campo.value.trim()) {
            error.textContent = "Este campo no puede estar vacío.";
            return false;
        }else{
            error.textContent = "";
        }
        return true;
    }
    
    //Función que verifica que los datos sean unicos para determinados campos, dicha gestiòn lo hace el archivo verificar_unicidad.php
    function verificarUnicidadAgre(tipo, valor, errorElemento) {
        let resultado = false;
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "../Clases/verificar_unicidad.php", false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
        let dniRepartidor = document.getElementById("agreDni").value.trim(); // Obtener el DNI del repartidor en edición
        let action = "agre";
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                console.log(xhr.responseText); // Para depuración
    
                if (xhr.status === 200) {
                    if (xhr.responseText.trim() === "existe") {
                        errorElemento.textContent = `El ${tipo} ya está registrado.`;
                        resultado = false;
                    } else {
                        errorElemento.textContent = "";
                        resultado = true;
                    }
                }
            }
        };
    
        xhr.send(`tipo=${tipo}&valor=${encodeURIComponent(valor)}&dni=${dniRepartidor}&action=${action}`);
        return resultado;
    }
});

