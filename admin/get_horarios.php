<?php
require '../config/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$id_servicio = isset($_GET['id_servicio']) ? (int)$_GET['id_servicio'] : 0;
$fecha = $_GET['fecha'] ?? '';
$id_cita = isset($_GET['id_cita']) ? (int)$_GET['id_cita'] : 0;

if (!$id_servicio || !$fecha) {
    echo json_encode(['horarios' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT s.*, d.id_doctor, u.nombre doctor
    FROM servicios s
    JOIN doctores d ON s.id_doctor = d.id_doctor
    JOIN usuarios u ON d.id_usuario = u.id_usuario
    WHERE s.id_servicio = ?
    AND s.activo = 1
    AND d.activo = 1
");

$stmt->execute([$id_servicio]);
$servicio = $stmt->fetch();

if (!$servicio) {
    echo json_encode(['horarios' => []]);
    exit;
}

$diaMap = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];

$dia = $diaMap[date('l', strtotime($fecha))];

$stmt = $pdo->prepare("
    SELECT hora_inicio, hora_fin
    FROM horarios_doctores
    WHERE id_doctor = ?
    AND dia_semana = ?
    AND activo = 1
");

$stmt->execute([$servicio['id_doctor'], $dia]);
$rangos = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT hora
    FROM citas
    WHERE id_doctor = ?
    AND fecha = ?
    AND estado IN ('Pendiente','Confirmada')
    AND id_cita <> ?
");

$stmt->execute([$servicio['id_doctor'], $fecha, $id_cita]);

$ocupados = [];

foreach ($stmt->fetchAll() as $o) {
    $ocupados[substr($o['hora'], 0, 5)] = true;
}

$horarios = [];

foreach ($rangos as $r) {
    $inicio = strtotime($fecha . ' ' . $r['hora_inicio']);
    $fin = strtotime($fecha . ' ' . $r['hora_fin']);

    for ($t = $inicio; $t < $fin; $t += 3600) {
        $hora = date('H:i', $t);

        if (isset($ocupados[$hora])) {
            continue;
        }

        $horarios[] = [
            'value' => $hora . ':00',
            'label' => date('g:i A', $t)
        ];
    }
}

echo json_encode(['horarios' => $horarios]);