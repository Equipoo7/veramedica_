<?php
include '_top.php';

$msg = '';
$err = '';

if (isset($_GET['cancelar'])) {
    $pdo->prepare("UPDATE citas SET estado='Cancelada' WHERE id_cita=?")
        ->execute([(int)$_GET['cancelar']]);
    $msg = 'Cita cancelada correctamente.';
}

function obtenerServicio($pdo, $id_servicio) {
    $serv = $pdo->prepare("
        SELECT s.*, d.id_doctor, u.nombre doctor 
        FROM servicios s 
        JOIN doctores d ON s.id_doctor=d.id_doctor 
        JOIN usuarios u ON d.id_usuario=u.id_usuario 
        WHERE s.id_servicio=? AND s.activo=1 AND d.activo=1
    ");
    $serv->execute([$id_servicio]);
    return $serv->fetch();
}

function validarHorario($pdo, $id_doctor, $fecha, $hora, $id_cita = 0) {
    $diaMap = [
        'Monday'=>'Lunes',
        'Tuesday'=>'Martes',
        'Wednesday'=>'Miércoles',
        'Thursday'=>'Jueves',
        'Friday'=>'Viernes',
        'Saturday'=>'Sábado',
        'Sunday'=>'Domingo'
    ];

    $dia = $diaMap[date('l', strtotime($fecha))];

    $h = $pdo->prepare("
        SELECT id_horario 
        FROM horarios_doctores 
        WHERE id_doctor=? 
        AND dia_semana=? 
        AND activo=1 
        AND ? >= hora_inicio 
        AND ? < hora_fin
    ");
    $h->execute([$id_doctor, $dia, $hora, $hora]);

    if (!$h->fetch()) {
        return 'El doctor no está disponible en ese horario.';
    }

    $ocup = $pdo->prepare("
        SELECT id_cita 
        FROM citas 
        WHERE id_doctor=? 
        AND fecha=? 
        AND hora=? 
        AND estado IN('Pendiente','Confirmada') 
        AND id_cita<>?
    ");
    $ocup->execute([$id_doctor, $fecha, $hora, $id_cita]);

    if ($ocup->fetch()) {
        return 'Ese horario ya está ocupado.';
    }

    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $id_servicio = (int)($_POST['id_servicio'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $nombre = trim($_POST['nombre_paciente'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    $s = obtenerServicio($pdo, $id_servicio);

    if (!$s) {
        $err = 'Servicio no válido.';
    } elseif (!$nombre || !$whatsapp) {
        $err = 'El nombre y WhatsApp son obligatorios.';
    } elseif (!$fecha || $fecha < date('Y-m-d')) {
        $err = 'No puedes agendar una fecha pasada.';
    } elseif (!$hora) {
        $err = 'Selecciona una hora válida.';
    } else {
        $err = validarHorario($pdo, $s['id_doctor'], $fecha, $hora, 0);

        if (!$err) {
            $ins = $pdo->prepare("
                INSERT INTO citas (
                    id_cliente_usuario,
                    id_doctor,
                    id_servicio,
                    nombre_paciente,
                    apellido_paterno,
                    apellido_materno,
                    fecha_nacimiento,
                    sexo,
                    correo,
                    whatsapp,
                    fecha,
                    hora,
                    estado,
                    observaciones
                ) VALUES (
                    NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Confirmada', ?
                )
            ");

            $ins->execute([
                $s['id_doctor'],
                $id_servicio,
                $nombre,
                $_POST['apellido_paterno'] ?? '',
                $_POST['apellido_materno'] ?? '',
                $_POST['fecha_nacimiento'] ?: null,
                $_POST['sexo'] ?? 'Otro',
                $correo,
                $whatsapp,
                $fecha,
                $hora,
                $_POST['observaciones'] ?? ''
            ]);

            $msg = 'Cita registrada correctamente desde mostrador.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $id = (int)($_POST['id_cita'] ?? 0);
    $id_servicio = (int)($_POST['id_servicio'] ?? 0);
    $fecha = $_POST['fecha'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $nombre = trim($_POST['nombre_paciente'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $correo = trim($_POST['correo'] ?? '');

    $s = obtenerServicio($pdo, $id_servicio);

    if (!$id || !$s) {
        $err = 'Datos de cita no válidos.';
    } elseif (!$nombre || !$whatsapp) {
        $err = 'El nombre y WhatsApp son obligatorios.';
    } elseif (!$fecha || $fecha < date('Y-m-d')) {
        $err = 'No puedes reagendar a una fecha pasada.';
    } elseif (!$hora) {
        $err = 'Selecciona una hora válida.';
    } else {
        $err = validarHorario($pdo, $s['id_doctor'], $fecha, $hora, $id);

        if (!$err) {
            $upd = $pdo->prepare("
                UPDATE citas 
                SET id_doctor=?,
                    id_servicio=?,
                    nombre_paciente=?,
                    apellido_paterno=?,
                    apellido_materno=?,
                    fecha_nacimiento=?,
                    sexo=?,
                    correo=?,
                    whatsapp=?,
                    fecha=?,
                    hora=?,
                    estado='Confirmada',
                    observaciones=? 
                WHERE id_cita=?
            ");

            $upd->execute([
                $s['id_doctor'],
                $id_servicio,
                $nombre,
                $_POST['apellido_paterno'] ?? '',
                $_POST['apellido_materno'] ?? '',
                $_POST['fecha_nacimiento'] ?: null,
                $_POST['sexo'] ?? 'Otro',
                $correo,
                $whatsapp,
                $fecha,
                $hora,
                $_POST['observaciones'] ?? '',
                $id
            ]);

            $msg = 'Cita actualizada/reagendada correctamente.';
        }
    }
}

$citas = $pdo->query("
    SELECT c.*, s.nombre servicio, u.nombre doctor 
    FROM citas c 
    JOIN servicios s ON c.id_servicio=s.id_servicio 
    JOIN doctores d ON c.id_doctor=d.id_doctor 
    JOIN usuarios u ON d.id_usuario=u.id_usuario 
    ORDER BY c.fecha DESC, c.hora DESC
")->fetchAll();

$servicios = $pdo->query("
    SELECT s.*, u.nombre doctor 
    FROM servicios s 
    JOIN doctores d ON s.id_doctor=d.id_doctor 
    JOIN usuarios u ON d.id_usuario=u.id_usuario 
    WHERE s.activo=1 
    ORDER BY u.nombre,s.nombre
")->fetchAll();

function waLinkAdmin($telefono, $c) {
    $t = preg_replace('/\D+/', '', $telefono);

    if (strlen($t) == 10) {
        $t = '52' . $t;
    }

    $msg = urlencode(
        'Hola ' . $c['nombre_paciente'] .
        ', tu cita en Farmacia VeraMedica está confirmada para el ' .
        $c['fecha'] .
        ' a las ' .
        substr($c['hora'], 0, 5) .
        '. Servicio: ' .
        $c['servicio'] .
        '.'
    );

    return 'https://wa.me/' . $t . '?text=' . $msg;
}
?>

<h1>Agenda de citas</h1>

<p>
    El mostrador puede revisar, registrar, editar, reagendar o cancelar citas.
    El estado <b>Atendida</b> lo registra únicamente el doctor desde su agenda.
</p>

<?php if($msg): ?>
    <div class="alert ok"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<?php if($err): ?>
    <div class="alert error"><?= htmlspecialchars($err) ?></div>
<?php endif; ?>

<div class="box form">
    <h2>Registrar nueva cita</h2>

    <form method="post">
        <input type="hidden" name="accion" value="crear">

        <label>Servicio</label>
        <select name="id_servicio" required>
            <option value="">Selecciona un servicio</option>
            <?php foreach($servicios as $s): ?>
                <option value="<?= $s['id_servicio'] ?>">
                    <?= htmlspecialchars($s['nombre'] . ' - ' . $s['doctor']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Fecha</label>
        <input type="date" name="fecha" min="<?= date('Y-m-d') ?>" required>

        <label>Hora</label>
        <input type="time" name="hora" required>

        <p style="font-size:13px;color:#666;">
            El sistema validará que el doctor esté disponible y que la hora no esté ocupada.
        </p>

        <label>Nombre</label>
        <input name="nombre_paciente" required>

        <label>Apellido paterno</label>
        <input name="apellido_paterno">

        <label>Apellido materno</label>
        <input name="apellido_materno">

        <label>Fecha nacimiento</label>
        <input type="date" name="fecha_nacimiento">

        <label>Sexo</label>
        <select name="sexo">
            <option>Mujer</option>
            <option>Hombre</option>
            <option>Otro</option>
        </select>

        <label>Correo</label>
        <input type="email" name="correo">

        <label>WhatsApp</label>
        <input name="whatsapp" required>

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <button class="btn">Registrar cita</button>
    </form>
</div>

<br>

<div class="table-responsive">
<table>
<tr>
    <th>Fecha</th>
    <th>Hora</th>
    <th>Paciente</th>
    <th>Servicio</th>
    <th>Doctor</th>
    <th>WhatsApp</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php foreach($citas as $c): ?>
<tr>
    <td><?= htmlspecialchars($c['fecha']) ?></td>
    <td><?= htmlspecialchars(substr($c['hora'], 0, 5)) ?></td>
    <td><?= htmlspecialchars($c['nombre_paciente'] . ' ' . $c['apellido_paterno'] . ' ' . $c['apellido_materno']) ?></td>
    <td><?= htmlspecialchars($c['servicio']) ?></td>
    <td><?= htmlspecialchars($c['doctor']) ?></td>
    <td>
        <a target="_blank" href="<?= htmlspecialchars(waLinkAdmin($c['whatsapp'], $c)) ?>">
            <?= htmlspecialchars($c['whatsapp']) ?>
        </a>
    </td>
    <td><?= htmlspecialchars($c['estado']) ?></td>
    <td class="acciones">
        <a href="#ver<?= $c['id_cita'] ?>">Ver</a>

        <?php if($c['estado'] !== 'Atendida' && $c['estado'] !== 'No asistió' && $c['estado'] !== 'Cancelada'): ?>
            <a href="#editar<?= $c['id_cita'] ?>">Editar/Reagendar</a>
        <?php endif; ?>

        <?php if($c['estado'] !== 'Cancelada' && $c['estado'] !== 'Atendida'): ?>
            <a href="?cancelar=<?= $c['id_cita'] ?>" onclick="return confirm('¿Cancelar esta cita?')">
                Cancelar
            </a>
        <?php endif; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php foreach($citas as $c): ?>

<div class="modal" id="ver<?= $c['id_cita'] ?>">
  <div class="modal-card">
    <a class="modal-close" href="#">×</a>

    <h2>Detalle de la cita</h2>

    <p><b>Paciente:</b> <?= htmlspecialchars($c['nombre_paciente'] . ' ' . $c['apellido_paterno'] . ' ' . $c['apellido_materno']) ?></p>
    <p><b>Correo:</b> <?= htmlspecialchars($c['correo'] ?: 'No registrado') ?></p>
    <p><b>WhatsApp:</b> <?= htmlspecialchars($c['whatsapp']) ?></p>
    <p><b>Servicio:</b> <?= htmlspecialchars($c['servicio']) ?></p>
    <p><b>Doctor:</b> <?= htmlspecialchars($c['doctor']) ?></p>
    <p><b>Fecha/Hora:</b> <?= htmlspecialchars($c['fecha']) ?> <?= htmlspecialchars(substr($c['hora'],0,5)) ?></p>
    <p><b>Estado:</b> <?= htmlspecialchars($c['estado']) ?></p>
    <p><b>Observaciones:</b> <?= nl2br(htmlspecialchars($c['observaciones'] ?: 'Sin observaciones')) ?></p>
  </div>
</div>

<div class="modal" id="editar<?= $c['id_cita'] ?>">
  <div class="modal-card">
    <a class="modal-close" href="#">×</a>

    <h2>Editar o reagendar cita</h2>

    <form method="post" class="form">
      <input type="hidden" name="accion" value="actualizar">
      <input type="hidden" name="id_cita" value="<?= $c['id_cita'] ?>">

      <label>Servicio</label>
      <select name="id_servicio" required>
        <?php foreach($servicios as $s): ?>
        <option value="<?= $s['id_servicio'] ?>" <?= $s['id_servicio'] == $c['id_servicio'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['nombre'] . ' - ' . $s['doctor']) ?>
        </option>
        <?php endforeach; ?>
      </select>

      <label>Fecha</label>
      <input 
        type="date" 
        name="fecha" 
        value="<?= htmlspecialchars($c['fecha']) ?>" 
        min="<?= date('Y-m-d') ?>" 
        required
      >

      <label>Hora</label>
      <input 
        type="time" 
        name="hora" 
        value="<?= htmlspecialchars(substr($c['hora'],0,5)) ?>" 
        required
      >

      <p style="font-size:13px;color:#666;">
        El sistema validará que el doctor esté disponible y que la hora no esté ocupada.
      </p>

      <label>Nombre</label>
      <input name="nombre_paciente" value="<?= htmlspecialchars($c['nombre_paciente']) ?>" required>

      <label>Apellido paterno</label>
      <input name="apellido_paterno" value="<?= htmlspecialchars($c['apellido_paterno']) ?>">

      <label>Apellido materno</label>
      <input name="apellido_materno" value="<?= htmlspecialchars($c['apellido_materno']) ?>">

      <label>Fecha nacimiento</label>
      <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($c['fecha_nacimiento']) ?>">

      <label>Sexo</label>
      <select name="sexo">
        <option <?= $c['sexo']=='Mujer' ? 'selected' : '' ?>>Mujer</option>
        <option <?= $c['sexo']=='Hombre' ? 'selected' : '' ?>>Hombre</option>
        <option <?= $c['sexo']=='Otro' ? 'selected' : '' ?>>Otro</option>
      </select>

      <label>Correo</label>
      <input type="email" name="correo" value="<?= htmlspecialchars($c['correo']) ?>">

      <label>WhatsApp</label>
      <input name="whatsapp" value="<?= htmlspecialchars($c['whatsapp']) ?>" required>

      <label>Observaciones</label>
      <textarea name="observaciones"><?= htmlspecialchars($c['observaciones']) ?></textarea>

      <button class="btn">Guardar cambios</button>
    </form>
  </div>
</div>

<?php endforeach; ?>

<?php include '_bottom.php'; ?>