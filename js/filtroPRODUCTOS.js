document.addEventListener("DOMContentLoaded", function () {
    const filtroCategoria = document.getElementById("filtroCategoria");
    const buscarCodigo = document.getElementById("buscarCodigo");
    const buscarDescripcion = document.getElementById("buscarDescripcion");
    const tabla = document.querySelector(".table tbody");

    function obtenerCategoriasSeleccionadas() {
        const seleccionadas = Array.from(filtroCategoria.selectedOptions).map(option => option.value);
        return seleccionadas.includes("0") ? [] : seleccionadas; // Si selecciona "Todas" (0), retorna un array vacío
    }

    function filtrarProductos() {
        const filtroCodigo = buscarCodigo.value.toLowerCase();
        const filtroDescripcion = buscarDescripcion.value.toLowerCase();
        const categoriasSeleccionadas = obtenerCategoriasSeleccionadas();

        Array.from(tabla.getElementsByTagName("tr")).forEach((fila) => {
            const codigo = fila.querySelector(".codigo")?.textContent.toLowerCase() || "";
            const descripcion = fila.querySelector(".descripcion")?.textContent.toLowerCase() || "";
            const idCategoria = fila.getAttribute("data-id-categoria");


            const coincideCodigo = codigo.includes(filtroCodigo);
            const coincideDescripcion = descripcion.includes(filtroDescripcion);
            const coincideCategoria = (categoriasSeleccionadas.length === 0 || categoriasSeleccionadas.includes(idCategoria));

            fila.style.display = (coincideCodigo && coincideDescripcion && coincideCategoria) ? "" : "none";
        });
    }

    filtroCategoria.addEventListener("change", filtrarProductos);
    buscarCodigo.addEventListener("input", filtrarProductos);
    buscarDescripcion.addEventListener("input", filtrarProductos);
});