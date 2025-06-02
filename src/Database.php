<?php
namespace Src;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private $connection;

    public function __construct()
    {
        // Cargar variables de entorno (solo si no están ya cargadas)
        if (!isset($_ENV['MYSQLHOST']) && file_exists(__DIR__.'/../../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../');
            $dotenv->load();
        }

        // Obtener variables de entorno con valores por defecto seguros
        $dbHost = $_ENV['MYSQLHOST'] ?? getenv('MYSQLHOST');
        $dbPort = $_ENV['MYSQLPORT'] ?? getenv('MYSQLPORT') ?? '3306';
        $dbName = $_ENV['MYSQLDATABASE'] ?? getenv('MYSQLDATABASE');
        $dbUser = $_ENV['MYSQLUSER'] ?? getenv('MYSQLUSER');
        $dbPass = $_ENV['MYSQLPASSWORD'] ?? getenv('MYSQLPASSWORD');
        $dbUrl = $_ENV['MYSQL_URL'] ?? getenv('MYSQL_URL');

        // Validación de parámetros esenciales
        if (empty($dbUrl) && (empty($dbHost) || empty($dbName))) {
            throw new \RuntimeException("Configuración de base de datos incompleta");
        }

        // Construcción del DSN
        $dsn = $dbUrl ? $this->parseDbUrl($dbUrl) : "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Mejor rendimiento para producción
            ]);

            // Verificación de conexión más robusta
            $this->connection->query("SELECT 1")->fetch();

        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            
            // Mensaje seguro para producción
            $errorMessage = (getenv('APP_ENV') === 'production') 
                ? "Error al conectar con la base de datos" 
                : $e->getMessage();
                
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * Parsea la URL de conexión para extraer componentes
     */
    private function parseDbUrl(string $url): string
    {
        $parsed = parse_url($url);
        
        if ($parsed === false) {
            throw new \RuntimeException("URL de base de datos inválida");
        }

        $dsn = "mysql:host={$parsed['host']}";
        $dsn .= isset($parsed['port']) ? ";port={$parsed['port']}" : "";
        $dsn .= ";dbname=" . ltrim($parsed['path'] ?? '', '/');
        $dsn .= ";charset=utf8mb4";

        return $dsn;
    }

    public function connect(): PDO
    {
        return $this->connection;
    }
}
?>