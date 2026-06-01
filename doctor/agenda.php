<?php
require '../config/conexion.php';
require '../includes/auth.php';
requiereRol('doctor');

$stmt = $pdo->prepare("SELECT d.id_doctor, u.nombre AS doctor FROM doctores d JOIN usuarios u ON d.id_usuario=u.id_usuario WHERE d.id_usuario=?");
$stmt->execute([$_SESSION['id_usuario']]);
$doc = $stmt->fetch();

$msg = '';
$err = '';

if (!$doc) {
    $err = 'No se encontró el perfil del doctor.';
}

if ($doc && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cita = (int)($_POST['id_cita'] ?? 0);
    $accion = $_POST['accion'] ?? '';
    $diagnostico = trim($_POST['diagnostico'] ?? '');
    $tratamiento = trim($_POST['tratamiento'] ?? '');
    $observaciones_medicas = trim($_POST['observaciones_medicas'] ?? '');

    $validar = $pdo->prepare("SELECT id_cita FROM citas WHERE id_cita=? AND id_doctor=?");
    $validar->execute([$id_cita, $doc['id_doctor']]);

    if (!$validar->fetch()) {
        $err = 'La cita no existe o no pertenece a este doctor.';
    } elseif ($accion === 'atendida') {
        $upd = $pdo->prepare("UPDATE citas SET estado='Atendida', diagnostico=?, tratamiento=?, observaciones_medicas=?, fecha_atencion=NOW() WHERE id_cita=? AND id_doctor=?");
        $upd->execute([$diagnostico, $tratamiento, $observaciones_medicas, $id_cita, $doc['id_doctor']]);
        $msg = 'La cita se marcó como atendida y se guardó la información médica.';
    } elseif ($accion === 'no_asistio') {
        $upd = $pdo->prepare("UPDATE citas SET estado='No asistió', observaciones_medicas=?, fecha_atencion=NOW() WHERE id_cita=? AND id_doctor=?");
        $upd->execute([$observaciones_medicas ?: 'El paciente no asistió a la cita.', $id_cita, $doc['id_doctor']]);
        $msg = 'La cita se marcó como no asistió.';
    }
}

