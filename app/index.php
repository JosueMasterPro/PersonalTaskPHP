<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Src\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000') // o 'http://localhost:3000' para más seguridad
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});



$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("¡Hola desde Slim en railway!..");
    return $response;
});

/*Solicitar usuarios a la BD*/
$app->get('/api/usuarios', function (Request $request, Response $response, $args) {
    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT * FROM usuarios";
    $stmt = $conn->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $payload = json_encode($users);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});
/*Fin Solicitar usuarios a la BD*/
/* Solicitar las tareas de la BD */
// Ruta para obtener todas las tareas
$app->get('/api/tareas', function (Request $request, Response $response, $args) {
    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT id, usuario_id, descripcion, estado FROM tareas";
    $stmt = $conn->query($sql);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $payload = json_encode($tareas);

    $response->getBody()->write($payload);
    return $response->withHeader('Content-Type', 'application/json');
});
/* Fin Solicitar las tareas de la BD */

$app->add(function ($request, $handler) {
    $method = $request->getMethod();
    $uri = $request->getUri();
    error_log("Solicitud: {$method} {$uri}");

    return $handler->handle($request);
});


$app->run();
