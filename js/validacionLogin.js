document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("loginForm").addEventListener("submit", function (event) {
        event.preventDefault(); // Evita el envío del formulario

        let correo = document.getElementById("correo").value.trim();
        let contrasena = document.getElementById("contrasena").value.trim();

        let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        // Declare Swal variable before using it
        const Swal = window.Swal;

        if (!emailRegex.test(correo)) {
            Swal.fire({ title: "Error", text: "Correo inválido", icon: "error" });
            return;
        }

        // Obtener el botón de submit para mostrar loading
        const submitBtn = document.querySelector('#loginForm button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Mostrar estado de carga
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
        submitBtn.disabled = true;

        // Crear un objeto URLSearchParams para enviar datos correctamente
        let formData = new URLSearchParams();
        formData.append("correo", correo);
        formData.append("contrasena", contrasena);

        // Enviar los datos al backend con AJAX
        fetch("php/login_usuario_be.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData.toString()
        })
        .then(response => {
            // Verificar si la respuesta es válida
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Respuesta del servidor:', data); // Para debug
            
            if (data.success) {
                if (data.requires_2fa) {
                    // Usuario requiere verificación 2FA
                    Swal.fire({
                        title: "Verificación 2FA",
                        text: data.message,
                        icon: "info",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        // Redirigir a la página de verificación 2FA
                        window.location.href = data.redirect;
                    });
                } else {
                    // Login exitoso sin 2FA
                    Swal.fire({
                        title: "¡Bienvenid@!",
                        text: data.message,
                        icon: "success",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        window.location.href = data.redirect; // Redirige según el tipo de usuario
                    });
                }
            } else {
                // Error en las credenciales
                Swal.fire({ 
                    title: "Error de acceso", 
                    text: data.message, 
                    icon: "error",
                    confirmButtonColor: "#d33"
                });
            }
        })
        .catch(error => {
            console.error("Error completo:", error);
            Swal.fire({ 
                title: "Error de conexión", 
                text: "Hubo un problema con el servidor. Revisa la consola para más detalles.", 
                icon: "error",
                confirmButtonColor: "#d33"
            });
        })
        .finally(() => {
            // Restaurar el botón a su estado original
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        });
    });
});