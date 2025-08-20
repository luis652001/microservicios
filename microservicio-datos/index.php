<?php
/**
 * Microservicio de Datos
 * Sistema de Microservicios - Arquitecturas 2025
 * Puerto: 5002
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
require_once 'models/Item.php';

// Crear instancias
$database = new Database();
$db = $database->getConnection();
$item = new Item($db);
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
        'service' => 'datos',
        'port' => 5002
    ], $status_code);
}

// Función para validar JWT y obtener user_id
function validateJWTAndGetUserId() {
    global $jwt;
    
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
    
    return $payload['user_id'];
}

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: mostrar la ruta completa
error_log("Microservicio Datos - Ruta completa: " . $path);

// Extraer solo la parte después de /microservicio-datos/
$path = str_replace('/microservicios/microservicio-datos/', '', $path);
$path = str_replace('/microservicio-datos/', '', $path);
$path = str_replace('/microservicio-datos', '', $path);

// Debug: mostrar la ruta procesada
error_log("Microservicio Datos - Ruta procesada: " . $path);

$path_parts = explode('/', trim($path, '/'));

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
    error_log("Microservicio Datos - Verificando health check...");
    error_log("Microservicio Datos - Path parts: " . print_r($path_parts, true));
    
    if (empty($path_parts) || $path_parts[0] === 'health' || $path_parts[0] === '') {
        error_log("Microservicio Datos - Ejecutando health check");
        sendResponse([
            'success' => true,
            'service' => 'datos',
            'status' => 'healthy',
            'port' => 5002,
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $db ? 'connected' : 'disconnected',
            'endpoints' => [
                'health' => '/health',
                'items' => '/items',
                'create_item' => '/items (POST)',
                'list_items' => '/items (GET)'
            ]
        ]);
    }
    
    // Ruta de items
    elseif ($path_parts[0] === 'items') {
        // Todas las operaciones de items requieren autenticación
        $user_id = validateJWTAndGetUserId();
        
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    // GET /items/{id}
                    $item->id = $path_parts[1];
                    if($item->readOne()) {
                        // Verificar que el item pertenezca al usuario
                        if($item->belongsToUser($user_id)) {
                            sendResponse([
                                'success' => true,
                                'item' => [
                                    'id' => $item->id,
                                    'nombre' => $item->nombre,
                                    'descripcion' => $item->descripcion,
                                    'estado' => $item->estado,
                                    'usuario_id' => $item->usuario_id,
                                    'creado_en' => $item->creado_en
                                ],
                                'service' => 'datos'
                            ]);
                        } else {
                            sendError('No tienes permisos para ver este item', 403);
                        }
                    } else {
                        sendError('Item no encontrado', 404);
                    }
                } else {
                    // GET /items (listar todos los items del usuario)
                    $stmt = $item->readByUser($user_id);
                    $items = [];
                    
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $items[] = [
                            'id' => $row['id'],
                            'nombre' => $row['nombre'],
                            'descripcion' => $row['descripcion'],
                            'estado' => $row['estado'],
                            'creado_en' => $row['creado_en']
                        ];
                    }
                    
                    sendResponse([
                        'success' => true,
                        'items' => $items,
                        'total' => count($items),
                        'service' => 'datos'
                    ]);
                }
                break;
                
            case 'POST':
                // POST /items (crear nuevo item)
                // Validar datos requeridos
                if(empty($input['nombre']) || empty($input['descripcion'])) {
                    sendError('Nombre y descripción son requeridos', 400);
                }
                
                // Crear item
                $item->nombre = $input['nombre'];
                $item->descripcion = $input['descripcion'];
                $item->estado = isset($input['estado']) ? $input['estado'] : 'activo';
                $item->usuario_id = $user_id;
                
                if($item->create()) {
                    sendResponse([
                        'success' => true,
                        'message' => 'Item creado exitosamente',
                        'service' => 'datos'
                    ], 201);
                } else {
                    sendError('Error al crear item', 500);
                }
                break;
                
            case 'PUT':
                // PUT /items/{id} (actualizar item)
                if (isset($path_parts[1])) {
                    // Validar datos requeridos
                    if(empty($input['nombre']) || empty($input['descripcion'])) {
                        sendError('Nombre y descripción son requeridos', 400);
                    }
                    
                    // Verificar que el item pertenezca al usuario
                    $item->id = $path_parts[1];
                    if(!$item->belongsToUser($user_id)) {
                        sendError('No tienes permisos para modificar este item', 403);
                    }
                    
                    // Actualizar item
                    $item->nombre = $input['nombre'];
                    $item->descripcion = $input['descripcion'];
                    $item->estado = isset($input['estado']) ? $input['estado'] : 'activo';
                    $item->usuario_id = $user_id;
                    
                    if($item->update()) {
                        sendResponse([
                            'success' => true,
                            'message' => 'Item actualizado exitosamente',
                            'service' => 'datos'
                        ]);
                    } else {
                        sendError('Error al actualizar item', 500);
                    }
                } else {
                    sendError('ID de item requerido', 400);
                }
                break;
                
            case 'DELETE':
                // DELETE /items/{id} (eliminar item)
                if (isset($path_parts[1])) {
                    // Verificar que el item pertenezca al usuario
                    $item->id = $path_parts[1];
                    if(!$item->belongsToUser($user_id)) {
                        sendError('No tienes permisos para eliminar este item', 403);
                    }
                    
                    if($item->delete()) {
                        sendResponse([
                            'success' => true,
                            'message' => 'Item eliminado exitosamente',
                            'service' => 'datos'
                        ]);
                    } else {
                        sendError('Error al eliminar item', 500);
                    }
                } else {
                    sendError('ID de item requerido', 400);
                }
                break;
                
            default:
                sendError('Método HTTP no permitido', 405);
        }
    }
    
    // Ruta de estado de items
    elseif ($path_parts[0] === 'status' && isset($path_parts[1]) && isset($path_parts[2])) {
        // Cambiar estado de item
        $user_id = validateJWTAndGetUserId();
        $item_id = $path_parts[1];
        $new_status = $path_parts[2];
        
        // Validar estado
        if(!in_array($new_status, ['activo', 'inactivo'])) {
            sendError('Estado inválido', 400);
        }
        
        // Verificar que el item pertenezca al usuario
        $item->id = $item_id;
        if(!$item->belongsToUser($user_id)) {
            sendError('No tienes permisos para modificar este item', 403);
        }
        
        // Actualizar estado
        $item->estado = $new_status;
        $item->usuario_id = $user_id;
        
        if($item->update()) {
            sendResponse([
                'success' => true,
                'message' => 'Estado del item actualizado exitosamente',
                'service' => 'datos'
            ]);
        } else {
            sendError('Error al actualizar estado del item', 500);
        }
    }
    
    // Ruta de estadísticas (opcional)
    elseif ($path_parts[0] === 'stats') {
        $user_id = validateJWTAndGetUserId();
        
        // Obtener estadísticas básicas del usuario
        $stmt = $db->prepare("SELECT 
            COUNT(*) as total_items,
            COUNT(CASE WHEN estado = 'activo' THEN 1 END) as items_activos,
            COUNT(CASE WHEN estado = 'inactivo' THEN 1 END) as items_inactivos
            FROM items WHERE usuario_id = ?");
        $stmt->execute([$user_id]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        sendResponse([
            'success' => true,
            'stats' => $stats,
            'service' => 'datos'
        ]);
    }
    
    // Ruta no encontrada
    else {
        sendError('Endpoint no encontrado en el microservicio de datos', 404);
    }
    
} catch (Exception $e) {
    sendError('Error interno del microservicio: ' . $e->getMessage(), 500);
}
?>
