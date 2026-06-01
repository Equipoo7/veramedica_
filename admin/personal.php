<?php include '_top.php';
$mensaje='';
$roles=$pdo->query("SELECT id_rol,nombre FROM roles WHERE nombre IN ('mostrador','doctor') ORDER BY nombre")->fetchAll();
$dias=['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'];

if(isset($_GET['estado'], $_GET['id'])){
    $id=(int)$_GET['id'];
    $estado=$_GET['estado']==='activar'?1:0;
    $pdo->prepare("UPDATE usuarios SET estado=? WHERE id_usuario=?")->execute([$estado,$id]);
    $pdo->prepare("UPDATE personal_mostrador SET activo=? WHERE id_usuario=?")->execute([$estado,$id]);
    $pdo->prepare("UPDATE doctores SET activo=? WHERE id_usuario=?")->execute([$estado,$id]);
    $mensaje=$estado?'Personal activado correctamente.':'Personal desactivado correctamente.';
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $id_usuario=(int)($_POST['id_usuario'] ?? 0);
    $nombre=trim($_POST['nombre'] ?? '');
    $correo=trim($_POST['correo'] ?? '');
    $telefono=trim($_POST['telefono'] ?? '');
    $rol=$_POST['rol'] ?? '';
    $turno=trim($_POST['turno'] ?? '');
    $especialidad=trim($_POST['especialidad'] ?? '');
    $password=$_POST['password'] ?? '';
    $estado=(int)($_POST['estado'] ?? 1);

    if($nombre==='' || $correo==='' || !in_array($rol,['mostrador','doctor'])){
        $mensaje='Completa nombre, correo y rol.';
    }else{
        $st=$pdo->prepare("SELECT id_rol FROM roles WHERE nombre=?");
        $st->execute([$rol]);
        $id_rol=(int)$st->fetchColumn();
        try{
            $pdo->beginTransaction();
            if($id_usuario>0){
                if($password!==''){
                    if(strlen($password)<8) throw new Exception('La contraseña debe tener mínimo 8 caracteres.');
                    $hash=password_hash($password,PASSWORD_DEFAULT);
                    $pdo->prepare("UPDATE usuarios SET nombre=?,correo=?,telefono=?,id_rol=?,estado=?,password_hash=? WHERE id_usuario=?")
                        ->execute([$nombre,$correo,$telefono,$id_rol,$estado,$hash,$id_usuario]);
                }else{
                    $pdo->prepare("UPDATE usuarios SET nombre=?,correo=?,telefono=?,id_rol=?,estado=? WHERE id_usuario=?")
                        ->execute([$nombre,$correo,$telefono,$id_rol,$estado,$id_usuario]);
                }
            }else{
                if(strlen($password)<8) throw new Exception('La contraseña debe tener mínimo 8 caracteres.');
                $hash=password_hash($password,PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO usuarios(nombre,correo,telefono,password_hash,id_rol,estado) VALUES(?,?,?,?,?,?)")
                    ->execute([$nombre,$correo,$telefono,$hash,$id_rol,$estado]);
                $id_usuario=(int)$pdo->lastInsertId();
            }

            if($rol==='doctor'){
                $ex=$pdo->prepare("SELECT id_doctor FROM doctores WHERE id_usuario=?");$ex->execute([$id_usuario]);
                if($ex->fetchColumn()){
                    $pdo->prepare("UPDATE doctores SET especialidad=?,activo=? WHERE id_usuario=?")->execute([$especialidad ?: 'Medicina general',$estado,$id_usuario]);
                }else{
                    $pdo->prepare("INSERT INTO doctores(id_usuario,especialidad,activo) VALUES(?,?,?)")->execute([$id_usuario,$especialidad ?: 'Medicina general',$estado]);
                }
                $pdo->prepare("UPDATE personal_mostrador SET activo=0 WHERE id_usuario=?")->execute([$id_usuario]);
            }else{
                $ex=$pdo->prepare("SELECT id_personal FROM personal_mostrador WHERE id_usuario=?");$ex->execute([$id_usuario]);
                if($ex->fetchColumn()){
                    $pdo->prepare("UPDATE personal_mostrador SET nombre=?,turno=?,notas=?,activo=? WHERE id_usuario=?")->execute([$nombre,$turno ?: 'Sin turno asignado',$_POST['notas'] ?? '',$estado,$id_usuario]);
                }else{
                    $pdo->prepare("INSERT INTO personal_mostrador(id_usuario,nombre,turno,notas,activo) VALUES(?,?,?,?,?)")->execute([$id_usuario,$nombre,$turno ?: 'Sin turno asignado',$_POST['notas'] ?? '',$estado]);
                }
                $pdo->prepare("UPDATE doctores SET activo=0 WHERE id_usuario=?")->execute([$id_usuario]);
            }
            $pdo->commit();
            $mensaje='Personal guardado correctamente.';
        }catch(Exception $e){
            if($pdo->inTransaction()) $pdo->rollBack();
            $mensaje='Error: '.$e->getMessage();
        }
    }
}

$edit=null;
if(isset($_GET['editar'])){
    $st=$pdo->prepare("SELECT u.*, r.nombre rol, p.turno, p.notas, d.especialidad
        FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol
        LEFT JOIN personal_mostrador p ON p.id_usuario=u.id_usuario
        LEFT JOIN doctores d ON d.id_usuario=u.id_usuario
        WHERE u.id_usuario=? AND r.nombre IN ('mostrador','doctor')");
    $st->execute([(int)$_GET['editar']]);
    $edit=$st->fetch();
}

$personal=$pdo->query("SELECT u.id_usuario,u.nombre,u.correo,u.telefono,u.estado,r.nombre rol,p.turno,d.especialidad
    FROM usuarios u JOIN roles r ON u.id_rol=r.id_rol
    LEFT JOIN personal_mostrador p ON p.id_usuario=u.id_usuario
    LEFT JOIN doctores d ON d.id_usuario=u.id_usuario
    WHERE r.nombre IN ('mostrador','doctor')
    ORDER BY r.nombre,u.nombre")->fetchAll();
?>
<h1>Gestión de personal</h1>
<?php if($mensaje): ?><div class="alert"><?=htmlspecialchars($mensaje)?></div><?php endif; ?>
<div class="box form">
<h2><?= $edit?'Editar personal':'Registrar nuevo personal' ?></h2>
<form method="post" class="grid-form">
<input type="hidden" name="id_usuario" value="<?=htmlspecialchars($edit['id_usuario']??'')?>">
<label>Nombre completo</label><input name="nombre" value="<?=htmlspecialchars($edit['nombre']??'')?>" required>
<label>Correo</label><input type="email" name="correo" value="<?=htmlspecialchars($edit['correo']??'')?>" required>
<label>Teléfono</label><input name="telefono" value="<?=htmlspecialchars($edit['telefono']??'')?>">
<label>Contraseña <?= $edit?'(opcional, solo si se cambiará)':'' ?></label><input type="password" name="password" minlength="8" <?= $edit?'':'required' ?> placeholder="Mínimo 8 caracteres">
<label>Rol</label><select name="rol" required><option value="mostrador" <?=isset($edit['rol'])&&$edit['rol']==='mostrador'?'selected':''?>>Mostrador</option><option value="doctor" <?=isset($edit['rol'])&&$edit['rol']==='doctor'?'selected':''?>>Doctor</option></select>
<label>Turno del mostrador</label><input name="turno" value="<?=htmlspecialchars($edit['turno']??'')?>" placeholder="Ej. Matutino 9:00 am - 3:00 pm">
<label>Especialidad del doctor</label><input name="especialidad" value="<?=htmlspecialchars($edit['especialidad']??'')?>" placeholder="Ej. Ultrasonido, Ginecología">
<label>Notas</label><textarea name="notas"><?=htmlspecialchars($edit['notas']??'')?></textarea>
<label>Estado</label><select name="estado"><option value="1">Activo</option><option value="0" <?=isset($edit['estado'])&&!$edit['estado']?'selected':''?>>Inactivo</option></select>
<button class="btn">Guardar personal</button><?php if($edit): ?><a class="btn-dark" href="personal.php">Cancelar edición</a><?php endif; ?>
</form>
</div>
<table><tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Turno/Especialidad</th><th>Estado</th><th>Acciones</th></tr>
<?php foreach($personal as $p): ?><tr>
<td><?=htmlspecialchars($p['nombre'])?></td><td><?=htmlspecialchars($p['correo'])?></td><td><?=htmlspecialchars($p['telefono']??'')?></td><td><?=htmlspecialchars(ucfirst($p['rol']))?></td><td><?=htmlspecialchars($p['rol']==='doctor'?($p['especialidad']??''):($p['turno']??''))?></td><td><?= $p['estado']?'Activo':'Inactivo' ?></td><td class="acciones"><a href="?editar=<?=$p['id_usuario']?>">Editar</a><?php if($p['estado']): ?><a href="?estado=desactivar&id=<?=$p['id_usuario']?>" onclick="return confirm('¿Desactivar este usuario?')">Desactivar</a><?php else: ?><a href="?estado=activar&id=<?=$p['id_usuario']?>">Activar</a><?php endif; ?></td>
</tr><?php endforeach; ?></table>
<?php include '_bottom.php'; ?>
