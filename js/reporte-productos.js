document.getElementById('exportProductos').addEventListener('click', function () {
    let table = document.getElementById('tablaProductos').cloneNode(true); // Clonar la tabla para modificarla sin afectar la original

    // Eliminar la última columna (Acciones) de la cabecera
    table.querySelector("thead tr").lastElementChild.remove();

    // Eliminar la última celda de cada fila del cuerpo de la tabla
    table.querySelectorAll("tbody tr").forEach(row => {
        row.lastElementChild.remove();
    });

    // Convertir la tabla modificada en hoja de cálculo
    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.table_to_sheet(table);
    XLSX.utils.book_append_sheet(wb, ws, "Productos");

    // Descargar el archivo Excel
    XLSX.writeFile(wb, "REPORTE-PRODUCTOS.xlsx");
});