$citas = [];
if ($doc) {
    $q = $pdo->prepare("SELECT c.*, s.nombre AS servicio, s.descripcion AS descripcion_servicio, s.precio
                        FROM citas c
                        JOIN servicios s ON c.id_servicio=s.id_servicio
                        WHERE c.id_doctor=?
                        ORDER BY c.fecha DESC, c.hora DESC");
    $q->execute([$doc['id_doctor']]);
    $citas = $q->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Agenda doctor</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
<div class="page">
  <div class="container full-width">
    <a class="btn" href="../logout.php">Cerrar sesión</a>
    <h1>Agenda del doctor</h1>
    <?php if($msg): ?><div class="alert ok"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <?php if($err): ?><div class="alert error"><?=htmlspecialchars($err)?></div><?php endif; ?>

    <div class="box">
      <h2><?=htmlspecialchars($doc['doctor'] ?? 'Doctor')?></h2>
      <p>Aquí puedes consultar la información del paciente, marcar citas como atendidas o registrar que el paciente no asistió.</p>
    </div>

    <div class="table-responsive" style="margin-top:22px">
      <table>
        <tr>
          <th>Fecha</th>
          <th>Hora</th>
          <th>Paciente</th>
          <th>Servicio</th>
          <th>WhatsApp</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
        <?php foreach($citas as $c): ?>
        <tr>
          <td><?=htmlspecialchars($c['fecha'])?></td>
          <td><?=htmlspecialchars(substr($c['hora'],0,5))?></td>
          <td><?=htmlspecialchars($c['nombre_paciente'].' '.$c['apellido_paterno'].' '.$c['apellido_materno'])?></td>
          <td><?=htmlspecialchars($c['servicio'])?></td>
          <td><?=htmlspecialchars($c['whatsapp'])?></td>
          <td><?=htmlspecialchars($c['estado'])?></td>
          <td class="acciones">
            <a href="#cita<?=$c['id_cita']?>">Ver paciente</a>
            <?php if(in_array($c['estado'], ['Pendiente','Confirmada'])): ?>
              <a href="#atender<?=$c['id_cita']?>">Atendida</a>
              <a href="#noasistio<?=$c['id_cita']?>">No asistió</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <?php foreach($citas as $c): ?>
      <div class="modal" id="cita<?=$c['id_cita']?>">
        <div class="modal-card">
          <a class="modal-close" href="#">×</a>
          <h2>Información del paciente</h2>
          <p><b>Nombre:</b> <?=htmlspecialchars($c['nombre_paciente'].' '.$c['apellido_paterno'].' '.$c['apellido_materno'])?></p>
          <p><b>Fecha de nacimiento:</b> <?=htmlspecialchars($c['fecha_nacimiento'] ?: 'No registrada')?></p>
          <p><b>Sexo:</b> <?=htmlspecialchars($c['sexo'])?></p>
          <p><b>Correo:</b> <?=htmlspecialchars($c['correo'] ?: 'No registrado')?></p>
          <p><b>WhatsApp:</b> <?=htmlspecialchars($c['whatsapp'])?></p>
          <hr>
          <p><b>Servicio:</b> <?=htmlspecialchars($c['servicio'])?></p>
          <p><b>Descripción:</b> <?=htmlspecialchars($c['descripcion_servicio'])?></p>
          <p><b>Fecha y hora:</b> <?=htmlspecialchars($c['fecha'])?> a las <?=htmlspecialchars(substr($c['hora'],0,5))?></p>
          <p><b>Observaciones del paciente:</b> <?=htmlspecialchars($c['observaciones'] ?: 'Sin observaciones')?></p>
          <?php if($c['diagnostico'] || $c['tratamiento'] || $c['observaciones_medicas']): ?>
          <hr>
          <h3>Información médica registrada</h3>
          <p><b>Diagnóstico:</b> <?=nl2br(htmlspecialchars($c['diagnostico'] ?: ''))?></p>
          <p><b>Tratamiento:</b> <?=nl2br(htmlspecialchars($c['tratamiento'] ?: ''))?></p>
          <p><b>Observaciones médicas:</b> <?=nl2br(htmlspecialchars($c['observaciones_medicas'] ?: ''))?></p>
          <?php endif; ?>
        </div>
      </div>

      <div class="modal" id="atender<?=$c['id_cita']?>">
        <div class="modal-card">
          <a class="modal-close" href="#">×</a>
          <h2>Marcar como atendida</h2>
          <p><b>Paciente:</b> <?=htmlspecialchars($c['nombre_paciente'])?></p>
          <form method="post" class="form">
            <input type="hidden" name="id_cita" value="<?=$c['id_cita']?>">
            <input type="hidden" name="accion" value="atendida">
            <label>Diagnóstico</label>
            <textarea name="diagnostico" rows="3" placeholder="Ejemplo: Resfriado común, dolor abdominal, revisión general..."></textarea>
            <label>Tratamiento</label>
            <textarea name="tratamiento" rows="3" placeholder="Indicaciones o tratamiento recomendado..."></textarea>
            <label>Observaciones médicas</label>
            <textarea name="observaciones_medicas" rows="3" placeholder="Notas adicionales de la consulta..."></textarea>
            <button class="btn" type="submit">Guardar y marcar atendida</button>
          </form>
        </div>
      </div>

      <div class="modal" id="noasistio<?=$c['id_cita']?>">
        <div class="modal-card">
          <a class="modal-close" href="#">×</a>
          <h2>Marcar como no asistió</h2>
          <p><b>Paciente:</b> <?=htmlspecialchars($c['nombre_paciente'])?></p>
          <form method="post" class="form">
            <input type="hidden" name="id_cita" value="<?=$c['id_cita']?>">
            <input type="hidden" name="accion" value="no_asistio">
            <label>Observación</label>
            <textarea name="observaciones_medicas" rows="3">El paciente no asistió a la cita.</textarea>
            <button class="btn" type="submit">Confirmar no asistencia</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
