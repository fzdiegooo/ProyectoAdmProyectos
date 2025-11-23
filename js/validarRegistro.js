//VALIDAR AGREGAR REGISTRO USUARIO en Registro Usuario

document.addEventListener("DOMContentLoaded", function () {
    $('#registerForm').submit(function (e) {
        //Evita el envio de datos
        e.preventDefault();
        //Condición que garantiza el correcto llenado de los campos antes de ser enviado los datos
        if (validarFormularioAgre()) {
            
            var formData = $(this).serialize() + '&accion=agregarU';
            //Se envian los datos mediante AJAX
            $.ajax({
                type: 'POST',
                url: 'index.php',//Destino de los datos
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
                    console.log("Respuesta completa del servidor:", xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al agregar el usuario: " + xhr.responseText,
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
    // Esto permite visualizar las validaciones ni bien se llenen los campos correspondientes del formulario

    document.getElementById("dni_U").addEventListener("input", function () {
        validarDni("dni_U", "validaDni_U");
    });

    document.getElementById("nombres_U").addEventListener("input", function () {
        validarTexto("nombres_U", "validaNombres_U");
    });

    document.getElementById("apellido_paterno_U").addEventListener("input", function () {
        validarTexto("apellido_paterno_U", "validaApellidoPaterno_U");
    });

    document.getElementById("apellido_materno_U").addEventListener("input", function () {
        validarTexto("apellido_materno_U", "validaApellidoMaterno_U");
    });

    document.getElementById("celular_U").addEventListener("input", function () {
        validarTelefonoAgre("celular_U", "validaCelular_U");
    });

    document.getElementById("fecha_nacimiento_U").addEventListener("input", function () {
        validarFechaNacimiento("fecha_nacimiento_U", "validaFechadenacimiento_U");
    });

    document.getElementById("email_U").addEventListener("input", function () {
        validarCorreoAgre("email_U", "validaEmail_U");
    });

    document.getElementById("password_U").addEventListener("input", function () {
        validarContrasenaAgre("password_U","confirmar_contrasena_U","validaPassword_U");
    });

    document.getElementById("confirmar_contrasena_U").addEventListener("input", function () {
        compararContrasena("password_U","confirmar_contrasena_U", "validaPassword_U");
    });

    document.getElementById("distrito_U").addEventListener("input", function () {
        validarTexto("distrito_U", "validaDistrito_U");
    });
    document.getElementById("avenida_U").addEventListener("input", function () {
        validarCampoVacio("avenida_U", "validaAvenida_U");
    });
    document.getElementById("numero_U").addEventListener("input", function () {
        validarCampoVacio("numero_U", "validaNumero_U");
    });
    document.getElementById("descripcion_U").addEventListener("input", function () {
        validarCampoVacio("descripcion_U", "validaDescripcion_U");
    });

    //Función que permite establecer el correcto llenado de los datos de todos los campos de su respectivo formulario
    function validarFormularioAgre() {
        console.log("Ejecutando validación del formulario de agregar...");
        let valido = true; // Inicia como válido
    
        // Ejecuta cada validación y actualiza 'valido' correctamente
        valido = validarDni("dni_U","validaDni_U") && valido;
        valido = validarTexto("nombres_U", "validaNombres_U") && valido;
        valido = validarTexto("apellido_paterno_U", "validaApellidoPaterno_U") && valido;
        valido = validarTexto("apellido_materno_U", "validaApellidoMaterno_U") && valido;
        valido = validarTelefonoAgre("celular_U", "validaCelular_U") && valido;
        valido = validarFechaNacimiento("fecha_nacimiento_U", "validaFechadenacimiento_U") && valido;
        valido = validarCorreoAgre("email_U", "validaEmail_U") && valido;
        valido = validarContrasenaAgre("password_U","confirmar_contrasena_U","validaPassword_U") && valido;
        valido = compararContrasena("password_U","confirmar_contrasena_U", "validaPassword_U") && valido;
        valido = validarTexto("distrito_U", "validaDistrito_U") && valido;
        valido = validarCampoVacio("avenida_U", "validaAvenida_U") && valido;
        valido = validarCampoVacio("numero_U", "validaNumero_U") && valido;
        valido = validarCampoVacio("descripcion_U", "validaDescripcion_U") && valido;
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

    
    function validarContrasenaAgre(campo1Id,campo2Id, errorId) {
        const campo1 = document.getElementById(campo1Id);
        const campo2 = document.getElementById(campo2Id);
        const error = document.getElementById(errorId);
    
        // Expresión regular que valida la contraseña
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/;
    
        if (!regex.test(campo1.value)) {
            error.textContent = "En el campo 'Contraseña' o 'Confirmar Contraseña' deben tener más de 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.";
            return false;
        }

        if (campo1.value != campo2.value) {
            error.textContent = "La contraseña no coiciden";
            return false;
        }
    
        // Si cumple los requisitos, borrar mensaje de error
        error.textContent = "";
        return true;
    }
    function compararContrasena(campo1Id,campo2Id, errorId) {
        const campo1 = document.getElementById(campo1Id);
        const campo2 = document.getElementById(campo2Id);
        const error = document.getElementById(errorId);

        // Expresión regular que valida la contraseña
    
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/;
    
        if ( !regex.test(campo1.value) || !regex.test(campo2.value)) {
            error.textContent = "En el campo 'Contraseña' o 'Confirmar Contraseña' deben tener más de 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.";
            return false;
        }
    
        if (campo1.value != campo2.value) {
            error.textContent = "La contraseña no coiciden";
            return false;
        }

        
        // Si cumple los requisitos, borrar mensaje de error
        error.textContent = "";
        return true;
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
        xhr.open("POST", "Clases/verificar_unicidad.php", false);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    
        let dniRepartidor = document.getElementById("dni_U").value.trim(); // Obtener el DNI del repartidor en edición
        let action = "agre_U";
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
        // Se envian los datos para la gestión correspondiente de búsqueda
        xhr.send(`tipo=${tipo}&valor=${encodeURIComponent(valor)}&dni=${dniRepartidor}&action=${action}`);
        return resultado;
    }
});
// Función que permite ver la contraseña de los usuarios
document.getElementById('verContraUsuario').addEventListener('click', function () {
    var passwordField = document.getElementById('password_U');// Se captura el Id del input contraseña
    var eyeIcon = document.getElementById('eyeIcon');// se captura el Id icono del ojo

    if (passwordField.type === "password") {// Si es de tipo password el input de la contraseña
        passwordField.type = "text";// Al darle clic se transforma en tipo text el cual permitara visualizar la contraseña
        eyeIcon.classList.remove("fa-eye");// se eliminar el icono del ojo tachado
        eyeIcon.classList.add("fa-eye-slash"); // y se reemplaza por el icono ojo
    } else {//Si no es de tipo password el input de la contraseña
        passwordField.type = "password";//Se establece de tipo password (para ocultar la contraseña nuevamente)
        eyeIcon.classList.remove("fa-eye-slash");// Se remueve icono del ojo 
        eyeIcon.classList.add("fa-eye"); // Se reemplaza por el icono ojo tachado
    }
});

//REGISTRO DESDE EL APARTADO ADMIN PARA CLIENTES
//Agregar Usuario
document.addEventListener("DOMContentLoaded", function () {
    $('#registerForm2').submit(function (e) {
        e.preventDefault();
        if (validarFormularioAgre2()) {
            
            var formData = $(this).serialize() + '&accion=agregarU';
            
            $.ajax({
                type: 'POST',
                url: 'clientes_admin.php',
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
                    console.log("Respuesta completa del servidor:", xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al agregar el usuario: " + xhr.responseText,
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


    document.getElementById("dni_U").addEventListener("input", function () {
        validarDni("dni_U", "validaDni_U");
    });

    document.getElementById("nombres_U").addEventListener("input", function () {
        validarTexto("nombres_U", "validaNombres_U");
    });

    document.getElementById("apellido_paterno_U").addEventListener("input", function () {
        validarTexto("apellido_paterno_U", "validaApellidoPaterno_U");
    });

    document.getElementById("apellido_materno_U").addEventListener("input", function () {
        validarTexto("apellido_materno_U", "validaApellidoMaterno_U");
    });

    document.getElementById("celular_U").addEventListener("input", function () {
        validarTelefonoAgre("celular_U", "validaCelular_U");
    });

    document.getElementById("fecha_nacimiento_U").addEventListener("input", function () {
        validarFechaNacimiento("fecha_nacimiento_U", "validaFechadenacimiento_U");
    });

    document.getElementById("email_U").addEventListener("input", function () {
        validarCorreoAgre("email_U", "validaEmail_U");
    });
    
    document.getElementById("direccion_U").addEventListener("input", function () {
        validarCampoVacio("direccion_U", "errorAgreDireccion_U");
    });

    document.getElementById("password_U").addEventListener("input", function () {
        validarContrasenaAgre("password_U","errorAgreContrasena_U");
    });

    //Función que permite establecer el correcto llenado de los datos de todos los campos de su respectivo formulario
    function validarFormularioAgre2() {
        console.log("Ejecutando validación del formulario de agregar...");
        let valido = true; // Inicia como válido
    
        // Ejecuta cada validación y actualiza 'valido' correctamente
        valido = validarDni("dni_U","validaDni_U") && valido;
        valido = validarTexto("nombres_U", "validaNombres_U") && valido;
        valido = validarTexto("apellido_paterno_U", "validaApellidoPaterno_U") && valido;
        valido = validarTexto("apellido_materno_U", "validaApellidoMaterno_U") && valido;
        valido = validarTelefonoAgre("celular_U", "validaCelular_U") && valido;
        valido = validarFechaNacimiento("fecha_nacimiento_U", "validaFechadenacimiento_U") && valido;
        valido = validarCorreoAgre("email_U", "validaEmail_U") && valido;
        valido = validarCampoVacio("direccion_U", "errorAgreDireccion_U") && valido;
        valido = validarContrasenaAgre("password_U","errorAgreContrasena_U") && valido;
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

        return verificarUnicidadAgreU("dni", campo.value, error);
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
        return verificarUnicidadAgreU("telefono", campo.value, error);
    }

    function validarCorreoAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe ingresar un correo válido.";
            return false;
        }

        return verificarUnicidadAgreU("correo", campo.value, error);
    }

    
    function validarContrasenaAgre(campo1Id, errorId) {
        const campo1 = document.getElementById(campo1Id);
        const error = document.getElementById(errorId);
    
        // Expresión regular que valida la contraseña
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{9,}$/;
    
        if (!regex.test(campo1.value)) {
            error.textContent = "En el campo 'Contraseña' o 'Confirmar Contraseña' deben tener más de 8 caracteres, incluir mayúsculas, minúsculas, números y caracteres especiales.";
            return false;
        }
        // Si cumple los requisitos, borrar mensaje de error
        error.textContent = "";
        return true;
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
            error.textContent = "El usuario debe tener al menos 18 años.";
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
    function verificarUnicidadAgreU(tipo, valor, errorElemento) {
        let resultado = false;
        let action = 'agre_U2';
        /*
        console.log("Enviando datos a verificar_unicidad.php:", {
            tipo: tipo,
            valor: valor,
            action: action
        });
        */
        $.ajax({
            type: "POST",
            url: "../Clases/verificar_unicidad.php",
            data: { tipo: tipo, valor: valor, action: action },
            dataType: "json",
            async: false, // Se usa false para garantizar que la respuesta se obtenga antes de continuar
            success: function (respuesta) {
                if (respuesta.unico) {
                    errorElemento.textContent = ""; // Si es único, no hay error
                    resultado = true;
                } else {
                    errorElemento.textContent = "Este " + tipo + " ya está registrado.";
                    resultado = false;
                }
            },
            error: function (xhr, status, error) {
                console.log("Error en la verificación de unicidad:", xhr.responseText);
                errorElemento.textContent = "Error al verificar " + tipo + ".";
                resultado = false;
            }
        });
    
        return resultado;
    }
    
});

//Editar Usuario
document.addEventListener("DOMContentLoaded", function () {
    $('#formEditarUsuario2').submit(function (e) {
        e.preventDefault();
        if (validarFormularioAgre3()) {
            
            var formData = $(this).serialize() + '&accion=editarU';
            
            $.ajax({
                type: 'POST',
                url: 'clientes_admin.php',
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
                    console.log("Respuesta completa del servidor:", xhr.responseText);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Hubo un problema al editar el usuario: " + xhr.responseText,
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


    document.getElementById("editNombre").addEventListener("input", function () {
        validarTexto("editNombre", "errorEditNombre");
    });

    document.getElementById("editApellidoPaterno").addEventListener("input", function () {
        validarTexto("editApellidoPaterno", "errorEditApellidoPaterno");
    });

    document.getElementById("editApellidoMaterno").addEventListener("input", function () {
        validarTexto("editApellidoMaterno", "errorEditApellidoMaterno");
    });

    document.getElementById("editCelular").addEventListener("input", function () {
        validarTelefonoAgre("editCelular", "errorEditCelular");
    });

    document.getElementById("editFechaNacimiento").addEventListener("input", function () {
        validarFechaNacimiento("editFechaNacimiento", "errorEditFechaNacimiento");
    });

    document.getElementById("editCorreo").addEventListener("input", function () {
        validarCorreoAgre("editCorreo", "errorEditCorreo");
    });
    


    document.getElementById("editDireccion").addEventListener("input", function () {
        validarCampoVacio("editDireccion", "errorEditDireccion");
    });

    function validarFormularioAgre3() {
        console.log("Ejecutando validación del formulario de agregar...");
        let valido = true; // Inicia como válido
    
        // Ejecuta cada validación y actualiza 'valido' correctamente
        valido = validarTexto("editNombre", "errorEditNombre") && valido;
        valido = validarTexto("editApellidoPaterno", "errorEditApellidoPaterno") && valido;
        valido = validarTexto("editApellidoMaterno", "errorEditApellidoMaterno") && valido;
        valido = validarTelefonoAgre("editCelular", "errorEditCelular") && valido;
        valido = validarFechaNacimiento("editFechaNacimiento", "errorEditFechaNacimiento") && valido;
        valido = validarCorreoAgre("editCorreo", "errorEditCorreo") && valido;
        valido =  validarCampoVacio("editDireccion", "errorEditDireccion") && valido;
        console.log("Resultado de validación:", valido);
        return valido;
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
        return verificarUnicidadEditU("telefono", campo.value, error);
    }

    function validarCorreoAgre(campoId, errorId) {
        const campo = document.getElementById(campoId);
        const error = document.getElementById(errorId);
        const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        if (!regex.test(campo.value)) {
            error.textContent = "Debe ingresar un correo válido.";
            return false;
        }

        return verificarUnicidadEditU("correo", campo.value, error);
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
            error.textContent = "El usuario debe tener al menos 18 años.";
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
       //Función que verifica que los datos sean unicos para determinados campos, dicha gestiòn lo hace el archivo verificar_unicidad2.php
    function verificarUnicidadEditU(tipo, valor, errorElemento) {
        let resultado = false;
        let action = 'edit_U';
        let dni_usuario = document.getElementById("editDniU").value.trim(); // obtener el DNI del usuario a acurrralizar
        /*
        console.log("Enviando datos a verificar_unicidad2.php:", {
            tipo: tipo,
            valor: valor,
            action: action,
            dni_usuario: dni_usuario
        });
        */
        $.ajax({
            type: "POST",
            url: "../Clases/verificar_unicidad2.php",
            data: { tipo: tipo, valor: valor, dni_usuario: dni_usuario, action: action },
            dataType: "json",
            async: false, // Se usa false para garantizar que la respuesta se obtenga antes de continuar
            success: function (respuesta) {
                if (respuesta.unico) {
                    errorElemento.textContent = ""; // Si es único, no hay error
                    resultado = true;
                } else {
                    errorElemento.textContent = "Este " + tipo + " ya está registrado.";
                    resultado = false;
                }
            },
            error: function (xhr, status, error) {
                console.log("Error en la verificación de unicidad:", xhr.responseText);
                errorElemento.textContent = "Error al verificar " + tipo + ".";
                resultado = false;
            }
        });
    
        return resultado;
    }
    
});
