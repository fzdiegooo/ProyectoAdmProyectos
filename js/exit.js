document.addEventListener("DOMContentLoaded", function () {
    function confirmarCierreSesion(event) {
        event.preventDefault(); // Evita la redirección inmediata

        Swal.fire({
            title: "¿Estás seguro de irte? 😢",
            text: "Tu sesión se cerrará y perderás el acceso.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, salir",
            cancelButtonText: "No, quedarme"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../php/cerrar_sesion.php"; // Redirige al script de cierre de sesión
            }
        });
    }

    // Asigna la función a ambos botones
    document.getElementById("cerrarSesionBtn").addEventListener("click", confirmarCierreSesion);
    document.getElementById("cerrarSesionBtnMobile").addEventListener("click", confirmarCierreSesion);
});