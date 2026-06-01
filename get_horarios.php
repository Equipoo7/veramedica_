<?php
require 'config/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$id_servicio = isset($_GET['id_servicio']) ? (int)$_GET['id_servicio'] : 0;
$fecha = $_GET['fecha'] ?? '';

if (!$id_servicio || !$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['ok'=>false, 'msg'=>'Selecciona servicio y fecha.', 'horarios'=>[]]);
    exit;
}

$hoy = date('Y-m-d');
if ($fecha < $hoy) {
    echo json_encode(['ok'=>false, 'msg'=>'No puedes agendar citas en fechas pasadas.', 'horarios'=>[]]);
    exit;
}

$stmt = $pdo->prepare("SELECT s.id_servicio, s.id_doctor, s.nombre servicio, u.nombre doctor
                       FROM servicios s
                       JOIN doctores d ON s.id_doctor=d.id_doctor
                       JOIN usuarios u ON d.id_usuario=u.id_usuario
                       WHERE s.id_servicio=? AND s.activo=1 AND d.activo=1");
$stmt->execute([$id_servicio]);
$servicio = $stmt->fetch();
if (!$servicio) {
    echo json_encode(['ok'=>false, 'msg'=>'Servicio no válido.', 'horarios'=>[]]);
    exit;
}

$diaMap = [
    'Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves',
    'Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'
];
$dia = $diaMap[date('l', strtotime($fecha))] ?? '';

$stmt = $pdo->prepare("SELECT hora_inicio, hora_fin FROM horarios_doctores
                       WHERE id_doctor=? AND dia_semana=? AND activo=1
                       ORDER BY hora_inicio");
$stmt->execute([$servicio['id_doctor'], $dia]);
$rangos = $stmt->fetchAll();

if (!$rangos) {
    echo json_encode([
        'ok'=>true,
        'msg'=>'El doctor no tiene horario disponible para este día.',
        'doctor'=>$servicio['doctor'],
        'horarios'=>[]
    ]);
    exit;
}

$stmt = $pdo->prepare("SELECT hora FROM citas
                       WHERE id_doctor=? AND fecha=? AND estado IN ('Pendiente','Confirmada')");
$stmt->execute([$servicio['id_doctor'], $fecha]);
$ocupados = array_flip(array_map(fn($r)=>substr($r['hora'],0,5), $stmt->fetchAll()));

$horarios = [];
$ahoraHora = date('H:i');
foreach ($rangos as $r) {
    $inicio = strtotime($fecha.' '.$r['hora_inicio']);
    $fin = strtotime($fecha.' '.$r['hora_fin']);
    for ($t=$inicio; $t < $fin; $t += 60*60) {
        $valor = date('H:i', $t);
        if ($fecha == $hoy && $valor <= $ahoraHora) continue;
        if (isset($ocupados[$valor])) continue;
        $horarios[] = [
            'value' => $valor.':00',
            'label' => date('g:i A', $t)
        ];
    }
}

$msg = count($horarios) ? 'Horarios disponibles de '.$servicio['doctor'].' para '.$dia.'.' : 'No quedan horarios libres para este día.';
echo json_encode(['ok'=>true, 'msg'=>$msg, 'doctor'=>$servicio['doctor'], 'horarios'=>$horarios]);
