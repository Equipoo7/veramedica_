<?php
require 'config/mail.php';
$destino = 'veramedica.farmcia@gmail.com';
$ok = enviarCorreo($destino, 'Prueba VeraMedica', '<h2>Correo de prueba</h2><p>PHPMailer está funcionando correctamente.</p>');
echo $ok ? 'Correo enviado correctamente. Revisa la bandeja de entrada y spam.' : 'No se pudo enviar el correo. Revisa config/mail.php.';
?>
