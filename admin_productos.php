<?php
session_start();
require 'config/db.php';

// Obtener todos los productos
$stmt = $pdo->query("SELECT * FROM productos ORDER BY id DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agregar un nuevo producto con imagen opcional
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar'])) {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $descripcion = htmlspecialchars(trim($_POST['descripcion']));
    $precio = floatval($_POST['precio']);
    $stock = intval($_POST['stock']);
    $nombreImagen = "default.jpg"; // Imagen predeterminada

    // Verificar si se subió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $directorioDestino = "public/imagenes/"; // Carpeta donde se guardarán las imágenes
        $nombreImagen = basename($_FILES["imagen"]["name"]); // Nombre original de la imagen
        $rutaImagen = $directorioDestino . $nombreImagen; // Ruta completa

        // Mover la imagen al directorio deseado
        if (!move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaImagen)) {
            $nombreImagen = "default.jpg"; // Si hay error, usar la imagen predeterminada
        }
    }

    // Guardar el producto en la base de datos
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen, stock) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nombre, $descripcion, $precio, $nombreImagen, $stock]);
    header("Location: admin_productos.php");
    exit();
}

// Eliminar un producto
if (isset($_GET['eliminar'])) {
    $producto_id = $_GET['eliminar'];

    // Obtener la imagen del producto antes de eliminarlo
    $stmt = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto && $producto['imagen'] !== "default.jpg") {
        $rutaImagen = "public/imagenes/" . $producto['imagen'];
        if (file_exists($rutaImagen)) {
            unlink($rutaImagen); // Eliminar el archivo de imagen si no es la imagen predeterminada
        }
    }

    // Eliminar el producto de la base de datos
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    header("Location: admin_productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrar Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Tienda Five</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin_productos.php">📦 Administrar Productos</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_pedidos.php">📜 Pedidos</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Administración de Productos</h1>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Formulario para agregar productos -->
    <h3 class="mt-4">Agregar Producto</h3>
    <form method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
        </div>
        <div class="mb-3">
            <label for="precio" class="form-label">Precio</label>
            <input type="number" step="0.01" class="form-control" id="precio" name="precio" required>
        </div>
        <div class="mb-3">
            <label for="stock" class="form-label">Stock</label>
            <input type="number" class="form-control" id="stock" name="stock" required>
        </div>
        <div class="mb-3">
            <label for="imagen" class="form-label">Imagen del Producto (Opcional)</label>
            <input type="file" class="form-control" id="imagen" name="imagen" accept="image/*">
        </div>
        <button type="submit" name="agregar" class="btn btn-success w-100">✅ Agregar Producto</button>
    </form>

    <!-- Tabla de productos -->
    <h3 class="mt-5">Lista de Productos</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Imagen</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto['id']; ?></td>
                    <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['stock']; ?></td>
                    <td>
                        <img src="public/imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                             width="50" 
                             onerror="this.src='public/imagenes/default.jpg';">
                    </td>
                    <td>
                        <a href="admin_productos.php?eliminar=<?php echo $producto['id']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
