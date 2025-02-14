<?php
session_start();
require 'config/db.php';

// Si el usuario ya está autenticado, redirigirlo a index.php
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Procesar formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nombre'] = $admin['nombre'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Correo o contraseña incorrectos.";
    }
}

// Procesar registro de nuevo administrador
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if (!empty($nombre) && !empty($email) && !empty($_POST['password'])) {
        $stmt = $pdo->prepare("INSERT INTO admin (nombre, email, password) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$nombre, $email, $password]);
            $success = "Administrador registrado con éxito. Ahora puedes iniciar sesión.";
        } catch (Exception $e) {
            $error = "El correo ya está registrado.";
        }
    } else {
        $error = "Todos los campos son obligatorios.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Tienda Five</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="login-container">
        <h2 class="text-center">🔑 Iniciar Sesión</h2>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100">Ingresar</button>
        </form>

        <hr>

        <h4 class="text-center">Registrar Administrador</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
            </div>
            <div class="mb-3">
                <label for="email_reg" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email_reg" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password_reg" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password_reg" name="password" required>
            </div>
            <button type="submit" name="register" class="btn btn-success w-100">Registrar</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
