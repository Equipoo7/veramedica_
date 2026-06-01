<?php
include 'includes/header.php';
$numeroFarmacia = '52229234566';
$mensajeWA = urlencode('Hola, quiero información sobre medicamentos, servicios o citas en Farmacia VeraMedica.');
$linkWA = "https://wa.me/$numeroFarmacia?text=$mensajeWA";
?>
<div class="hero" style="text-align:left;padding:18px 40px;">
    <h1 style="font-size:32px;margin-bottom:5px;">
        Contacto y ubicación
    </h1>
    <p style="font-size:18px;margin:0;">
        Estamos para atenderte
    </p>
</div>

<div class="box" style="padding:0;overflow:hidden;border-radius:16px;">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3769.4690092168253!2d-96.119294!3d19.130936699999996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x85c341f80804d02f%3A0x74ffd5c21d47890e!2sVera%20Medica!5e0!3m2!1ses-419!2smx!4v1780275925058!5m2!1ses-419!2smx"
        width="100%"
        height="380"
        style="border:0;"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>
    <div class="grid2">
        <div class="box">
            <h2>Información de contacto</h2>
            <p>Calle Vicente Suárez s/n<br>Col.Manantial, C.P. 94297, Heroica Veracruz, Ver., México.</p>
            <h2>229234566</h2>
            <p>WhatsApp directo para pedidos, dudas sobre medicamentos, servicios y citas.</p>
            <a class="btn" href="<?= $linkWA ?>" target="_blank">Preguntar por WhatsApp</a>
        </div>
        <div class="box">
            <h2>Horarios de atención</h2>
            <p>Lunes a Viernes 8:00–20:00</p>
            <p>Sábado 9:00–18:00</p>
            <p>Domingo 10:00–15:00</p>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
