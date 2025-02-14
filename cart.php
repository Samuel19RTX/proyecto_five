<?php
session_start();
require 'config/db.php';

// Inicializar el carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// Agregar producto al carrito con AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto_id'])) {
    $producto_id = $_POST['producto_id'];
    
    if (isset($_SESSION['carrito'][$producto_id])) {
        $_SESSION['carrito'][$producto_id]++;
    } else {
        $_SESSION['carrito'][$producto_id] = 1;
    }

    echo json_encode(["status" => "success", "message" => "Producto agregado al carrito"]);
    exit();
}

// Eliminar producto del carrito con AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $producto_id = $_POST['remove_id'];
    unset($_SESSION['carrito'][$producto_id]);

    echo json_encode(["status" => "success", "message" => "Producto eliminado del carrito"]);
    exit();
}

// Actualizar cantidades en el carrito con AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['cantidad'] as $id => $cantidad) {
        if ($cantidad > 0) {
            $_SESSION['carrito'][$id] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$id]);
        }
    }

    echo json_encode(["status" => "success", "message" => "Carrito actualizado"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .subtotal, #total {
            font-weight: bold;
            color: #28a745;
        }
    </style>
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
                <li class="nav-item"><a class="nav-link" href="cart.php">🛒 Carrito <span id="cart-count">(0)</span></a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Carrito de Compras</h1>

    <?php if (!empty($_SESSION['carrito'])): ?>
        <form id="updateCartForm">
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
                    <?php
                    $total = 0;
                    foreach ($_SESSION['carrito'] as $producto_id => $cantidad):
                        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
                        $stmt->execute([$producto_id]);
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                        $subtotal = $producto['precio'] * $cantidad;
                        $total += $subtotal;
                    ?>
                        <tr id="row-<?php echo $producto_id; ?>">
                            <td><?php echo $producto['nombre']; ?></td>
                            <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td>
                                <input type="number" name="cantidad[<?php echo $producto_id; ?>]" 
                                       value="<?php echo $cantidad; ?>" min="1" 
                                       class="form-control cantidad-input" 
                                       data-precio="<?php echo $producto['precio']; ?>"
                                       data-id="<?php echo $producto_id; ?>">
                            </td>
                            <td id="subtotal-<?php echo $producto_id; ?>" class="subtotal">
                                $<?php echo number_format($subtotal, 2); ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-item" data-id="<?php echo $producto_id; ?>">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h3>Total: <span id="total">$<?php echo number_format($total, 2); ?></span></h3>
            <button type="submit" class="btn btn-primary">Actualizar Carrito</button>
            <a href="checkout.php" class="btn btn-success">Proceder al Pago</a>
        </form>
    <?php else: ?>
        <p class="text-center">El carrito está vacío.</p>
    <?php endif; ?>
</div>

<!-- Script para actualizar subtotal y total sin recargar -->
<script>
document.querySelectorAll(".cantidad-input").forEach(input => {
    input.addEventListener("input", function() {
        let cantidad = parseInt(this.value);
        let precio = parseFloat(this.getAttribute("data-precio"));
        let id = this.getAttribute("data-id");

        if (cantidad > 0) {
            let nuevoSubtotal = (precio * cantidad).toFixed(2);
            document.getElementById("subtotal-" + id).innerText = "$" + nuevoSubtotal;
            actualizarTotal();
        }
    });
});

// Función para actualizar el total
function actualizarTotal() {
    let total = 0;
    document.querySelectorAll(".subtotal").forEach(elemento => {
        total += parseFloat(elemento.innerText.replace("$", ""));
    });
    document.getElementById("total").innerText = "$" + total.toFixed(2);
}

// Actualizar el carrito sin recargar usando AJAX
document.getElementById("updateCartForm").addEventListener("submit", function(event) {
    event.preventDefault();
    let formData = new FormData(this);

    fetch("cart.php", {
        method: "POST",
        body: new URLSearchParams(formData) + "&update_cart=1"
    })
    .then(response => response.json())
    .then(data => {
        console.log("Carrito actualizado:", data);
    })
    .catch(error => console.error("Error:", error));
});

// Eliminar productos con AJAX
document.querySelectorAll(".remove-item").forEach(button => {
    button.addEventListener("click", function() {
        let id = this.getAttribute("data-id");

        fetch("cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "remove_id=" + id
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById("row-" + id).remove();
            actualizarTotal();
        })
        .catch(error => console.error("Error:", error));
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
