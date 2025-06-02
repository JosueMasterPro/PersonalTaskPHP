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
//CRUD /api/usuarios
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
//test de ruta post /api/usuarios(funciona)
/*$app->post('/api/usuarios', function (Request $request, Response $response) {
    $data = json_decode($request->getBody(), true);
    
    return $response
    ->withHeader('Content-Type', 'application/json')
    ->withHeader('Cache-Control', 'no-store')
    ->withStatus(200);
    // Respuesta temporal para debug
    return $response->withJson([
        'debug' => true,
        'datos_recibidos' => $data,
        'validacion' => [
            'usuario' => !empty($data['usuario']),
            'nombre' => !empty($data['name']),
            'apellido' => !empty($data['secondName']),
            'correo' => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL),
            'password' => !empty($data['password']) 
        ]
    ]);
});*/

// Ruta POST /api/usuarios
$app->post('/api/usuarios', function (Request $request, Response $response) {
    try {
        // Obtener y decodificar los datos JSON
        $data = json_decode($request->getBody(), true);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(200);
        return $response->withJson([
            'debug' => true,
            'datos_recibidos' => $data,
            'validacion' => [
                'usuario' => !empty($data['usuario']),
                'nombre' => !empty($data['name']),
                'apellido' => !empty($data['secondName']),
                'correo' => filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL),
                'password' => !empty($data['password']) 
            ]
        ]);
            // Validación de campos requeridos
        if (empty($data['usuario']) ||empty($data['name']) || empty($data['secondName']) || empty($data['correo']) || empty($data['password'])) {
            throw new InvalidArgumentException('Todos los campos (nombre, apellido, correo, password) son requeridos');
        }

        // Validación básica de email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El formato del correo electrónico no es válido');
        }

        // Validación de fortaleza de contraseña (opcional)
        if (strlen($data['password']) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres');
        }

        // Conexión a la base de datos
        $db = new Database();
        $conn = $db->connect();

        // Hash de la contraseña (nunca almacenar en texto plano)
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Consulta preparada para seguridad
        $stmt = $conn->prepare("
            INSERT INTO usuarios (usuario,nombre, apellido, correo, password)
            VALUES (:usuario,:nombre, :apellido, :correo, :password)
        ");

        // Asignar valores con bindParam
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':nombre', $data['name']);
        $stmt->bindParam(':apellido', $data['secondName']);
        $stmt->bindParam(':correo', $data['email']);
        $stmt->bindParam(':password', $passwordHash);

        // Ejecutar consulta
        $stmt->execute();

        // Respuesta exitosa (sin enviar el password hash)
        $responseData = [
            'success' => true,
            'message' => 'Usuario registrado correctamente',
            'user_id' => $conn->lastInsertId(),
            'user_data' => [
                'nombre' => $data['name'],
                'apellido' => $data['secondName'],
                'correo' => $data['email']
            ]
        ];

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($responseData));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(201);
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        
        // Manejo especial para errores de duplicado de correo
        if ($e->getCode() == 23000) { // Código para violación de clave única
            $errorMessage = 'El correo electrónico ya está registrado';
        } else {
            $errorMessage = 'Error en la base de datos';
        }
        
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $errorMessage,
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        
        return $response->withStatus(500);
            
    } catch (InvalidArgumentException $e) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Datos inválidos',
            'message' => $e->getMessage()
        ]));
        
        return $response->withStatus(400);
    }
});

// Ruta GET /api/tareas
$app->get('/api/tareas', function (Request $request, Response $response) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->query("SELECT * FROM tareas");
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

/* url test
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
});*/
// Catch-all route para OPTIONS (debe ir AL FINAL)
$app->map(['OPTIONS'], '/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->run();
?>