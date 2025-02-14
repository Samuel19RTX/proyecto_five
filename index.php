<?php
session_start();
require 'config/db.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener los productos disponibles con stock
$stmt = $pdo->query("SELECT * FROM productos WHERE stock > 0");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tienda Five - Condones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Animación para las tarjetas de productos */
        .card {
            transition: transform 0.3s ease-in-out;
            border-radius: 10px;
            overflow: hidden;
        }
        .card:hover {
            transform: scale(1.05);
        }

        /* Barra de búsqueda estilizada */
        .search-bar {
            width: 50%;
            margin: 20px auto;
        }

        /* Notificación visual de "Producto agregado" */
        .alert-fixed {
            position: fixed;
            top: 10px;
            right: 10px;
            display: none;
            z-index: 1000;
        }

        /* Diseño de botones */
        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
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
                <li class="nav-item"><a class="nav-link" href="admin_productos.php">⚙️ Administración</a></li>
                <li class="nav-item"><a class="nav-link btn btn-danger text-white" href="logout.php">🔒 Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Notificación de producto agregado -->
<div id="alerta" class="alert alert-success alert-fixed">✅ Producto agregado al carrito</div>

<!-- Sección de bienvenida -->
<header class="bg-primary text-white text-center py-4">
    <h1>Bienvenido a Tienda Five</h1>
    <p>Encuentra los mejores productos al mejor precio.</p>
</header>

<!-- Barra de búsqueda -->
<div class="container search-bar">
    <input type="text" id="search" class="form-control" placeholder="🔍 Buscar productos...">
</div>

<!-- Catálogo de Productos -->
<div class="container mt-4">
    <h2 class="text-center mb-4">Catálogo de Productos</h2>
    <div class="row" id="productList">
        <?php foreach ($productos as $producto): ?>
            <div class="col-md-4 product-item">
                <div class="card mb-4 shadow">
                    <img src="public/imagenes/<?php echo htmlspecialchars($producto['imagen']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($producto['nombre']); ?>" 
                         onerror="this.src='public/imagenes/default.jpg';">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <p class="card-text"><strong>$<?php echo number_format($producto['precio'], 2); ?></strong></p>
                        <p class="card-text"><strong>Stock: <?php echo $producto['stock']; ?></strong></p>
                        <?php if ($producto['stock'] > 0): ?>
                            <button class="btn btn-success add-to-cart" data-id="<?php echo $producto['id']; ?>">🛒 Agregar al Carrito</button>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>❌ Agotado</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Script para búsqueda en tiempo real -->
<script>
    document.getElementById("search").addEventListener("keyup", function() {
        let searchText = this.value.toLowerCase();
        let products = document.querySelectorAll(".product-item");

        products.forEach(product => {
            let name = product.querySelector(".card-title").textContent.toLowerCase();
            if (name.includes(searchText)) {
                product.style.display = "block";
            } else {
                product.style.display = "none";
            }
        });
    });
</script>

<!-- Script para agregar productos al carrito sin recargar la página -->
<script>
document.querySelectorAll(".add-to-cart").forEach(button => {
    button.addEventListener("click", function(event) {
        event.preventDefault();
        let productoId = this.getAttribute("data-id");

        fetch("cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "producto_id=" + productoId
        })
        .then(response => response.json())
        .then(data => {
            mostrarAlerta(); // Mostrar notificación
            actualizarCarrito(); // Actualizar cantidad en carrito
        })
        .catch(error => console.error("Error:", error));
    });
});

// Función para actualizar el número de productos en el carrito
function actualizarCarrito() {
    fetch("cart.php?ajax=1")
    .then(response => response.json())
    .then(data => {
        document.getElementById("cart-count").textContent = `(${data.productos.length})`;
    })
    .catch(error => console.error("Error:", error));
}

// Función para mostrar notificación de producto agregado
function mostrarAlerta() {
    let alerta = document.getElementById("alerta");
    alerta.style.display = "block";
    setTimeout(() => {
        alerta.style.display = "none";
    }, 2000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
