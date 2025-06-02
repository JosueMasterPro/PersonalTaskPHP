<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Src\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Middleware para manejar solicitudes OPTIONS (Preflight CORS)
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

// Middleware CORS global
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Vary', 'Origin');
});

/* RUTAS */

// Ruta base
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("¡Hola desde Slim en Railway!");
    return $response;
});

// Obtener usuarios
$app->get('/api/usuarios', function (Request $request, Response $response, $args) {
    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM usuarios";
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(print_r($users, true)); // log

    $payload = json_encode($users);
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Cache-Control', 'no-store');
});

// Obtener tareas
$app->get('/api/tareas', function (Request $request, Response $response, $args) {
    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT id, usuario_id, descripcion, estado FROM tareas";
    $stmt = $conn->query($sql);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log(print_r($tareas, true)); 

    $payload = json_encode($tareas);
    $response->getBody()->write($payload);
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withHeader('Cache-Control', 'no-store');
});

$app->run();
?>