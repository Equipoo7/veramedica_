<?php
require 'config/conexion.php'; 
session_start();
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $correo=trim($_POST['correo']??''); 
    $pass=$_POST['password']??''; 
    
    $stmt=$pdo->prepare("SELECT u.*, r.nombre rol FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol WHERE u.correo=? AND u.estado=1");
    $stmt->execute([$correo]); 
    $u=$stmt->fetch();
    
    if($u && password_verify($pass,$u['password_hash'])){
        $_SESSION['id_usuario']=$u['id_usuario']; 
        $_SESSION['nombre']=$u['nombre']; 
        $_SESSION['rol']=$u['rol'];
        
        if($u['rol']==='mostrador') header('Location: admin/dashboard.php');
        elseif($u['rol']==='doctor') header('Location: doctor/agenda.php');
        else header('Location: index.php');
        exit;
    } else {
        $error='Correo o contraseña incorrectos.';
    }
}

include 'includes/header.php';
?>

<style>
.login-page-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 75vh;
    background-color: #f4f7fa; /* Fondo gris claro limpio */
    padding: 40px 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
}

.login-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 46, 99, 0.08);
    width: 100%;
    max-width: 420px;
    padding: 35px;
    border: 1px solid #e2e8f0;
    box-sizing: border-box;
}

.login-header h1 {
    color: #002e63;
    font-size: 26px;
    margin: 0 0 6px 0;
    text-align: center;
    font-weight: 700;
}

.login-header p {
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

.error-alert-box {
    background-color: #fef2f2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
    padding: 10px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 15px;
    text-align: center;
}

.toggle-password {
    position: absolute;
    right: 14px;
    cursor: pointer;
    color: #64748b;
    user-select: none;
}

.btn-login-submit {
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

.btn-login-submit:hover {
    background-color: #064b93;
}

.login-links {
    display: flex;
    justify-content: center;
    font-size: 13px;
    margin-top: 18px;
    gap: 8px;
}

.login-links a {
    color: #0960b9;
    text-decoration: none;
    font-weight: 500;
}

.login-links a:hover {
    text-decoration: underline;
}

.login-links .divider {
    color: #cbd5e1;
}
</style>

<div class="login-page-wrapper">
    <div class="login-card">
        
        <div class="login-header">
            <h1>Iniciar sesión</h1>
            <p>Accede a tu panel médico de VeraMédica</p>
        </div>

        <?php if($error): ?>
            <div class="error-alert-box"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" class="login-form" novalidate>
            
            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-container">
                    <span class="input-icon">✉</span>
                    <input 
                        type="email" 
                        id="correo" 
                        name="correo" 
                        placeholder="ejemplo@gmail.com" 
                        value="<?= htmlspecialchars($correo ?? '') ?>"
                        required
                    >
                </div>
                <span class="error-message" id="error-correo">Por favor, introduce un correo electrónico válido.</span>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-container">
                    <span class="input-icon">🔒</span>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••" 
                        required
                    >
                    <span class="toggle-password" onclick="togglePasswordVisibility()">👁</span>
                </div>
                <span class="error-message" id="error-password">La contraseña debe ser mayor o igual a 8 caracteres.</span>
            </div>

            <button type="submit" class="btn-login-submit">Entrar</button>

            <div class="login-links">
                <a href="registro.php">Crear cuenta</a>
                <span class="divider">·</span>
                <a href="recuperar.php">Recuperar contraseña</a>
            </div>

        </form>
    </div> </div> <script>
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

document.querySelector('.login-form').addEventListener('submit', function(e) {
    const correo = document.getElementById('correo');
    const password = document.getElementById('password');
    const errorCorreo = document.getElementById('error-correo');
    const errorPassword = document.getElementById('error-password');
    let isValid = true;

    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(correo.value.trim())) {
        correo.classList.add('invalid');
        errorCorreo.style.display = 'block';
        isValid = false;
    } else {
        correo.classList.remove('invalid');
        errorCorreo.style.display = 'none';
    }

    if (password.value.length < 8) {
        password.classList.add('invalid');
        errorPassword.style.display = 'block';
        isValid = false;
    } else {
        password.classList.remove('invalid');
        errorPassword.style.display = 'none';
    }

    if (!isValid) {
        e.preventDefault();
    }
});
</script>

<?php include 'includes/footer.php'; ?>