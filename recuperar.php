<?php
require 'config/conexion.php';
require 'config/mail.php';

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = ? AND estado = 1");
    $stmt->execute([$correo]);
    $u = $stmt->fetch();

    if ($u) {
        $token = bin2hex(random_bytes(32));
        $exp = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $pdo->prepare("
            INSERT INTO recuperacion_password(id_usuario, token, expira_en)
            VALUES (?, ?, ?)
        ")->execute([$u['id_usuario'], $token, $exp]);

        $base = 'http://localhost/veramedica_completo';
        $link = $base . "/restablecer.php?token=" . $token;

        $nombre = htmlspecialchars($u['nombre']);

        $html = "
        <div style='font-family:Arial,sans-serif; padding:20px; color:#1f2937;'>

            <h2 style='color:#123b5d;'>
                Recuperación de contraseña - Farmacia VeraMedica
            </h2>

            <p>Hola, <b>$nombre</b>.</p>

            <p>Recibimos una solicitud para restablecer tu contraseña.</p>

            <p>Haz clic en el siguiente botón:</p>

            <p>
                <a href='$link'
                   style='
                    background:#123b5d;
                    color:white;
                    padding:12px 22px;
                    text-decoration:none;
                    border-radius:8px;
                    display:inline-block;
                    font-weight:bold;
                   '>
                   Restablecer contraseña
                </a>
            </p>

            <p>Si el botón no funciona, copia este enlace:</p>

            <p>
                <a href='$link'>$link</a>
            </p>

            <p style='font-size:13px;color:#555;'>
                Este enlace expira en 30 minutos.
            </p>

        </div>
        ";

        $enviado = enviarCorreo(
            $correo,
            'Recuperación de contraseña - VeraMedica',
            $html
        );

        if ($enviado) {
            $msg = "Se envió un enlace de recuperación a tu correo. Revisa también Spam o Promociones.";
        } else {
            $err = "No se pudo enviar el correo. Revisa la configuración SMTP.";
        }

    } else {
        $msg = "Si el correo existe, se enviará un enlace de recuperación.";
    }
}

include 'includes/header.php';

if ($msg) {
    echo "<div class='alert ok'>$msg</div>";
}

if ($err) {
    echo "<div class='alert error'>$err</div>";
}
?>

<div class="container box form">
    <h1>Recuperar contraseña</h1>

    <form method="post">
        <label>Correo registrado</label>
        <input type="email" name="correo" required>

        <button class="btn">
            Enviar enlace
        </button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>