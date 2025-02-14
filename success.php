<?php
session_start();
require 'config/db.php';

// Verificar si hay una sesión de compra válida
if (!isset($_SESSION['compra_exitosa']) || !$_SESSION['compra_exitosa']) {
    header("Location: index.php");
    exit();
}

// Obtener datos de la compra más reciente
$stmt = $pdo->prepare("SELECT p.id, p.monto, p.fecha, u.nombre, u.email, u.direccion, u.telefono 
                        FROM pagos p 
                        JOIN usuarios u ON p.usuario_id = u.id 
                        ORDER BY p.id DESC LIMIT 1");
$stmt->execute();
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

// Limpiar la sesión de compra para evitar recargas duplicadas
unset($_SESSION['compra_exitosa']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Compra Exitosa - Tienda Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .recibo {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border: 2px solid #28a745;
            border-radius: 10px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">Tienda Five</a>
    </div>
</nav>

<!-- Mensaje de Confirmación -->
<div class="container mt-5">
    <div class="recibo text-center">
        <h2 class="text-success">🎉 ¡Compra Exitosa!</h2>
        <p>Gracias por tu compra, <strong><?php echo htmlspecialchars($compra['nombre']); ?></strong>.</p>
        <p>Hemos enviado un correo de confirmación a <strong><?php echo htmlspecialchars($compra['email']); ?></strong>.</p>
        <hr>
        <h4>Detalles del Pedido</h4>
        <p><strong>ID de Compra:</strong> <?php echo $compra['id']; ?></p>
        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($compra['direccion']); ?></p>
        <p><strong>Teléfono de Contacto:</strong> <?php echo htmlspecialchars($compra['telefono']); ?></p>
        <p><strong>Monto Pagado:</strong> $<?php echo number_format($compra['monto'], 2); ?></p>
        <p><strong>Fecha de Pago:</strong> <?php echo $compra['fecha']; ?></p>
        <hr>
        <a href="index.php" class="btn btn-primary">🏠 Volver a la Tienda</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
