<?php
/**
 * Microservicio de Usuarios
 * Sistema de Microservicios - Arquitecturas 2025
 * Puerto: 5001
 */

// Configuración de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
require_once 'config/database.php';
require_once 'config/jwt.php';
require_once 'models/User.php';

// Crear instancias
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$jwt = new JWTConfig();

// Función para enviar respuesta JSON
function sendResponse($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Función para manejar errores
function sendError($message, $status_code = 400) {
    sendResponse([
        'success' => false,
        'message' => $message,
        'service' => 'usuarios',
        'port' => 5001
    ], $status_code);
}

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: mostrar la ruta completa
error_log("Microservicio Usuarios - Ruta completa: " . $path);

// Extraer solo la parte después de /microservicio-usuarios/
$path = str_replace('/microservicios/microservicio-usuarios/', '', $path);
$path = str_replace('/microservicio-usuarios/', '', $path);
$path = str_replace('/microservicio-usuarios', '', $path);

// Debug: mostrar la ruta procesada
error_log("Microservicio Usuarios - Ruta procesada: " . $path);
error_log("Microservicio Usuarios - Ruta raw: '" . $_SERVER['REQUEST_URI'] . "'");

$path_parts = explode('/', trim($path, '/'));
error_log("Microservicio Usuarios - Path parts: " . print_r($path_parts, true));
error_log("Microservicio Usuarios - Path parts count: " . count($path_parts));
error_log("Microservicio Usuarios - Path parts empty: " . (empty($path_parts) ? 'true' : 'false'));

// Obtener datos del body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Enrutamiento del microservicio
try {
    // Verificar conexión a base de datos
    if (!$db) {
        sendError('Error de conexión a base de datos', 500);
    }
    
    // Ruta de health check (raíz del microservicio)
    error_log("Microservicio Usuarios - Verificando health check...");
    error_log("Microservicio Usuarios - Path parts: " . print_r($path_parts, true));
    
    if (empty($path_parts) || $path_parts[0] === 'health' || $path_parts[0] === '') {
        error_log("Microservicio Usuarios - Ejecutando health check");
        sendResponse([
            'success' => true,
            'service' => 'usuarios',
            'status' => 'healthy',
            'port' => 5001,
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $db ? 'connected' : 'testing',
            'endpoints' => [
                'health' => '/health',
                'login' => '/auth/login',
                'register' => '/auth/register',
                'profile' => '/auth/profile'
            ]
        ]);
    }
    
    // Ruta de autenticación
    elseif ($path_parts[0] === 'auth') {
        // Debug: mostrar información de la ruta
        error_log("Microservicio Usuarios - Auth endpoint: " . ($path_parts[1] ?? 'VACÍO'));
        error_log("Microservicio Usuarios - Método HTTP: " . $method);
        
        switch ($method) {
            case 'POST':
                if ($path_parts[1] === 'register') {
                    // Validar datos requeridos
                    if(empty($input['nombre']) || empty($input['correo']) || empty($input['password'])) {
                        sendError('Todos los campos son requeridos', 400);
                    }

                    // Validar formato de email
                    if(!filter_var($input['correo'], FILTER_VALIDATE_EMAIL)) {
                        sendError('Formato de email inválido', 400);
                    }

                    // Verificar si el email ya existe
                    $user->correo = $input['correo'];
                    if($user->emailExists()) {
                        sendError('El email ya está registrado', 400);
                    }

                    // Hash de la contraseña
                    $password_hash = password_hash($input['password'], PASSWORD_DEFAULT);

                    // Crear usuario
                    $user->nombre = $input['nombre'];
                    $user->correo = $input['correo'];
                    $user->password_hash = $password_hash;

                    if($user->create()) {
                        sendResponse([
                            'success' => true,
                            'message' => 'Usuario registrado exitosamente',
                            'service' => 'usuarios'
                        ], 201);
                    } else {
                        sendError('Error al registrar usuario', 500);
                    }
                } 
                elseif ($path_parts[1] === 'login') {
                    // Validar datos requeridos
                    if(empty($input['correo']) || empty($input['password'])) {
                        sendError('Email y contraseña son requeridos', 400);
                    }

                    // Verificar si el usuario existe
                    $user->correo = $input['correo'];
                    if(!$user->emailExists()) {
                        sendError('Credenciales inválidas', 401);
                    }

                    // Verificar contraseña
                    if(!password_verify($input['password'], $user->password_hash)) {
                        sendError('Credenciales inválidas', 401);
                    }

                    // Generar token JWT
                    $payload = [
                        'user_id' => $user->id,
                        'nombre' => $user->nombre,
                        'correo' => $user->correo,
                        'service' => 'usuarios'
                    ];

                    $token = $jwt->generateToken($payload);

                    sendResponse([
                        'success' => true,
                        'message' => 'Login exitoso',
                        'token' => $token,
                        'user' => [
                            'id' => $user->id,
                            'nombre' => $user->nombre,
                            'correo' => $user->correo
                        ],
                        'service' => 'usuarios'
                    ]);
                }
                else {
                    sendError('Endpoint de autenticación no válido', 404);
                }
                break;
                
            case 'GET':
                if ($path_parts[1] === 'profile') {
                    // Obtener token del header
                    $headers = getallheaders();
                    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
                    
                    if(empty($auth_header) || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                        sendError('Token de autenticación requerido', 401);
                    }
                    
                    $token = $matches[1];
                    $payload = $jwt->validateToken($token);
                    
                    if(!$payload) {
                        sendError('Token inválido o expirado', 401);
                    }
                    
                    // Obtener perfil del usuario
                    $user->id = $payload['user_id'];
                    if($user->readOne()) {
                        sendResponse([
                            'success' => true,
                            'user' => [
                                'id' => $user->id,
                                'nombre' => $user->nombre,
                                'correo' => $user->correo,
                                'creado_en' => $user->creado_en
                            ],
                            'service' => 'usuarios'
                        ]);
                    } else {
                        sendError('Usuario no encontrado', 404);
                    }
                }
                else {
                    sendError('Endpoint de perfil no válido', 404);
                }
                break;
                
            default:
                sendError('Método HTTP no permitido', 405);
        }
    }
    
    // Ruta de usuarios
    elseif ($path_parts[0] === 'usuarios') {
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    // GET /usuarios/{id}
                    $user->id = $path_parts[1];
                    if($user->readOne()) {
                        sendResponse([
                            'success' => true,
                            'user' => [
                                'id' => $user->id,
                                'nombre' => $user->nombre,
                                'correo' => $user->correo,
                                'creado_en' => $user->creado_en
                            ],
                            'service' => 'usuarios'
                        ]);
                    } else {
                        sendError('Usuario no encontrado', 404);
                    }
                } else {
                    // GET /usuarios (listar todos - solo para administradores)
                    // En un entorno real, aquí se verificarían permisos
                    sendResponse([
                        'success' => true,
                        'message' => 'Lista de usuarios (funcionalidad de administrador)',
                        'service' => 'usuarios'
                    ]);
                }
                break;
                
            case 'PUT':
                // PUT /usuarios/{id}
                if (isset($path_parts[1])) {
                    // Verificar autenticación
                    $headers = getallheaders();
                    $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';
                    
                    if(empty($auth_header) || !preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                        sendError('Token de autenticación requerido', 401);
                    }
                    
                    $token = $matches[1];
                    $payload = $jwt->validateToken($token);
                    
                    if(!$payload) {
                        sendError('Token inválido o expirado', 401);
                    }
                    
                    // Verificar que el usuario solo pueda editar su propio perfil
                    if($payload['user_id'] != $path_parts[1]) {
                        sendError('No tienes permisos para editar este usuario', 403);
                    }
                    
                    // Validar datos
                    if(empty($input['nombre']) || empty($input['correo'])) {
                        sendError('Nombre y correo son requeridos', 400);
                    }
                    
                    // Actualizar usuario
                    $user->id = $path_parts[1];
                    $user->nombre = $input['nombre'];
                    $user->correo = $input['correo'];
                    
                    if($user->update()) {
                        sendResponse([
                            'success' => true,
                            'message' => 'Usuario actualizado exitosamente',
                            'service' => 'usuarios'
                        ]);
                    } else {
                        sendError('Error al actualizar usuario', 500);
                    }
                } else {
                    sendError('ID de usuario requerido', 400);
                }
                break;
                
            default:
                sendError('Método HTTP no permitido', 405);
        }
    }
    
    // Ruta no encontrada
    else {
        sendError('Endpoint no encontrado en el microservicio de usuarios', 404);
    }
    
} catch (Exception $e) {
    sendError('Error interno del microservicio: ' . $e->getMessage(), 500);
}
?>
