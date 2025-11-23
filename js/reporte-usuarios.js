document.getElementById('exportUsuarios').addEventListener('click', function () {
    let table = document.getElementById('tablaUsuarios').cloneNode(true); // Clonar la tabla para modificarla sin afectar la original

    // Eliminar la última columna (Acciones) de la cabecera si existe
    let headerRow = table.querySelector("thead tr");
    if (headerRow.lastElementChild.textContent.trim().toLowerCase() === "acciones") {
        headerRow.lastElementChild.remove();
    }

    // Eliminar la última celda (Acciones) de cada fila en el cuerpo de la tabla
    table.querySelectorAll("tbody tr").forEach(row => {
        if (row.lastElementChild) {
            row.lastElementChild.remove();
        }
    });

    // Convertir la tabla modificada en hoja de cálculo
    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.table_to_sheet(table);
    XLSX.utils.book_append_sheet(wb, ws, "Usuarios");

    // Descargar el archivo Excel
    XLSX.writeFile(wb, "REPORTE-USUARIOS.xlsx");
});