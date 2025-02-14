<?php
session_start();
require 'config/db.php';

// Verificar si el carrito está vacío
if (empty($_SESSION['carrito'])) {
    header("Location: index.php");
    exit();
}

// Obtener total del carrito y verificar stock
$total = 0;
foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
    $stmt = $pdo->prepare("SELECT nombre, precio, stock FROM productos WHERE id = ?");
    $stmt->execute([$producto_id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        die("Error: Producto no encontrado.");
    }

    if ($producto['stock'] < $cantidad) {
        die("Error: No hay suficiente stock de " . $producto['nombre']);
    }

    $total += $producto['precio'] * $cantidad;
}

// Procesar pago al enviar el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = htmlspecialchars(trim($_POST["nombre"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $direccion = htmlspecialchars(trim($_POST["direccion"]));
    $telefono = htmlspecialchars(trim($_POST["telefono"]));
    $tarjeta = trim($_POST["tarjeta"]);
    $expiracion = trim($_POST["expiracion"]);
    $cvv = trim($_POST["cvv"]);

    // Validaciones
    if (empty($nombre) || empty($email) || empty($direccion) || empty($telefono) || empty($tarjeta) || empty($expiracion) || empty($cvv)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (!preg_match("/^[0-9]{16}$/", $tarjeta)) {
        $error = "Número de tarjeta inválido.";
    } elseif (!preg_match("/^(0[1-9]|1[0-2])\/\d{2}$/", $expiracion)) {
        $error = "Fecha de expiración inválida.";
    } elseif (!preg_match("/^[0-9]{3,4}$/", $cvv)) {
        $error = "Código CVV inválido.";
    } else {
        // Insertar usuario si no existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, direccion, telefono, password) VALUES (?, ?, ?, ?, 'default')");
            $stmt->execute([$nombre, $email, $direccion, $telefono]);
            $usuario_id = $pdo->lastInsertId();
        } else {
            $usuario_id = $usuario['id'];
        }

        // Insertar pago
        $stmt = $pdo->prepare("INSERT INTO pagos (usuario_id, monto, metodo_pago) VALUES (?, ?, 'Tarjeta de Crédito')");
        $stmt->execute([$usuario_id, $total]);

        // Reducir stock de los productos comprados
        foreach ($_SESSION['carrito'] as $producto_id => $cantidad) {
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$cantidad, $producto_id]);
        }

        // Vaciar el carrito
        unset($_SESSION['carrito']);
        $_SESSION['compra_exitosa'] = true;

        // Redirigir a la página de éxito
        header("Location: success.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout - Tienda Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
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
    <h1 class="text-center">Finalizar Compra</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre Completo</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección de Envío</label>
            <input type="text" class="form-control" id="direccion" name="direccion" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" required>
        </div>
        <h4>Detalles de la Tarjeta</h4>
        <div class="mb-3">
            <label for="tarjeta" class="form-label">Número de Tarjeta</label>
            <input type="text" class="form-control" id="tarjeta" name="tarjeta" maxlength="16" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="expiracion" class="form-label">Fecha de Expiración (MM/YY)</label>
                <input type="text" class="form-control" id="expiracion" name="expiracion" maxlength="5" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="cvv" class="form-label">CVV</label>
                <input type="text" class="form-control" id="cvv" name="cvv" maxlength="4" required>
            </div>
        </div>
        <h3>Total a pagar: $<?php echo number_format($total, 2); ?></h3>
        <button type="submit" class="btn btn-success w-100">💳 Confirmar Pago</button>
    </form>
</div>

<script>
document.querySelector("form").addEventListener("submit", function(event) {
    let tarjeta = document.getElementById("tarjeta").value;
    let expiracion = document.getElementById("expiracion").value;
    let cvv = document.getElementById("cvv").value;

    if (!/^[0-9]{16}$/.test(tarjeta)) {
        alert("El número de tarjeta debe tener 16 dígitos.");
        event.preventDefault();
    }

    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiracion)) {
        alert("La fecha de expiración debe estar en formato MM/YY.");
        event.preventDefault();
    }

    if (!/^[0-9]{3,4}$/.test(cvv)) {
        alert("El CVV debe tener 3 o 4 dígitos.");
        event.preventDefault();
    }
});
</script>

</body>
</html>
