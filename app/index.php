<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Src\Database;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Middleware CORS (debe ser el PRIMER middleware)
$app->add(function ($request, $handler) {
    // Respuesta para preflight OPTIONS
    if ($request->getMethod() === 'OPTIONS') {
        $response = new \Slim\Psr7\Response();
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
            ->withHeader('Access-Control-Max-Age', '86400');
    }

    // Procesamiento normal de otras solicitudes
    $response = $handler->handle($request);
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With')
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

        // Crear nueva respuesta para asegurar compatibilidad
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($users));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(200);
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'error' => 'Database error',
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

// Ruta GET /api/tareas
$app->get('/api/tareas', function (Request $request, Response $response) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->query("SELECT * FROM Tareas");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Crear nueva respuesta para asegurar compatibilidad
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($users));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(200);
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'error' => 'Database error',
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

$app->get('/db-check', function (Request $request, Response $response) use ($app) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $result = $conn->query("
            SELECT 
                DATABASE() as db_name,
                USER() as db_user,
                @@hostname as db_host,
                VERSION() as db_version
        ")->fetch();

        // Crear respuesta JSON manualmente
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'connection' => $result,
            'env_vars' => [
                'DB_HOST' => getenv('DB_HOST'),
                'DB_PORT' => getenv('DB_PORT'),
                'DB_NAME' => getenv('DB_NAME')
            ]
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
            
    } catch (\Exception $e) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $e->getMessage(),
            'trace' => getenv('APP_ENV') !== 'production' ? $e->getTrace() : null
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});
// Catch-all route para OPTIONS (debe ir AL FINAL)
$app->map(['OPTIONS'], '/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->run();
?>