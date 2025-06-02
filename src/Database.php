<?php
namespace Src;

use PDO;
use PDOException;

class Database
{
    private $connection;

    public function __construct()
    {
        // Estas variables las proporciona automáticamente Railway
        // cuando añades un servicio de MySQL a tu proyecto
        $dbHost = getenv('MYSQLHOST');
        $dbPort = getenv('MYSQLPORT');
        $dbName = getenv('MYSQLDATABASE');
        $dbUser = getenv('MYSQLUSER');
        $dbPass = getenv('MYSQLPASSWORD');
        $dbUrl = getenv('MYSQL_URL'); // Alternativa para algunos entornos

        // Intenta conectar usando la URL primero, luego los parámetros individuales
        $dsn = $dbUrl ? $dbUrl : "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            // Verificación adicional de conexión
            $this->connection->query("SELECT 1");

        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            throw new \RuntimeException("Error al conectar con la base de datos: " . $e->getMessage());
        }
    }

    public function connect()
    {
        return $this->connection;
    }
}
?>