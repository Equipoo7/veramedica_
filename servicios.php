<?php 
error_reporting(E_ALL); 
ini_set('display_errors', 1); 

require 'config/conexion.php';
include 'includes/header.php';

// 1. Consulta general de todos los servicios activos
$servicios = $pdo->query("
    SELECT s.*, u.nombre doctor
    FROM servicios s
    LEFT JOIN doctores d ON s.id_doctor=d.id_doctor
    LEFT JOIN usuarios u ON d.id_usuario=u.id_usuario
    WHERE s.activo=1
    ORDER BY s.id_servicio
")->fetchAll();

// 2. Filtrado dinámico de los Ultrasonidos del Dr. Edgar
$edgar = array_filter($servicios, fn($s)=>
    stripos($s['doctor'] ?? '', 'Edgar') !== false ||
    stripos($s['descripcion'] ?? '', 'Edgar') !== false
);

// 3. Filtrado dinámico de las Especialidades de la Dra. Isis (Para Ellas)
$isis = array_filter($servicios, fn($s)=>
    stripos($s['doctor'] ?? '', 'Isis') !== false ||
    stripos($s['nombre'] ?? '', 'Gine') !== false ||
    stripos($s['nombre'] ?? '', 'Pélvico') !== false ||
    stripos($s['nombre'] ?? '', 'Endovaginal') !== false ||
    stripos($s['nombre'] ?? '', 'Mama') !== false
);

// 4. Filtrado dinámico de Servicios Médicos Generales (Todo lo que no es de Edgar ni Isis)
$generales = array_filter($servicios, fn($s)=>
    !in_array($s, $edgar, true) &&
    !in_array($s, $isis, true)
);

// 5. Dividir matemáticamente la lista de generales en 2 columnas simétricas
$total_generales = count($generales);
$mitad = ceil($total_generales / 2);
$columna_izquierda = array_slice($generales, 0, $mitad);
$columna_derecha = array_slice($generales, $mitad);
?>

<div class="container full-width">

    <div class="service-hero">
        <div>
            <h1>Servicios que ofrecemos</h1>
            <p>Atención médica profesional para tu bienestar</p>
        </div>
    </div>

    <section class="service-panel">
        <div class="service-layout">

            <div class="service-left">
                <div class="doctor-title-container">
                    <span class="doctor-icon">🩺</span>
                    <div>
                        <h2>Servicios del Dr. Edgar</h2>
                        <p>Atención de diagnóstico por ultrasonido</p>
                    </div>
                </div>

                <div class="service-image-card">
                    <img src="assets/img/eco_0.jpg" alt="Ultrasonido" class="ultra-img-real">
                </div>

                <div class="yellow-card">
                    <div class="yellow-card-text">
                        <h3>Ecografía<br>Por Rastreo</h3>
                        <p class="yellow-sub">➔ Complemento de evaluación</p>
                        <small>(No incluye entrega de imágenes ni reporte escrito).</small>
                    </div>
                    <span class="eco-price">$150</span>
                </div>
            </div>

            <div class="service-center">
                <div class="service-list wide-list">
                    <?php if (!empty($edgar)): ?>
                        <?php foreach($edgar as $s): ?>
                            <div class="service-item">
                                <span class="service-name-container">
                                    <span class="item-icon">📋</span>
                                    <?= htmlspecialchars($s['nombre']) ?>
                                </span>
                                <span class="price">$<?= number_format($s['precio'], 0) ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="padding: 15px; text-align: center; color: rgba(255,255,255,0.6);">No hay ultrasonidos registrados.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="service-right">
                <div class="gender-block pink-block single-gender-layout">
                    <h2 class="gender-title">♀ Para Ellas</h2>
                    <div class="service-list inside-gender">
                        <?php if (!empty($isis)): ?>
                            <?php foreach($isis as $s): ?>
                                <div class="service-item">
                                    <span class="service-name-container">
                                        <span class="item-icon">🌸</span>
                                        <?= htmlspecialchars($s['nombre']) ?>
                                    </span>
                                    <span class="price">$<?= number_format($s['precio'], 0) ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 15px; text-align: center; color: #666;">No hay servicios de ginecología registrados.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="general-services-panel">
        <div class="general-container-block">
          
                

        </div>
    </section>

</div>

<?php include 'includes/footer.php'; ?>