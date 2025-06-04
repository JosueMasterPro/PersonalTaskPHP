<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Src\Database;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/MailSender.php'; // ruta de correo

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
// Ruta select usuarios
$app->get('/api/usuarios', function (Request $request, Response $response) {
    try {
        $db = new Database();
        $conn = $db->connect();

        $stmt = $conn->query("call sp_ObtenerUsuarios();");
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
/*Insertar usuarios */
// Ruta POST /api/usuarios
$app->post('/api/signUp', function (Request $request, Response $response) {
    try {
        // Obtener y decodificar los datos JSON
        $data = json_decode($request->getBody(), true);

        // Validación de campos requeridos
        if (empty($data['usuario']) || empty($data['name']) || empty($data['secondName']) || empty($data['email']) || empty($data['password'])) {
            throw new InvalidArgumentException('Todos los campos (usuario, nombre, apellido, correo, password) son requeridos');
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

        // Generar token seguro para verificación
        $token = bin2hex(random_bytes(32));

        // Hash de la contraseña (nunca almacenar en texto plano)
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Consulta preparada usando el procedimiento almacenado con token
        $stmt = $conn->prepare("
            CALL sp_InsertarUsuario(:usuario, :nombre, :apellido, :correo, :password, :token);
        ");

        // Asignar valores con bindParam
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->bindParam(':nombre', $data['name']);
        $stmt->bindParam(':apellido', $data['secondName']);
        $stmt->bindParam(':correo', $data['email']);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':token', $token);

        // Ejecutar consulta
        $stmt->execute();

        // Aquí podrías enviar el correo de verificación usando el token
        $mailSender = new MailSender();
        $enviado = $mailSender->enviarVerificacion($data['email'], $data['name'], $token);

        // Respuesta exitosa (sin enviar el password hash)
        $responseData = [
            'success' => true,
            'message' => 'Usuario registrado correctamente. Revisa tu correo para confirmar la cuenta.',
            // El id nuevo puedes obtenerlo del SELECT en el SP, por ejemplo así:
            'token_verificacion' => $token,
            'user_data' => [
                'usuario' => $data['usuario'],
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
// Verificar Token
$app->get('/verificar', function (Request $req, Response $res) {
    $token = $req->getQueryParams()['token'] ?? '';
    
    if (!$token) {
        $res->getBody()->write("Token no proporcionado.");
        return $res->withStatus(400);
    }

    // Crear conexión dentro de la función
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("CALL sp_VerificarCuenta(:token)");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $filas = $stmt->fetchColumn();

    if ($filas == 1) {
        $res->getBody()->write("¡Cuenta verificada con éxito! Ya puedes iniciar sesión.");
    } else {
        $res->getBody()->write("Token inválido o cuenta ya verificada.");
    }
    return $res;
});

//Iniciar Sesion
// Ruta POST /api/login
$app->post('/api/login', function (Request $request, Response $response) {
    try {
        // Obtener y decodificar los datos JSON
        $data = json_decode($request->getBody(), true);

        // Validación de campos requeridos
        $requiredFields = ['usuario', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new InvalidArgumentException("El campo $field es requerido");
            }
        }

        // Conexión a la base de datos
        $db = new Database();
        $conn = $db->connect();

        // Consulta preparada usando el procedimiento almacenado
        $stmt = $conn->prepare("CALL sp_VerificarLogin(:usuario)");
        $stmt->bindParam(':usuario', $data['usuario']);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //Verificar usuarios
        if (!$usuario) {                     // ← no existe
            throw new RuntimeException('Usuario no existe');
        }        

        // Verificar si usuario está verificado
        if (isset($usuario['verificado']) && !$usuario['verificado']) {
            throw new RuntimeException('Usuario no verificado. Por favor verifica tu correo.');
        }

        // Verificar con password_verify
        if (!$usuario || !password_verify($data['password'], $usuario['password'])) {
            throw new RuntimeException('Contraseña incorrecta o usuario incorrecto');
        }
        
        // Ejecutar consulta
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Generar token de sesión (ejemplo básico)
        $tokenPayload = [
            'sub' => $usuario['id'],
            'usuario' => $usuario['usuario'],
            'iat' => time(),
            'exp' => time() + (60 * 60) // Expira en 1 hora
        ];
        $token = base64_encode(json_encode($tokenPayload));
        // Eliminar información sensible antes de responder
        unset($usuario['password']);

        // Respuesta exitosa
        $responseData = [
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'user' => $usuario
        ];

        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode($responseData));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(200);
            
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        
        $errorMessage = 'Error en la base de datos';
        if ($e->getCode() == '23000') {
            $errorMessage = 'Error de duplicado de usuario';
        }
        
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => $errorMessage,
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(500);
            
    } catch (InvalidArgumentException $e) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Datos inválidos',
            'message' => $e->getMessage()
        ]));
        
        return $response->withStatus(400);
        
    } catch (RuntimeException $e) {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Autenticación fallida',
            'message' => $e->getMessage()
        ]));
        
        return $response->withStatus(401);
    }
});

