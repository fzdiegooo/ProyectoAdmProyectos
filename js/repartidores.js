
document.addEventListener("DOMContentLoaded", function () {
    //ELIMINAR 2 REPARTIDOR
    $(document).ready(function () {
        // Manejar el evento de clic en el botón de eliminar producto
        $('.btnEliminar').click(function () {
          // Obtener el código del producto a eliminar desde el atributo data-codigo
          var codigoRepartidor = $(this).data('dni');

          Swal.fire({
            title: "¿Estás seguro?",
            text: "Esta acción eliminará el repartidor.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
            allowOutsideClick: false // Evita que se cierre al hacer clic fuera
        }).then((result) => {
            if (result.isConfirmed) {
                console.log("Enviando código a AJAX:", codigoRepartidor);
      
                $.ajax({
                    type: 'POST',
                    url: '../php/eliminar_repartidor.php',
                    data: {
                        dni: codigoRepartidor,
                        elimina: 'eliminar'
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
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
                            text: "Hubo un problema al eliminar el repartidor: " + xhr.responseText,
                            confirmButtonColor: "#d33",
                            allowOutsideClick: false, // Evita que se cierre al hacer clic fuera
                        });
                    }
                });
            } else {
                console.log("Eliminación cancelada por el usuario.");
            }
        });
        });
      });

    // 🟡 Editar repartidor - Abrir modal con datos
    document.querySelector("#tablaRepartidores").addEventListener("click", function (event) {
        if (event.target.classList.contains("btnEditar")) {
            let button = event.target;
            console.log("Botón Editar clickeado"); // Para depuración

            document.getElementById("editDni").value = button.dataset.dni;
            document.getElementById("editNombre").value = button.dataset.nombre;
            document.getElementById("editApellidoPaterno").value = button.dataset.apellidoPaterno;
            document.getElementById("editApellidoMaterno").value = button.dataset.apellidoMaterno;
            document.getElementById("editTelefono").value = button.dataset.telefono;
            document.getElementById("editFechaNacimiento").value = button.dataset.fechaNacimiento;
            document.getElementById("editCorreo").value = button.dataset.correo;
            document.getElementById("editGenero").value = button.dataset.genero;
            document.getElementById("editDireccion").value = button.dataset.direccion;
            document.getElementById("editPlaca").value = button.dataset.placa;
            document.getElementById("editVehiculo").value = button.dataset.vehiculo;

            // Mostrar el modal
            $("#modalEditarRepartidor").modal("show");
        }
    });
});

//Función que permite filtrar la tabla del apartado administradores de la secciòn Repartidores
$(document).ready(function () {
    function filtrarTabla() {
        let dni = $("#filtroDNIR").val().toLowerCase();
        let nombre = $("#filtroNombreR").val().toLowerCase();
        let apellidoP = $("#filtroApellidoPR").val().toLowerCase();
        let apellidoM = $("#filtroApellidoMR").val().toLowerCase();
        let celular = $("#filtroCelularR").val().toLowerCase();
        let correo = $("#filtroCorreoR").val().toLowerCase();
        let fecha = $("#filtroFechaR").val();
        let genero = $("#filtroGeneroR").val();

        $("#tablaRepartidores tbody tr").each(function () {
            let fila = $(this);
            let tdDNI = fila.find(".dni").text().toLowerCase();
            let tdNombre = fila.find(".nombre").text().toLowerCase();
            let tdApellidoP = fila.find(".apellido_paterno").text().toLowerCase();
            let tdApellidoM = fila.find(".apellido_materno").text().toLowerCase();
            let tdCelular = fila.find(".celular").text().toLowerCase();
            let tdCorreo = fila.find(".correo").text().toLowerCase();
            let tdFecha = fila.find(".fecha_nacimiento").text();
            let tdGenero = fila.find(".genero").text();

            let mostrar = 
                (tdDNI.includes(dni)) &&
                (tdNombre.includes(nombre)) &&
                (tdApellidoP.includes(apellidoP)) &&
                (tdApellidoM.includes(apellidoM)) &&
                (tdCelular.includes(celular)) &&
                (tdCorreo.includes(correo)) &&
                (fecha === "" || tdFecha === fecha) &&
                (genero === "" || tdGenero === genero);

            fila.toggle(mostrar);
        });
    }

    $("input, select").on("input change", filtrarTabla);
});


$(document).on('hidden.bs.modal', function () {
    $('.modal-backdrop').remove();
});

