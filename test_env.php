<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "<h2>Variables de entorno DB:</h2>";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'No definida') . "<br>";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'No definida') . "<br>";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'No definida') . "<br>";
echo "DB_PASS: " . (!empty($_ENV['DB_PASS']) ? '******' : 'No definida') . "<br>";
?>