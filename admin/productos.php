<?php
include '_top.php';

$uploadDirAbs = __DIR__ . '/../uploads/productos/';
$uploadDirDb = 'uploads/productos/';
if (!is_dir($uploadDirAbs)) {
    mkdir($uploadDirAbs, 0777, true);
}

function subirImagenProducto($campo, $uploadDirAbs, $uploadDirDb, &$error) {
    if (!isset($_FILES[$campo]) || $_FILES[$campo]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($_FILES[$campo]['error'] !== UPLOAD_ERR_OK) {
        $error = 'No se pudo subir la imagen. Intenta de nuevo.';
        return false;
    }
    $permitidos = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp', 'image/gif'=>'gif'];
    $mime = mime_content_type($_FILES[$campo]['tmp_name']);
    if (!isset($permitidos[$mime])) {
        $error = 'La imagen debe ser JPG, PNG, WEBP o GIF.';
        return false;
    }
    if ($_FILES[$campo]['size'] > 3 * 1024 * 1024) {
        $error = 'La imagen no debe pesar más de 3 MB.';
        return false;
    }
    $nombreSeguro = 'producto_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $permitidos[$mime];
    $destino = $uploadDirAbs . $nombreSeguro;
    if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $destino)) {
        $error = 'No se pudo guardar la imagen en la carpeta uploads/productos.';
        return false;
    }
    return $uploadDirDb . $nombreSeguro;
}

$msg = '';
$err = '';

if (isset($_GET['eliminar'])) {
    $pdo->prepare("UPDATE productos SET activo=0 WHERE id_producto=?")->execute([(int)$_GET['eliminar']]);
    $msg = 'Producto eliminado correctamente.';
}

$editando = null;
if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id_producto=? AND activo=1");
    $stmt->execute([(int)$_GET['editar']]);
    $editando = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_producto'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $id_categoria = (int)($_POST['id_categoria'] ?? 1);

    if ($nombre === '' || $precio < 0 || $stock < 0) {
        $err = 'Revisa los datos: nombre, precio y stock son obligatorios.';
    } else {
        $imagenNueva = subirImagenProducto('imagen', $uploadDirAbs, $uploadDirDb, $err);
        if ($imagenNueva !== false) {
            if ($id > 0) {
                if ($imagenNueva) {
                    $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, id_categoria=?, imagen=? WHERE id_producto=?";
                    $pdo->prepare($sql)->execute([$nombre, $descripcion, $precio, $stock, $id_categoria, $imagenNueva, $id]);
                } else {
                    $sql = "UPDATE productos SET nombre=?, descripcion=?, precio=?, stock=?, id_categoria=? WHERE id_producto=?";
                    $pdo->prepare($sql)->execute([$nombre, $descripcion, $precio, $stock, $id_categoria, $id]);
                }
                $msg = 'Producto actualizado correctamente.';
                $editando = null;
            } else {
                if (!$imagenNueva) {
                    $imagenNueva = 'paracetamol.jpg';
                }
                $sql = "INSERT INTO productos(nombre, descripcion, precio, stock, imagen, id_categoria, activo) VALUES(?,?,?,?,?,?,1)";
                $pdo->prepare($sql)->execute([$nombre, $descripcion, $precio, $stock, $imagenNueva, $id_categoria]);
                $msg = 'Producto agregado correctamente.';
            }
        }
    }
}

$categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
$productos = $pdo->query("SELECT p.*, c.nombre categoria FROM productos p LEFT JOIN categorias c ON p.id_categoria=c.id_categoria WHERE p.activo=1 ORDER BY p.id_producto DESC")->fetchAll();
function srcAdminProducto($imagen) {
    if (!$imagen) return '../assets/img/paracetamol.jpg';
    if (str_starts_with($imagen, 'uploads/')) return '../' . $imagen;
    return '../assets/img/' . $imagen;
}
?>
<h1>Productos</h1>
<?php if($msg): ?><div class="alert ok"><?=$msg?></div><?php endif; ?>
<?php if($err): ?><div class="alert error"><?=$err?></div><?php endif; ?>

<div class="box form">
    <h2><?= $editando ? 'Editar producto' : 'Agregar producto' ?></h2>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_producto" value="<?= htmlspecialchars($editando['id_producto'] ?? '') ?>">
        <label>Nombre del producto</label>
        <input name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($editando['nombre'] ?? '') ?>">
        <label>Descripción</label>
        <textarea name="descripcion" placeholder="Descripción del producto" rows="3"><?= htmlspecialchars($editando['descripcion'] ?? '') ?></textarea>
        <label>Precio</label>
        <input type="number" step="0.01" min="0" name="precio" placeholder="Precio" required value="<?= htmlspecialchars($editando['precio'] ?? '') ?>">
        <label>Stock</label>
        <input type="number" min="0" name="stock" placeholder="Stock" required value="<?= htmlspecialchars($editando['stock'] ?? '') ?>">
        <label>Categoría</label>
        <select name="id_categoria">
            <?php foreach($categorias as $cat): ?>
                <option value="<?=$cat['id_categoria']?>" <?= (($editando['id_categoria'] ?? 1)==$cat['id_categoria'])?'selected':'' ?>><?=htmlspecialchars($cat['nombre'])?></option>
            <?php endforeach; ?>
        </select>
        <label>Imagen del producto</label>
        <?php if($editando && !empty($editando['imagen'])): ?>
            <div style="margin:8px 0"><img src="<?=htmlspecialchars(srcAdminProducto($editando['imagen']))?>" style="max-width:150px;max-height:100px;object-fit:contain;border:1px solid #ccc;border-radius:8px;background:#fff"></div>
            <small>Si no seleccionas otra imagen, se conserva la actual.</small>
        <?php endif; ?>
        <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
        <button class="btn"><?= $editando ? 'Actualizar producto' : 'Guardar producto' ?></button>
        <?php if($editando): ?><a class="btn" href="productos.php" style="background:#777">Cancelar edición</a><?php endif; ?>
    </form>
</div>

<table>
    <tr><th>Imagen</th><th>Nombre</th><th>Descripción</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Estado</th><th>Acción</th></tr>
    <?php foreach($productos as $p): ?>
    <tr>
        <td><img src="<?=htmlspecialchars(srcAdminProducto($p['imagen']))?>" style="width:75px;height:55px;object-fit:contain;background:#fff"></td>
        <td><?=htmlspecialchars($p['nombre'])?></td>
        <td><?=htmlspecialchars($p['descripcion'])?></td>
        <td><?=htmlspecialchars($p['categoria'] ?? '')?></td>
        <td>$<?=number_format($p['precio'],2)?></td>
        <td><?=$p['stock']?></td>
        <td><span class="badge <?= $p['stock']<=0?'bad':'' ?>"><?= $p['stock']>0?'Disponible':'No disponible' ?></span></td>
        <td class="acciones"><a href="?editar=<?=$p['id_producto']?>">Editar</a><a href="?eliminar=<?=$p['id_producto']?>" onclick="return confirm('¿Eliminar este producto?')">Eliminar</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include '_bottom.php'; ?>
