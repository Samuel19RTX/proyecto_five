<?php
$host = "autorack.proxy.rlwy.net";  // Host de la base de datos
$port = "19720";  // Puerto de la base de datos
$dbname = "railway";  // Nombre de la base de datos
$user = "postgres";  // Usuario de la base de datos
$password = "HMNKqfYUcejmMHlNQhpVhrAYbEnBhCvt";  // Contraseña de la base de datos

try {
    // Conectar a la base de datos
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    // Establecer el modo de error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión exitosa a la base de datos";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>

