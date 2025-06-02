<?php
require __DIR__ . '/../vendor/autoload.php';
use Src\Database;

header('Content-Type: text/plain');

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "✅ Conexión exitosa a la base de datos!\n";
    echo "Versión MySQL: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
    
    // Listar tablas
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Tablas disponibles:\n";
    print_r($tables);
    
    // Ejemplo de consulta a una tabla (si existe)
    if (!empty($tables)) {
        $firstTable = $tables[0];
        $data = $conn->query("SELECT * FROM {$firstTable} LIMIT 1")->fetch();
        echo "\nPrimer registro de {$firstTable}:\n";
        print_r($data);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Variables usadas:\n";
    echo "Host: " . getenv('MYSQLHOST') . "\n";
    echo "Port: " . getenv('MYSQLPORT') . "\n";
    echo "DB: " . getenv('MYSQLDATABASE') . "\n";
    echo "User: " . getenv('MYSQLUSER') . "\n";
    echo "Pass: " . (getenv('MYSQLPASSWORD') ? '******' : 'null') . "\n";
}