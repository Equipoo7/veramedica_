<?php 
include '_top.php'; 

$citas = $pdo->query("SELECT COUNT(*) total FROM citas")->fetch()['total'];
$prod = $pdo->query("SELECT COUNT(*) total FROM productos")->fetch()['total'];
$servicios = $pdo->query("SELECT COUNT(*) total FROM servicios")->fetch()['total'];

$personal = $pdo->query("
    SELECT COUNT(*) total 
    FROM usuarios 
    WHERE id_rol IN (
        SELECT id_rol 
        FROM roles 
        WHERE nombre IN ('mostrador', 'doctor')
    )
")->fetch()['total'];
?>

<h1>Panel de mostrador</h1>

<div class="dashboard-grid">

    <a class="dash-card" href="citas.php">
        <h2>Citas registradas</h2>
        <p class="dash-number"><?= $citas ?></p>
        <span>Ver y administrar citas</span>
    </a>

    <a class="dash-card" href="productos.php">
        <h2>Productos</h2>
        <p class="dash-number"><?= $prod ?></p>
        <span>Agregar, editar o eliminar productos</span>
    </a>

    <a class="dash-card" href="servicios.php">
        <h2>Servicios</h2>
        <p class="dash-number"><?= $servicios ?></p>
        <span>Editar servicios médicos</span>
    </a>

    <a class="dash-card" href="personal.php">
        <h2>Personal</h2>
        <p class="dash-number"><?= $personal ?></p>
        <span>Registrar, editar o desactivar personal</span>
    </a>

</div>

<?php include '_bottom.php'; ?>