//Tabla de Tareas
// Ruta para tareas filtradas
$app->post('/api/tareas', function (Request $request, Response $response) {
    try {
        /************* 1. Datos del usuario autenticado *************/
        $data = json_decode((string)$request->getBody(),true);

        $usuario   = $data['usuario'];  
        $rol  = $data['rol']; 
        $tipo  = $data['tipo_task'];  

        /************* 2. Conectar DB y llamar al SP *************/
        $db   = new Database();
        $conn = $db->connect();

        $stmt = $conn->prepare("CALL sp_ObtenerTareasPorRol(:usuario, :rol, :tipo)");
        $stmt->bindParam(':usuario',   $usuario,  PDO::PARAM_STR);
        $stmt->bindParam(':rol', $rol, PDO::PARAM_SRT);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_SRT);

        $stmt->execute();

        $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /************* 3. Respuesta *************/
        $payload = json_encode($tareas);

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Cache-Control', 'no-store')
            ->withStatus(200);

    } catch (PDOException $e) {
        error_log('DB Error: ' . $e->getMessage());

        $response->getBody()->write(json_encode([
            'error'   => 'Database error',
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(500);
    }
});

//Editar tareas
$app->put('/api/tareas/{id}', function (Request $request, Response $response, array $args) {
    try {
        $id = (int) $args['id'];
        $data = json_decode($request->getBody(), true);

        // Validaciones básicas
        if (empty($data['titulo'])) {
            throw new InvalidArgumentException('El título es requerido.');
        }

        // Conexión DB
        $db = new Database();
        $conn = $db->connect();

        // Validar si la tarea existe
        $check = $conn->prepare("SELECT id_Tarea FROM tareas WHERE id_Tarea = :id");
        $check->bindParam(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->rowCount() === 0) {
            throw new InvalidArgumentException("La tarea con ID $id no existe.");
        }

        // Preparar actualización
        $stmt = $conn->prepare("
            UPDATE tareas
            SET titulo = :titulo,
                descripcion = :descripcion,
                completada = :completada,
                fecha_final = :fecha_final
            WHERE id_Tarea = :id
        ");

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':titulo', $data['titulo']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':completada', $data['completada'], PDO::PARAM_BOOL);
        $stmt->bindParam(':fecha_final', $data['fecha_final']);

        $stmt->execute();

        $response->getBody()->write(json_encode([
            'success' => true,
            'message' => 'Tarea actualizada exitosamente'
        ]));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);

    } catch (InvalidArgumentException $e) {
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Datos inválidos',
            'message' => $e->getMessage()
        ]));
        return $response->withStatus(400);
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'success' => false,
            'error' => 'Error de base de datos',
            'message' => getenv('APP_ENV') !== 'production' ? $e->getMessage() : null
        ]));
        return $response->withStatus(500);
    }
});





$app->map(['OPTIONS'], '/{routes:.+}', function ($request, $response) {
    return $response;
});
$app->run();
?>