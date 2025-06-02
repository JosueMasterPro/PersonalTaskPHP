<?php
require __DIR__ . '/vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];
    
    echo "Intentando conectar a: mysql:host=$host;dbname=$dbname<br>";
    echo "Usuario: $user<br>";
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión exitosa a la base de datos!<br>";
    
    // Prueba una consulta simple
    $stmt = $conn->query("SELECT 1");
    $result = $stmt->fetch();
    echo "Consulta de prueba ejecutada correctamente: ";
    print_r($result);
    
} catch (PDOException $e) {
    echo "❌ Error de conexión: " . $e->getMessage();
}?>