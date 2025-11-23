$(document).ready(function () {
    function filtrarTabla() {
        let dni = $("#buscarDNI").val().toLowerCase();
        let nombre = $("#buscarNombre").val().toLowerCase();
        let apellidoP = $("#buscarApellidoP").val().toLowerCase();
        let apellidoM = $("#buscarApellidoM").val().toLowerCase();
        let estado = $("#filtroEstado").val();
        let genero = $("#filtroGenero").val();

        $("#tablaUsuarios tbody tr").each(function () {
            let fila = $(this);
            let tdDNI = fila.find(".dni").text().toLowerCase();
            let tdNombre = fila.find(".nombre").text().toLowerCase();
            let tdApellidoP = fila.find(".apellido_paterno").text().toLowerCase();
            let tdApellidoM = fila.find(".apellido_materno").text().toLowerCase();
            let tdEstado = fila.find(".estado").text().trim(); // Activo / Inactivo
            let tdGenero = fila.find(".genero").text().trim(); // Masculino / Femenino / Otro

            let mostrar = 
                tdDNI.includes(dni) &&
                tdNombre.includes(nombre) &&
                tdApellidoP.includes(apellidoP) &&
                tdApellidoM.includes(apellidoM) &&
                (estado === "" || tdEstado === estado) &&
                (genero === "" || tdGenero === genero);

            fila.toggle(mostrar);
        });
    }

    $("input, select").on("input change", filtrarTabla);
});