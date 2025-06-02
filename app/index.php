<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Src\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Middleware CORS global (debe ir PRIMERO)
$app->add(function ($request, $handler) {
    // Preflight OPTIONS request
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }

    // Normal requests
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Vary', 'Origin');
});

/* RUTAS */

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("¡Hola desde Slim en Railway!");
    return $response;
});

// Ruta GET /api/usuarios
$app->get('/api/usuarios', function (Request $request, Response $response) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->query("SELECT * FROM usuarios");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($users));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store');
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Database error']));
        return $response->withStatus(500);
    }
});

// Ruta GET /api/tareas
$app->get('/api/tareas', function (Request $request, Response $response) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->query("SELECT id, usuario_id, descripcion, estado FROM tareas");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($tasks));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store');
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => 'Database error']));
        return $response->withStatus(500);
    }
});

// Catch-all route para OPTIONS (debe ir AL FINAL)
$app->map(['OPTIONS'], '/{routes:.+}', function ($request, $response) {
    return $response;
});


$app->get('/debug-routes', function ($request, $response) {
    $routes = [];
    foreach ($this->get('routes') as $route) {
        $routes[] = [
            'pattern' => $route->getPattern(),
            'methods' => $route->getMethods()
        ];
    }
    return $response->withJson($routes);
});
$app->run();
?>