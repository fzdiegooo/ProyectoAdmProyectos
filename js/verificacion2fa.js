document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('verificationForm');
    const input = document.getElementById('codigoInput');
    const submitBtn = document.getElementById('verifyBtn');
    const Swal = window.Swal; // Declare the Swal variable
    
    if (!form || !input || !submitBtn) return;
    
    // Auto-focus en el input
    input.focus();
    
    // Formateo del input en tiempo real
    input.addEventListener('input', function(e) {
        // Solo permitir números
        let value = this.value.replace(/[^0-9]/g, '');
        
        // Limitar a 6 dígitos
        if (value.length > 6) {
            value = value.substring(0, 6);
        }
        
        this.value = value;
        
        // Habilitar/deshabilitar botón según la longitud
        submitBtn.disabled = value.length !== 6;
        
        // Auto-submit cuando se completen 6 dígitos
        if (value.length === 6) {
            setTimeout(() => {
                if (form && !submitBtn.disabled) {
                    submitBtn.click();
                }
            }, 300); // Pequeño delay para mejor UX
        }
    });
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const codigo = input.value.trim();
        
        if (codigo.length !== 6) {
            mostrarError('El código debe tener 6 dígitos');
            return;
        }
        
        // Mostrar estado de carga
        mostrarCargando(true);
        
        // Enviar verificación via AJAX
        fetch('php/verificar_2fa_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ codigo_2fa: codigo }).toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarExito('Verificación exitosa', () => {
                    window.location.href = data.redirect;
                });
            } else {
                mostrarError(data.message);
                input.value = '';
                input.focus();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión. Inténtalo de nuevo.');
        })
        .finally(() => {
            mostrarCargando(false);
        });
    });
    
    // Countdown timer
    function actualizarCountdown() {
        const countdownEl = document.getElementById('countdown');
        if (!countdownEl) return;
        
        const now = Math.floor(Date.now() / 1000);
        const timeLeft = 30 - (now % 30);
        
        if (timeLeft <= 10) {
            countdownEl.innerHTML = `<i class="fas fa-clock me-1"></i>Nuevo código en ${timeLeft}s`;
            countdownEl.style.color = '#dc3545';
        } else {
            countdownEl.innerHTML = `<i class="fas fa-clock me-1"></i>Nuevo código en ${timeLeft}s`;
            countdownEl.style.color = '#6c757d';
        }
        
        // Limpiar input cuando se genera nuevo código
        if (timeLeft === 30) {
            input.value = '';
            submitBtn.disabled = true;
        }
    }
    
    // Iniciar countdown
    actualizarCountdown();
    setInterval(actualizarCountdown, 1000);
    
    // Funciones auxiliares
    function mostrarCargando(mostrar) {
        if (mostrar) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        } else {
            submitBtn.classList.remove('loading');
            submitBtn.disabled = input.value.length !== 6;
        }
    }
    
    function mostrarError(mensaje) {
        Swal.fire({
            icon: 'error',
            title: 'Error de verificación',
            text: mensaje,
            confirmButtonColor: '#d33'
        });
    }
    
    function mostrarExito(mensaje, callback) {
        Swal.fire({
            icon: 'success',
            title: '¡Verificación exitosa!',
            text: mensaje,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        }).then(callback);
    }
    
    // Manejar teclas especiales
    input.addEventListener('keydown', function(e) {
        // Permitir: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Permitir: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }
        // Asegurar que es un número
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
    
    // Prevenir paste de texto no numérico
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numericPaste = paste.replace(/[^0-9]/g, '').substring(0, 6);
        this.value = numericPaste;
        
        // Trigger input event para validaciones
        this.dispatchEvent(new Event('input'));
    });
});