<?php
require 'config/conexion.php';
include 'includes/header.php';

$productos = $pdo->query("SELECT * FROM productos WHERE activo=1 AND stock>0 ORDER BY RAND() LIMIT 3")->fetchAll();
$promos = $pdo->query("SELECT * FROM promociones WHERE activo=1 AND (fecha_inicio IS NULL OR fecha_inicio <= CURDATE()) AND (fecha_fin IS NULL OR fecha_fin >= CURDATE()) ORDER BY fecha_fin ASC, id_promocion DESC LIMIT 3")->fetchAll();
$numeroFarmacia = '52229234566';
$mensajeWA = urlencode('Hola, quiero información sobre medicamentos, servicios o citas en Farmacia VeraMedica.');
$linkWA = "https://wa.me/$numeroFarmacia?text=$mensajeWA";

function srcProductoInicio($imagen) {
    if (!$imagen) return 'assets/img/paracetamol.jpg';
    if (str_starts_with($imagen, 'uploads/')) return htmlspecialchars($imagen);
    return 'assets/img/' . htmlspecialchars($imagen);
}
?>
<section class="hero">
    <h1>Farmacia VeraMedica</h1>
    <p>Medicamentos genéricos de calidad y servicios médicos profesionales.</p>
   
</section>

<div class="container">

    <div class="promo-row">

        <?php if($promos): ?>

            <div class="promo-banner">

                <?php foreach($promos as $promo): ?>

                    <div class="promo-discount">
                        <strong><?= (int)$promo['descuento'] ?>%</strong>
                        <span>DESCUENTO</span>
                    </div>

                    <div class="promo-info">
                        <small>PROMOCIÓN VIGENTE</small>
                        <h3><?= htmlspecialchars($promo['titulo']) ?></h3>
                        <p><?= htmlspecialchars($promo['descripcion']) ?></p>
                        <a href="catalogo.php">Ver productos en oferta</a>
                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="promo-banner">
                <div class="promo-discount">
                    <strong>0%</strong>
                    <span>PROMO</span>
                </div>

                <div class="promo-info">
                    <small>PROMOCIONES</small>
                    <h3>Sin promociones vigentes</h3>
                    <p>Por el momento no hay promociones activas.</p>
                    <a href="catalogo.php">Ver catálogo</a>
                </div>
            </div>

        <?php endif; ?>

        <div class="consulta-banner">

            <div class="consulta-icon">
                🩺
            </div>

            <div class="consulta-info">
                <small>CONSULTORIO EN FARMACIA</small>

                <h3>Consulta médica aquí mismo</h3>

                <p>
                    Agenda tu cita y recibe confirmación automática por correo electrónico.
                </p>

                <div class="banner-actions">
                    <a class="btn-light" href="citas.php">
                        Agendar cita
                    </a>

                    <a class="btn-light" href="<?= $linkWA ?>" target="_blank">
                        WhatsApp
                    </a>
                </div>
            </div>

        </div>

    </div>

    <h1>Productos Destacados</h1>
   
    <div class="cards">
        <?php foreach($productos as $p): ?>
            <div class="card">
                <img src="<?= srcProductoInicio($p['imagen']) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>">
                <h2><?= htmlspecialchars($p['nombre']) ?></h2>
                <p><?= htmlspecialchars($p['descripcion']) ?></p>
                <p>$<?= number_format($p['precio'],2) ?> MXN</p>
                <span class="badge"><?= $p['stock']>0?'Disponible':'No Disponible' ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
