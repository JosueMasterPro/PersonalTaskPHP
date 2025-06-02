
// Agrega esto al inicio del archivo para restringir acceso
if ($_SERVER['REMOTE_ADDR'] !== 'TU_IP_PERSONAL' && $_ENV['APP_ENV'] === 'production') {
    die('Acceso no autorizado');
}
<?php
require __DIR__ . '/../vendor/autoload.php';
use Src\Database;

header('Content-Type: text/plain');

// Restricción de acceso en producción
if (getenv('APP_ENV') === 'production' && !in_array($_SERVER['REMOTE_ADDR'], ['TU_IP', '127.0.0.1'])) {
    die("Acceso restringido\n");
}

try {
    echo "=== Prueba de conexión a BD ===\n\n";
    
    // Mostrar variables usadas (sin password)
    echo "Configuración usada:\n";
    echo "Host: " . ($_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST')) . "\n";
    echo "Port: " . ($_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT')) . "\n";
    echo "DB: " . ($_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE')) . "\n";
    echo "User: " . ($_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER')) . "\n\n";
    
    $db = new Database();
    $conn = $db->connect();
    
    echo "✅ Conexión exitosa a la base de datos!\n";
    echo "Versión MySQL: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
    
    // Pruebas adicionales
    $tests = [
        "SHOW TABLES" => "Listado de tablas",
        "SELECT COUNT(*) AS total_tables FROM information_schema.tables WHERE table_schema = DATABASE()" => "Conteo de tablas",
        "SHOW VARIABLES LIKE 'max_connections%'" => "Conexiones máximas"
    ];
    
    foreach ($tests as $sql => $desc) {
        echo "--- $desc ---\n";
        $result = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        print_r($result);
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    error_log("Error DB Test: " . $e->getMessage());
    
    // Diagnóstico avanzado
    echo "\n=== Diagnóstico ===\n";
    echo "Variables ENV disponibles:\n";
    echo "MYSQLHOST: " . (getenv('MYSQLHOST') ? 'set' : 'not set') . "\n";
    echo "MYSQLPORT: " . (getenv('MYSQLPORT') ? 'set' : 'not set') . "\n";
    echo "MYSQLDATABASE: " . (getenv('MYSQLDATABASE') ? 'set' : 'not set') . "\n";
    echo "MYSQLUSER: " . (getenv('MYSQLUSER') ? 'set' : 'not set') . "\n";
}
?>