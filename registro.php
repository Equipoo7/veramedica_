<?php
require 'config/conexion.php'; 
$msg=''; 
$err='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $nombre=trim($_POST['nombre']); 
    $correo=trim($_POST['correo']); 
    $tel=trim($_POST['telefono']); 
    $pass=$_POST['password'];
    
    if(strlen($pass)<8) {
        $err='La contraseña debe tener mínimo 8 caracteres.';
    } else {
        try {
            $hash=password_hash($pass,PASSWORD_DEFAULT);
            $stmt=$pdo->prepare("INSERT INTO usuarios(nombre,correo,telefono,password_hash,id_rol) VALUES(?,?,?,?,1)");
            $stmt->execute([$nombre,$correo,$tel,$hash]); 
            $msg='Cuenta creada correctamente. Ya puedes iniciar sesión.';
        } catch(Exception $e) { 
            $err='Ese correo ya está registrado.'; 
        }
    }
}

include 'includes/header.php';
?>

<style>
.register-page-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 75vh;
    background-color: #f4f7fa; /* Fondo gris claro limpio */
    padding: 40px 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
}

.register-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 46, 99, 0.08);
    width: 100%;
    max-width: 440px;
    padding: 35px;
    border: 1px solid #e2e8f0;
    box-sizing: border-box;
}

.register-header h1 {
    color: #002e63;
    font-size: 26px;
    margin: 0 0 6px 0;
    text-align: center;
    font-weight: 700;
}

.register-header p {
    color: #64748b;
    font-size: 13px;
    margin: 0 0 24px 0;
    text-align: center;
}

.form-group {
    margin-bottom: 18px;
    text-align: left;
}

.form-group label {
    display: block;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
}

.input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 14px;
    color: #94a3b8;
    font-size: 14px;
    pointer-events: none;
}

.input-container input {
    width: 100%;
    padding: 11px 14px 11px 38px;
    font-size: 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    transition: all 0.2s;
    background-color: #ffffff;
    color: #1e293b;
}

.input-container input:focus {
    outline: none;
    border-color: #0960b9;
    box-shadow: 0 0 0 3px rgba(9, 96, 185, 0.12);
}

.input-container input.invalid {
    border-color: #ef4444;
    background-color: #fef2f2;
}

.error-message {
    display: none;
    color: #ef4444;
    font-size: 11px;
    margin-top: 5px;
    font-weight: 500;
}

/* Alertas de Servidor */
.alert-box {
    padding: 12px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 500;
}

.alert-box.ok-msg {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.alert-box.error-msg {
    background-color: #fef2f2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
}

.toggle-password {
    position: absolute;
    right: 14px;
    cursor: pointer;
    color: #64748b;
    user-select: none;
}

.btn-register-submit {
    width: 100%;
    padding: 12px;
    background-color: #0960b9;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 8px;
    transition: background 0.2s;
}

.btn-register-submit:hover {
    background-color: #064b93;
}

.register-links {
    display: flex;
    justify-content: center;
    font-size: 13px;
    margin-top: 18px;
}

.register-links a {
    color: #0960b9;
    text-decoration: none;
    font-weight: 500;
}

.register-links a:hover {
    text-decoration: underline;
}
</style>

<div class="register-page-wrapper">
    <div class="register-card">
        
        <div class="register-header">
            <h1>Registrarse</h1>
            <p>Crea tu cuenta en VeraMédica para gestionar tus citas</p>
        </div>

        <?php if($msg): ?>
            <div class="alert-box ok-msg"><?= $msg ?></div>
        <?php endif; ?>
        
        <?php if($err): ?>
            <div class="alert-box error-msg"><?= $err ?></div>
        <?php endif; ?>

        <form method="post" class="register-form" novalidate>
            
            <div class="form-group">
                <label>Nombre completo</label>
                <div class="input-container">
                    <span class="input-icon"> </span>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        placeholder="Ej. Juan Pérez Gómez" 
                        value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                        required
                    >
                </div>
                <span class="error-message" id="error-nombre">Por favor, introduce tu nombre completo.</span>
            </div>

            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-container">
                    <span class="input-icon">✉</span>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        placeholder="ejemplo@veramedica.com" 
                        value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>"
                        required
                    >
                </div>
                <span class="error-message" id="error-correo">Por favor, introduce un correo electrónico válido.</span>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <div class="input-container">
                    <span class="input-icon">✆</span>
                    <input 
                        type="tel" 
                        id="telefono" 
                        name="telefono" 
                        placeholder="2291234567" 
                        maxlength="10"
                        value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>"
                        required
                    >
                </div>
                <span class="error-message" id="error-telefono">El teléfono debe contener 10 dígitos numéricos.</span>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-container">
                    <span class="input-icon">🔒</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Mínimo 8 caracteres" 
                        required
                    >
                    <span class="toggle-password" onclick="togglePasswordVisibility()">👁</span>
                </div>
                <span class="error-message" id="error-password">La contraseña debe ser mayor o igual a 8 caracteres.</span>
            </div>

            <button type="submit" class="btn-register-submit">Crear cuenta</button>

            <div class="register-links">
                <span>¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></span>
            </div>

        </form>

    </div>
</div>

<script>
// Ocultar y mostrar contraseña
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.querySelector('.toggle-password');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.textContent = '🙈';
    } else {
        passwordInput.type = 'password';
        toggleIcon.textContent = '👁';
    }
}

// Forzar que el campo teléfono solo acepte números en tiempo de escritura
document.getElementById('telefono').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// Validaciones avanzadas antes del envío del formulario (POST)
document.querySelector('.register-form').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre');
    const correo = document.getElementById('correo');
    const telefono = document.getElementById('telefono');
    const password = document.getElementById('password');
    
    const errorNombre = document.getElementById('error-nombre');
    const errorCorreo = document.getElementById('error-correo');
    const errorTelefono = document.getElementById('error-telefono');
    const errorPassword = document.getElementById('error-password');
    
    let isValid = true;

    // 1. Validar Nombre
    if (nombre.value.trim() === '') {
        nombre.classList.add('invalid');
        errorNombre.style.display = 'block';
        isValid = false;
    } else {
        nombre.classList.remove('invalid');
        errorNombre.style.display = 'none';
    }

    // 2. Validar Correo (Expresión Regular)
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(correo.value.trim())) {
        correo.classList.add('invalid');
        errorCorreo.style.display = 'block';
        isValid = false;
    } else {
        correo.classList.remove('invalid');
        errorCorreo.style.display = 'none';
    }

    // 3. Validar Teléfono (Exactamente 10 dígitos de México)
    if (telefono.value.trim().length !== 10) {
        telefono.classList.add('invalid');
        errorTelefono.style.display = 'block';
        isValid = false;
    } else {
        telefono.classList.remove('invalid');
        errorTelefono.style.display = 'none';
    }

    // 4. Validar Seguridad de Contraseña (Mínimo 8 caracteres)
    if (password.value.length < 8) {
        password.classList.add('invalid');
        errorPassword.style.display = 'block';
        isValid = false;
    } else {
        password.classList.remove('invalid');
        errorPassword.style.display = 'none';
    }

    // Bloquear el envío si alguna validación falla
    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php include 'includes/footer.php'; ?>