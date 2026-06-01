<?php
require 'config/conexion.php'; $token=$_GET['token']??''; $err=''; $msg='';
$stmt=$pdo->prepare("SELECT * FROM recuperacion_password WHERE token=? AND usado=0 AND expira_en > NOW()");
$stmt->execute([$token]); $r=$stmt->fetch();
if(!$r) $err='El enlace no existe, ya fue usado o expiró.';
if($_SERVER['REQUEST_METHOD']==='POST' && $r){
 $p=$_POST['password']??'';
 if(strlen($p)<8) $err='La contraseña debe tener mínimo 8 caracteres.';
 else{
  $pdo->prepare("UPDATE usuarios SET password_hash=? WHERE id_usuario=?")->execute([password_hash($p,PASSWORD_DEFAULT),$r['id_usuario']]);
  $pdo->prepare("UPDATE recuperacion_password SET usado=1 WHERE id_recuperacion=?")->execute([$r['id_recuperacion']]);
  $msg='Contraseña actualizada. Ya puedes iniciar sesión.';
 }
}
include 'includes/header.php'; if($err)echo"<div class='alert error'>$err</div>"; if($msg)echo"<div class='alert ok'>$msg</div>";
if($r && !$msg): ?><div class="container box form"><h1>Nueva contraseña</h1><form method="post"><input type="password" name="password" required><button class="btn">Guardar</button></form></div><?php endif; include 'includes/footer.php'; ?>
