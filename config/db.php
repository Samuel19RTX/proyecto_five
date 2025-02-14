<?php
// URL de la base de datos proporcionada por Railway
$databaseUrl = "postgresql://postgres:HMNKqfYUcejmMHlNQhpVhrAYbEnBhCvt@autorack.proxy.rlwy.net:19720/railway";

try {
    // Establece la conexión usando la URL
    $pdo = new PDO("pgsql:" . $databaseUrl);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Conexión exitosa a la base de datos";

} catch (PDOException $e) {
    // Si hay un error en la conexión
    die("Error de conexión: " . $e->getMessage());
}
?>
