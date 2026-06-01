<?php
include '_top.php';

$msg = '';
$err = '';

if (isset($_GET['desactivar'])) {
    $id = (int)$_GET['desactivar'];
    $pdo->prepare("UPDATE promociones SET activo=0 WHERE id_promocion=?")->execute([$id]);
    $msg = 'Promoción desactivada correctamente.';
}

if (isset($_GET['activar'])) {
    $id = (int)$_GET['activar'];
    $pdo->prepare("UPDATE promociones SET activo=1 WHERE id_promocion=?")->execute([$id]);
    $msg = 'Promoción activada correctamente.';
}

if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $pdo->prepare("DELETE FROM promociones WHERE id_promocion=?")->execute([$id]);
    $msg = 'Promoción eliminada correctamente.';
}

$editar = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM promociones WHERE id_promocion=?");
    $stmt->execute([(int)$_GET['editar']]);
    $editar = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $descuento = (int)($_POST['descuento'] ?? 0);
    $inicio = $_POST['inicio'] ?: null;
    $fin = $_POST['fin'] ?: null;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $id = (int)($_POST['id_promocion'] ?? 0);

    if ($titulo === '') {
        $err = 'El título de la promoción es obligatorio.';
    } elseif ($inicio && $fin && $inicio > $fin) {
        $err = 'La fecha de inicio no puede ser mayor que la fecha final.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE promociones SET titulo=?, descripcion=?, descuento=?, fecha_inicio=?, fecha_fin=?, activo=? WHERE id_promocion=?");
            $stmt->execute([$titulo, $descripcion, $descuento, $inicio, $fin, $activo, $id]);
            $msg = 'Promoción actualizada correctamente.';
            $editar = null;
        } else {
            $stmt = $pdo->prepare("INSERT INTO promociones(titulo, descripcion, descuento, fecha_inicio, fecha_fin, activo) VALUES(?,?,?,?,?,?)");
            $stmt->execute([$titulo, $descripcion, $descuento, $inicio, $fin, $activo]);
            $msg = 'Promoción agregada correctamente.';
        }
    }
}

$promos = $pdo->query("SELECT * FROM promociones ORDER BY activo DESC, fecha_fin DESC, id_promocion DESC")->fetchAll();

if ($msg) echo "<div class='alert ok'>$msg</div>";
if ($err) echo "<div class='alert error'>$err</div>";
?>

<h1>Promociones</h1>
<p>Las promociones activas y vigentes se muestran automáticamente en la página de inicio.</p>

<div class="box form">
    <h2><?= $editar ? 'Editar promoción' : 'Agregar promoción' ?></h2>

    <form method="post">
        <?php if($editar): ?>
            <input type="hidden" name="id_promocion" value="<?= (int)$editar['id_promocion'] ?>">
        <?php endif; ?>

        <label>Título</label>
        <input name="titulo" placeholder="Ejemplo: Domingos de ahorro" required
               value="<?= htmlspecialchars($editar['titulo'] ?? '') ?>">

        <label>Descripción</label>
        <textarea name="descripcion" placeholder="Describe la promoción"><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>

        <label>Descuento (%)</label>
        <input type="number" name="descuento" min="0" max="100"
               value="<?= htmlspecialchars($editar['descuento'] ?? '0') ?>">

        <div class="grid2">
            <div>
                <label>Fecha inicio</label>
                <input type="date" name="inicio"
                       value="<?= htmlspecialchars($editar['fecha_inicio'] ?? '') ?>">
            </div>

            <div>
                <label>Fecha fin</label>
                <input type="date" name="fin"
                       value="<?= htmlspecialchars($editar['fecha_fin'] ?? '') ?>">
            </div>
        </div>

        <label style="display:flex;gap:8px;align-items:center;margin:8px 0 14px">
            <input type="checkbox" name="activo" value="1" style="width:auto"
                <?= (!$editar || (int)$editar['activo'] === 1) ? 'checked' : '' ?>>
            Promoción activa
        </label>

        <button class="btn">Guardar promoción</button>

        <?php if($editar): ?>
            <a class="btn" href="promociones.php">Cancelar edición</a>
        <?php endif; ?>
    </form>
</div>

<div class="table-responsive" style="margin-top:22px">
<table>
    <tr>
        <th>Título</th>
        <th>Descripción</th>
        <th>Descuento</th>
        <th>Vigencia</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>

    <?php foreach($promos as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['titulo']) ?></td>
        <td><?= htmlspecialchars($p['descripcion']) ?></td>
        <td><?= (int)$p['descuento'] ?>%</td>
        <td><?= htmlspecialchars($p['fecha_inicio']) ?> a <?= htmlspecialchars($p['fecha_fin']) ?></td>
        <td><?= (int)$p['activo'] === 1 ? 'Activa' : 'Inactiva' ?></td>

        <td class="acciones">
            <a href="?editar=<?= (int)$p['id_promocion'] ?>">Editar</a>

            <?php if((int)$p['activo'] === 1): ?>
                <a href="?desactivar=<?= (int)$p['id_promocion'] ?>"
                   onclick="return confirm('¿Desactivar promoción?')">
                   Desactivar
                </a>
            <?php else: ?>
                <a href="?activar=<?= (int)$p['id_promocion'] ?>">
                   Activar
                </a>
            <?php endif; ?>

            <a href="?eliminar=<?= (int)$p['id_promocion'] ?>"
               onclick="return confirm('¿Seguro que deseas eliminar esta promoción?')">
               Eliminar
            </a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div>

<?php include '_bottom.php'; ?>