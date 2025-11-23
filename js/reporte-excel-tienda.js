//PARA LA SECCION DEL REPARTIDOR
/*
document.addEventListener('DOMContentLoaded', function () {
    function exportarExcel(tipo) {
        let tabla;

        if (tipo === 'productos') {
            tabla = document.getElementById('tablaProductos');
        } else if (tipo === 'usuarios') {
            tabla = document.getElementById('tablaUsuarios');
        }

        // Verificar si la tabla existe
        if (!tabla) {
            alert("No se encontró la tabla de " + tipo);
            return;
        }

        // Clonar la tabla para evitar modificar la original en la página
        let clonedTable = tabla.cloneNode(true);

        // Si es la tabla de productos, eliminar la columna "Acciones"
        if (tipo === 'productos') {
            let clonedThead = clonedTable.querySelector('thead tr');
            let clonedTbodyRows = clonedTable.querySelectorAll('tbody tr');

            // Buscar la columna "Acciones" y eliminarla
            let headers = Array.from(clonedThead.children);
            let accionesIndex = headers.findIndex(th => th.textContent.trim().toLowerCase() === "acciones");

            if (accionesIndex !== -1) {
                clonedThead.removeChild(headers[accionesIndex]); // Elimina la cabecera "Acciones"

                clonedTbodyRows.forEach(row => {
                    let cells = row.children;
                    if (cells.length > accionesIndex) {
                        row.removeChild(cells[accionesIndex]); // Elimina la celda correspondiente en cada fila
                    }
                });
            }
        }

        // Convertir la tabla en un archivo Excel
        let workbook = XLSX.utils.table_to_book(clonedTable, { sheet: tipo === 'productos' ? "Productos" : "Usuarios" });

        // Nombre del archivo según el tipo
        let fileName = tipo === 'productos' ? "Reporte_Productos.xlsx" : "Reporte_Usuarios.xlsx";

        // Descargar el archivo
        XLSX.writeFile(workbook, fileName);
    }

    // Esperar a que los botones existan antes de asignar eventos
    setTimeout(() => {
        let btnProductos = document.getElementById('exportProductos');
        let btnUsuarios = document.getElementById('exportUsuarios');

        if (btnProductos) {
            btnProductos.addEventListener('click', function () {
                exportarExcel('productos');
            });
        }

        if (btnUsuarios) {
            btnUsuarios.addEventListener('click', function () {
                exportarExcel('usuarios');
            });
        }
    }, 500); // Pequeño retraso para asegurar que los elementos estén en el DOM
});
*/
//Funciòn que permite exportar los datos de la tabla de repartidores
document.getElementById("exportRepartidores").addEventListener("click", function() {
    let tabla = document.getElementById("tablaRepartidores");

    // Crear un nuevo array para almacenar los datos sin la última columna
    let data = [];
    let headers = [];

    // Obtener encabezados de la tabla (excluyendo la última columna "Acciones")
    let ths = tabla.querySelectorAll("thead th");
    for (let i = 0; i < ths.length - 1; i++) { // Ignorar última columna
        headers.push(ths[i].innerText.trim());
    }
    data.push(headers); // Agregar encabezados al array

    // Obtener SOLO las filas visibles de la tabla
    let filas = tabla.querySelectorAll("tbody tr");
    filas.forEach(fila => {
        if (fila.style.display !== "none") { // Solo incluir filas visibles
            let rowData = [];
            let celdas = fila.querySelectorAll("td");

            for (let i = 0; i < celdas.length - 1; i++) { // Ignorar última columna
                rowData.push(celdas[i].innerText.trim());
            }

            data.push(rowData);
        }
    });

    // Crear la hoja de Excel sin la columna "Acciones"
    let wb = XLSX.utils.book_new();
    let ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, "Repartidores");

    // Generar y descargar el archivo Excel
    XLSX.writeFile(wb, "REPORTE_REPARTIDOR.xlsx");
});