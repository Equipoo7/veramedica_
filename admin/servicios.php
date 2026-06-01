<?php include '_top.php';
if(isset($_GET['eliminar'])) $pdo->prepare("UPDATE servicios SET activo=0 WHERE id_servicio=?")->execute([(int)$_GET['eliminar']]);
if($_SERVER['REQUEST_METHOD']==='POST'){
 $id=$_POST['id_servicio']??'';
 if($id){
  $pdo->prepare("UPDATE servicios SET nombre=?,descripcion=?,precio=?,id_doctor=?,activo=? WHERE id_servicio=?")->execute([$_POST['nombre'],$_POST['descripcion'],$_POST['precio'],$_POST['id_doctor'],$_POST['activo'],$_POST['id_servicio']]);
 }else{
  $pdo->prepare("INSERT INTO servicios(nombre,descripcion,precio,id_doctor,activo) VALUES(?,?,?,?,1)")->execute([$_POST['nombre'],$_POST['descripcion'],$_POST['precio'],$_POST['id_doctor']]);
 }
}
$docs=$pdo->query("SELECT d.id_doctor,u.nombre FROM doctores d JOIN usuarios u ON d.id_usuario=u.id_usuario WHERE d.activo=1 ORDER BY u.nombre")->fetchAll();
$edit=null;if(isset($_GET['editar'])){$st=$pdo->prepare("SELECT * FROM servicios WHERE id_servicio=?");$st->execute([(int)$_GET['editar']]);$edit=$st->fetch();}
$servicios=$pdo->query("SELECT s.*,u.nombre doctor FROM servicios s LEFT JOIN doctores d ON s.id_doctor=d.id_doctor LEFT JOIN usuarios u ON d.id_usuario=u.id_usuario WHERE s.activo=1 ORDER BY s.id_servicio")->fetchAll();
?>
<h1>Servicios médicos</h1>
<div class="box form"><h2><?= $edit?'Editar servicio':'Agregar servicio' ?></h2><form method="post"><input type="hidden" name="id_servicio" value="<?=htmlspecialchars($edit['id_servicio']??'')?>"><label>Nombre del servicio</label><input name="nombre" value="<?=htmlspecialchars($edit['nombre']??'')?>" required><label>Descripción</label><textarea name="descripcion"><?=htmlspecialchars($edit['descripcion']??'')?></textarea><label>Precio</label><input type="number" step="0.01" name="precio" value="<?=htmlspecialchars($edit['precio']??'')?>" required><label>Doctor que lo atiende</label><select name="id_doctor" required><?php foreach($docs as $d): ?><option value="<?=$d['id_doctor']?>" <?=isset($edit['id_doctor'])&&$edit['id_doctor']==$d['id_doctor']?'selected':''?>><?=htmlspecialchars($d['nombre'])?></option><?php endforeach; ?></select><label>Estado</label><select name="activo"><option value="1">Activo</option><option value="0" <?=isset($edit['activo'])&&!$edit['activo']?'selected':''?>>Inactivo</option></select><button class="btn">Guardar servicio</button><?php if($edit): ?><a class="btn-dark" href="servicios.php">Cancelar edición</a><?php endif; ?></form></div>
<table><tr><th>Servicio</th><th>Descripción</th><th>Precio</th><th>Doctor</th><th>Acción</th></tr><?php foreach($servicios as $s): ?><tr><td><?=htmlspecialchars($s['nombre'])?></td><td><?=htmlspecialchars($s['descripcion'])?></td><td>$<?=number_format($s['precio'],2)?></td><td><?=htmlspecialchars($s['doctor']??'')?></td><td class="acciones"><a href="?editar=<?=$s['id_servicio']?>">Editar</a><a href="?eliminar=<?=$s['id_servicio']?>" onclick="return confirm('¿Eliminar este servicio?')">Eliminar</a></td></tr><?php endforeach; ?></table>
<?php include '_bottom.php'; ?>
