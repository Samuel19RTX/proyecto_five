<?php
session_start();
require 'config/db.php';

$productosEnCarrito = [];
$total = 0;

if (!empty($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $producto['cantidad'] = $cantidad;
            $producto['subtotal'] = $producto['precio'] * $cantidad;
            $total += $producto['subtotal'];
            $productosEnCarrito[] = $producto;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito de Compras</title>
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
                <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Carrito</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Carrito de Compras</h1>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productosEnCarrito as $producto): ?>
                <tr>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['cantidad']; ?></td>
                    <td>$<?php echo number_format($producto['subtotal'], 2); ?></td>
                    <td>
                        <button class="btn btn-danger remove-from-cart" data-id="<?php echo $producto['id']; ?>">❌ Eliminar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Total a pagar: $<?php echo number_format($total, 2); ?></h3>
</div>

</body>
</html>
