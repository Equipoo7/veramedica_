<?php
require 'config/conexion.php'; require 'config/mail.php'; session_start(); $msg=''; $err='';
$servicios=$pdo->query("SELECT s.*, d.id_doctor, u.nombre doctor FROM servicios s JOIN doctores d ON s.id_doctor=d.id_doctor JOIN usuarios u ON d.id_usuario=u.id_usuario WHERE s.activo=1 ORDER BY u.nombre,s.nombre")->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $id_servicio=(int)($_POST['id_servicio'] ?? 0); $fecha=$_POST['fecha'] ?? ''; $hora=$_POST['hora'] ?? ''; $nombre=trim($_POST['nombre'] ?? ''); $correo=trim($_POST['correo'] ?? ''); $whatsapp=trim($_POST['whatsapp'] ?? '');
 $serv=$pdo->prepare("SELECT s.*, u.nombre doctor FROM servicios s JOIN doctores d ON s.id_doctor=d.id_doctor JOIN usuarios u ON d.id_usuario=u.id_usuario WHERE s.id_servicio=? AND s.activo=1 AND d.activo=1"); $serv->execute([$id_servicio]); $s=$serv->fetch();
 if(!$s) $err='Servicio no válido.';
 elseif(!$fecha || $fecha < date('Y-m-d')) $err='La fecha no es válida. No puedes agendar en fechas pasadas.';
 elseif(!$hora) $err='Selecciona un horario disponible.';
 else{
  $diaMap=['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
  $dia=$diaMap[date('l',strtotime($fecha))];
  $h=$pdo->prepare("SELECT * FROM horarios_doctores WHERE id_doctor=? AND dia_semana=? AND activo=1 AND ? >= hora_inicio AND ? < hora_fin");
  $h->execute([$s['id_doctor'],$dia,$hora,$hora]);
  $ocup=$pdo->prepare("SELECT id_cita FROM citas WHERE id_doctor=? AND fecha=? AND hora=? AND estado IN('Pendiente','Confirmada')");
  $ocup->execute([$s['id_doctor'],$fecha,$hora]);
  if(!$h->fetch()) $err='El doctor '.$s['doctor'].' no está disponible en ese horario.';
  elseif($ocup->fetch()) $err='Ese horario ya está ocupado. Selecciona otro.';
  else{
   $pdo->prepare("INSERT INTO citas(id_cliente_usuario,id_doctor,id_servicio,nombre_paciente,apellido_paterno,apellido_materno,fecha_nacimiento,sexo,correo,whatsapp,fecha,hora,estado) VALUES(?,?,?,?,?,?,?,?,?,?,?,?, 'Confirmada')")
   ->execute([$_SESSION['id_usuario']??null,$s['id_doctor'],$id_servicio,$nombre,$_POST['ap_paterno']??'',$_POST['ap_materno']??'',$_POST['fecha_nacimiento']?:null,$_POST['sexo']??'Otro',$correo,$whatsapp,$fecha,$hora]);
   if($correo) enviarCorreo($correo,'Confirmación de cita VeraMedica',"Tu cita para {$s['nombre']} con {$s['doctor']} fue confirmada para el $fecha a las $hora.");
   $msg='Cita registrada correctamente.';
  }
 }
}
include 'includes/header.php'; if($msg)echo"<div class='alert ok'>$msg</div>"; if($err)echo"<div class='alert error'>$err</div>";
?>
<form method="post" id="formCita">
<div class="container full-width citas-layout form">
  <div class="box">
    <h1>Detalles de la cita</h1>
    <label>Servicio</label>
    <select name="id_servicio" id="id_servicio" required>
      <option value="">Selecciona un servicio</option>
      <?php foreach($servicios as $s): ?>
        <option value="<?=$s['id_servicio']?>"><?=htmlspecialchars($s['nombre'].' - '.$s['doctor'].' $'.number_format($s['precio'],0))?></option>
      <?php endforeach; ?>
    </select>
    <label>Fecha</label>
    <input type="date" name="fecha" id="fecha" required min="<?=date('Y-m-d')?>">
    <p id="mensajeHorario" style="font-size:14px;color:#555">Primero selecciona un servicio y una fecha para ver únicamente los horarios reales del doctor.</p>
  </div>

  <div class="box">
    <h1>Horarios</h1>
    <div class="hours-buttons" id="horariosBox">
      <p class="empty-hours">Selecciona servicio y fecha.</p>
    </div>
  </div>

  <div>
    <div class="box">
      <h1>Datos Personales</h1>
      <label>Nombre(s)</label><input name="nombre" required>
      <label>Apellido Paterno</label><input name="ap_paterno">
      <label>Apellido Materno</label><input name="ap_materno">
      <label>Fecha de nacimiento</label><input type="date" name="fecha_nacimiento">
      <label>Sexo</label><select name="sexo"><option>Mujer</option><option>Hombre</option><option>Otro</option></select>
      <label>Correo Electrónico</label><input type="email" name="correo">
      <label>WhatsApp</label><input name="whatsapp" required>
    </div>
    <div class="box" style="margin-top:22px">
      <h1>Resumen</h1>
      <p>El sistema solo permite agendar si el doctor atiende ese día, a esa hora y el espacio no está ocupado.</p>
      <button type="submit" class="btn" id="btnAgendar" disabled>CONFIRMAR Y AGENDAR</button>
    </div>
  </div>
</div>
</form>
<script>
const servicio = document.getElementById('id_servicio');
const fecha = document.getElementById('fecha');
const box = document.getElementById('horariosBox');
const msg = document.getElementById('mensajeHorario');
const btn = document.getElementById('btnAgendar');

async function cargarHorarios(){
  btn.disabled = true;
  box.innerHTML = '<p class="empty-hours">Cargando horarios...</p>';
  if(!servicio.value || !fecha.value){
    box.innerHTML = '<p class="empty-hours">Selecciona servicio y fecha.</p>';
    msg.textContent = 'Primero selecciona un servicio y una fecha para ver únicamente los horarios reales del doctor.';
    return;
  }
  const r = await fetch(`get_horarios.php?id_servicio=${encodeURIComponent(servicio.value)}&fecha=${encodeURIComponent(fecha.value)}`);
  const data = await r.json();
  msg.textContent = data.msg || '';
  if(!data.horarios || data.horarios.length === 0){
    box.innerHTML = '<p class="empty-hours">No hay horarios disponibles.</p>';
    return;
  }
  box.innerHTML = '';
  data.horarios.forEach((h, i)=>{
    const label = document.createElement('label');
    label.className = 'hour-option';
    label.innerHTML = `<input type="radio" name="hora" value="${h.value}" required ${i===0?'checked':''}><span>${h.label}</span>`;
    box.appendChild(label);
  });
  btn.disabled = !document.querySelector('input[name="hora"]:checked');
  box.querySelectorAll('input[name="hora"]').forEach(radio => {
    radio.addEventListener('change', () => btn.disabled = false);
  });
}
servicio.addEventListener('change', cargarHorarios);
fecha.addEventListener('change', cargarHorarios);
document.getElementById('formCita').addEventListener('submit', function(e){
  if(!document.querySelector('input[name="hora"]:checked')){
    e.preventDefault(); alert('Selecciona un horario disponible.');
  }
});
</script>
<?php include 'includes/footer.php'; ?>
