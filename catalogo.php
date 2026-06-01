<?php
require 'config/conexion.php';
include 'includes/header.php';

$q = $_GET['q'] ?? '';

$where = ["p.activo = 1"];
$params = [];

if ($q != '') {
    $where[] = "p.nombre LIKE ?";
    $params[] = "%".$q."%";
}

if (!empty($_GET['categoria'])) {
    $where[] = "p.id_categoria = ?";
    $params[] = $_GET['categoria'];
}

if (isset($_GET['disponibilidad']) && $_GET['disponibilidad'] !== '') {

    if ($_GET['disponibilidad'] == '1') {
        $where[] = "p.stock > 0";
    }

    if ($_GET['disponibilidad'] == '0') {
        $where[] = "p.stock <= 0";
    }
}

$sql = "
SELECT p.*, c.nombre categoria
FROM productos p
LEFT JOIN categorias c
ON p.id_categoria = c.id_categoria
WHERE ".implode(" AND ", $where)."
ORDER BY p.nombre
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

function srcProducto($imagen) {
    if (!$imagen) return 'assets/img/paracetamol.jpg';

    if (str_starts_with($imagen, 'uploads/')) {
        return $imagen;
    }

    return 'assets/img/' . $imagen;
}
?>

<div class="searchbar">
    <h1>Buscador de Medicamentos</h1>

    <form method="GET">

        <input
            name="q"
            placeholder="Buscar medicamento"
            value="<?=htmlspecialchars($q)?>"
        >

    </form>
</div>

<div class="container full-width catalogo-layout">

    <aside class="filtros box">

        <form method="GET">

            <h1>Filtros</h1>

            <p><b>Categoría</b></p>

            <select name="categoria">

                <option value="">Todas</option>

                <?php
                $cats = $pdo->query("
                    SELECT *
                    FROM categorias
                    ORDER BY nombre
                ");

                foreach($cats as $c){

                    $selected =
                    (($_GET['categoria'] ?? '') == $c['id_categoria'])
                    ? 'selected'
                    : '';

                    echo "
                    <option value='{$c['id_categoria']}' $selected>
                        {$c['nombre']}
                    </option>";
                }
                ?>

            </select>

            <p><b>Disponibilidad</b></p>

            <select name="disponibilidad">

                <option value="">Todos</option>

                <option value="1"
                <?= (($_GET['disponibilidad'] ?? '') == '1')
                ? 'selected'
                : '' ?>>
                    Disponibles
                </option>

                <option value="0"
                <?= (($_GET['disponibilidad'] ?? '') == '0')
                ? 'selected'
                : '' ?>>
                    No disponibles
                </option>

            </select>

            <br><br>
<button type="submit" class="btn">
    Buscar
</button>

<a href="catalogo.php" class="btn">
    Ver todos
</a>
        </form>

    </aside>

    <section>

        <div class="cards">

        <?php foreach($productos as $p): ?>

            <a class="card product-card"
               href="#producto<?=$p['id_producto']?>">

                <img
                    src="<?=htmlspecialchars(srcProducto($p['imagen']))?>"
                    alt="<?=htmlspecialchars($p['nombre'])?>"
                >

                <h2><?=htmlspecialchars($p['nombre'])?></h2>

                <p><?=htmlspecialchars($p['descripcion'])?></p>

                <p>
                    $<?=number_format($p['precio'],2)?> MXN
                </p>

                <span class="badge <?= $p['stock']<=0?'bad':'' ?>">

                    <?= $p['stock']>0
                    ? 'Disponible'
                    : 'No Disponible' ?>

                </span>

            </a>

            <div class="modal"
                 id="producto<?=$p['id_producto']?>">

                <div class="modal-card">

                    <a class="modal-close" href="#">×</a>

                    <img
                        src="<?=htmlspecialchars(srcProducto($p['imagen']))?>"
                        style="
                        width:100%;
                        height:220px;
                        object-fit:contain;
                        "
                    >

                    <h2>
                        <?=htmlspecialchars($p['nombre'])?>
                    </h2>

                    <p>
                        <b>Descripción:</b>
                        <?=htmlspecialchars($p['descripcion'])?>
                    </p>

                    <p>
                        <b>Categoría:</b>
                        <?=htmlspecialchars($p['categoria']
                        ?? 'Sin categoría')?>
                    </p>

                    <p>
                        <b>Precio:</b>
                        $<?=number_format($p['precio'],2)?> MXN
                    </p>

                    <p>
                        <b>Stock:</b>
                        <?=$p['stock']?>
                    </p>

                    <span class="badge <?= $p['stock']<=0?'bad':'' ?>">

                        <?= $p['stock']>0
                        ? 'Disponible'
                        : 'No Disponible' ?>

                    </span>

                </div>

            </div>

        <?php endforeach; ?>

        </div>

    </section>

</div>

<?php include 'includes/footer.php'; ?>