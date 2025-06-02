<?php
namespace Src;

use PDO;
use PDOException;

class Database
{
    private $connection;

    public function __construct()
    {
        // Cargar variables de Railway
        $dbHost = getenv('DB_HOST') ?: 'mysql.railway.internal';
        $dbPort = getenv('DB_PORT') ?: '3306';
        $dbName = getenv('DB_NAME') ?: 'railway';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: 'qfRrpVFfMdsORpNtIKuJtLwEFlEHbVEJ';
        
        $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
            ]);

            // Verificación adicional
            $this->connection->query("SELECT 1")->fetch();

        } catch (PDOException $e) {
            error_log("Error DB: " . $e->getMessage());
            throw new \RuntimeException(
                getenv('APP_ENV') === 'production'
                    ? "Error de conexión a la base de datos"
                    : $e->getMessage()
            );
        }
    }

    public function connect(): PDO
    {
        return $this->connection;
    }
}
?>