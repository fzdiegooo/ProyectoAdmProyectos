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