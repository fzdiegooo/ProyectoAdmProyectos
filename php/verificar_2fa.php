<?php
session_start();

// Verificar que el usuario esté en proceso de login con 2FA
if (!isset($_SESSION['pending_2fa']) || !isset($_SESSION['temp_secret'])) {
    header("Location: index.php");
    exit();
}

require_once 'GoogleAuthenticator.php';

$error = '';
$success = '';

if ($_POST) {
    $codigo = trim($_POST['codigo_2fa']);
    $secret = $_SESSION['temp_secret'];
    
    if (strlen($codigo) == 6 && is_numeric($codigo)) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        
        if ($ga->verifyCode($secret, $codigo, 2)) {
            // Código correcto, completar login
            $rol = $_SESSION['temp_rol'];
            
            // Limpiar variables temporales
            unset($_SESSION['pending_2fa']);
            unset($_SESSION['temp_secret']);
            unset($_SESSION['temp_rol']);
            
            // Redirigir según rol
            switch ($rol) {
                case 'admin':
                    header("Location: ../Admin/admin-page.php");
                    break;
                case 'vendedor':
                    header("Location: ../Trabajador/pedidos.php");
                    break;
                case 'cliente':
                    header("Location: ../Cliente/cliente-page.php");
                    break;
                default:
                    header("Location: ../index.php");
            }
            exit();
        } else {
            $error = "Código incorrecto. Inténtalo de nuevo.";
        }
    } else {
        $error = "El código debe tener 6 dígitos numéricos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA - Tu Aplicación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #5EBC50 0%, #4a9d42 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 50px;
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .verification-icon {
            font-size: 5rem;
            color: #5EBC50;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .code-input {
            font-size: 2rem;
            text-align: center;
            letter-spacing: 1rem;
            border: 3px solid #e9ecef;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            transition: all 0.3s ease;
            font-weight: bold;
        }
        .code-input:focus {
            border-color: #5EBC50;
            box-shadow: 0 0 0 0.3rem rgba(94, 188, 80, 0.25);
            outline: none;
        }
        .verify-btn {
            background: linear-gradient(135deg, #5EBC50 0%, #4a9d42 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 40px;
            font-weight: 700;
            color: white;
            width: 100%;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }
        .verify-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(94, 188, 80, 0.3);
        }
        .verify-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .back-link {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .back-link:hover {
            color: #5EBC50;
        }
        .countdown {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 15px;
        }
        .loading-spinner {
            display: none;
        }
        .loading .loading-spinner {
            display: inline-block;
        }
        .loading .btn-text {
            display: none;
        }
    </style>
</head>
<body>
    <div class="verification-card">
        <i class="fas fa-shield-alt verification-icon"></i>
        <h2 class="mb-3 fw-bold">Verificación en dos pasos</h2>
        <p class="text-muted mb-4">Ingresa el código de 6 dígitos de tu aplicación<br><strong>Google Authenticator</strong></p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="verificationForm">
            <div class="mb-4">
                <input type="text" 
                       class="form-control code-input" 
                       name="codigo_2fa" 
                       id="codigoInput"
                       placeholder="000000" 
                       maxlength="6" 
                       pattern="[0-9]{6}" 
                       required 
                       autocomplete="off"
                       inputmode="numeric">
            </div>
            <button type="submit" class="btn verify-btn" id="verifyBtn">
                <span class="loading-spinner spinner-border spinner-border-sm me-2" role="status"></span>
                <span class="btn-text">
                    <i class="fas fa-check me-2"></i>Verificar Código
                </span>
            </button>
        </form>
        
        <div class="countdown" id="countdown"></div>
        

        
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                El código cambia cada 30 segundos
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('codigoInput');
            const form = document.getElementById('verificationForm');
            const btn = document.getElementById('verifyBtn');
            
            // Auto-focus en el input
            input.focus();
            
            // Formateo del input
            input.addEventListener('input', function(e) {
                // Solo permitir números
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Habilitar/deshabilitar botón
                btn.disabled = this.value.length !== 6;
                
                // Auto-submit cuando se completen 6 dígitos
                if (this.value.length === 6) {
                    setTimeout(() => {
                        form.submit();
                    }, 500);
                }
            });
            
            // Efecto de loading al enviar
            form.addEventListener('submit', function() {
                btn.classList.add('loading');
                btn.disabled = true;
            });
            
            // Countdown timer (opcional)
            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                const timeLeft = 30 - (now % 30);
                const countdownEl = document.getElementById('countdown');
                
                if (timeLeft <= 10) {
                    countdownEl.innerHTML = `<i class="fas fa-clock me-1"></i>Nuevo código en ${timeLeft}s`;
                    countdownEl.style.color = '#dc3545';
                } else {
                    countdownEl.innerHTML = `<i class="fas fa-clock me-1"></i>Nuevo código en ${timeLeft}s`;
                    countdownEl.style.color = '#6c757d';
                }
            }
            
            // Actualizar countdown cada segundo
            updateCountdown();
            setInterval(updateCountdown, 1000);
            
            // Limpiar input cuando se genera nuevo código
            setInterval(function() {
                const now = Math.floor(Date.now() / 1000);
                if (now % 30 === 0) {
                    input.value = '';
                    btn.disabled = true;
                }
            }, 1000);
        });
    </script>
    <link rel="stylesheet" href="css/2fa-styles.css">
<script src="js/verificacion2fa.js"></script>
</body>
</html>