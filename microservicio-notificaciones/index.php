<?php
/**
 * Microservicio de Notificaciones
 * Sistema de Microservicios - Arquitecturas 2025
 * Puerto: 5003
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

// Crear instancias
$database = new Database();
$db = $database->getConnection();
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
        'service' => 'notificaciones',
        'port' => 5003
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

// Función para simular envío de notificación
function sendNotification($to, $subject, $message, $user_id) {
    global $db;
    
    // En un entorno real, aquí se integraría con servicios como:
    // - Email (SMTP, SendGrid, Mailgun)
    // - SMS (Twilio, AWS SNS)
    // - Push notifications (Firebase, OneSignal)
    // - Webhooks
    
    // Por ahora, simulamos el envío y guardamos en base de datos
    try {
        $stmt = $db->prepare("INSERT INTO notificaciones (usuario_id, destinatario, asunto, mensaje, estado, enviado_en) VALUES (?, ?, ?, ?, 'enviado', NOW())");
        $result = $stmt->execute([$user_id, $to, $subject, $message]);
        
        if ($result) {
            // Simular delay de envío
            usleep(100000); // 0.1 segundos
            
            return [
                'success' => true,
                'notification_id' => $db->lastInsertId(),
                'status' => 'enviado',
                'timestamp' => date('Y-m-d H:i:s')
            ];
        } else {
            return [
                'success' => false,
                'error' => 'Error al guardar notificación'
            ];
        }
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Función para obtener historial de notificaciones
function getNotificationHistory($user_id, $limit = 10) {
    global $db;
    
    error_log("Microservicio Notificaciones - getNotificationHistory: user_id = $user_id, limit = $limit");
    
    try {
        // MariaDB no permite parámetros preparados en LIMIT, usar concatenación segura
        $limit = (int)$limit; // Asegurar que sea entero
        $stmt = $db->prepare("SELECT id, destinatario, asunto, mensaje, estado, enviado_en 
                              FROM notificaciones 
                              WHERE usuario_id = ? 
                              ORDER BY enviado_en DESC 
                              LIMIT $limit");
        $stmt->execute([$user_id]);
        
        error_log("Microservicio Notificaciones - Query ejecutada, filas encontradas: " . $stmt->rowCount());
        
        $notifications = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'id' => $row['id'],
                'destinatario' => $row['destinatario'],
                'asunto' => $row['asunto'],
                'mensaje' => $row['mensaje'],
                'estado' => $row['estado'],
                'enviado_en' => $row['enviado_en']
            ];
        }
        
        error_log("Microservicio Notificaciones - Notificaciones procesadas: " . count($notifications));
        return $notifications;
    } catch (Exception $e) {
        error_log("Microservicio Notificaciones - Error en getNotificationHistory: " . $e->getMessage());
        return [];
    }
}

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: mostrar la ruta completa
error_log("Microservicio Notificaciones - Ruta completa: " . $path);

// Extraer solo la parte después de /microservicio-notificaciones/
$path = str_replace('/microservicios/microservicio-notificaciones/', '', $path);
$path = str_replace('/microservicio-notificaciones/', '', $path);
$path = str_replace('/microservicio-notificaciones', '', $path);

// Debug: mostrar la ruta procesada
error_log("Microservicio Notificaciones - Ruta procesada: " . $path);

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
    error_log("Microservicio Notificaciones - Verificando health check...");
    error_log("Microservicio Notificaciones - Path parts: " . print_r($path_parts, true));
    
    if (empty($path_parts) || $path_parts[0] === 'health' || $path_parts[0] === '') {
        error_log("Microservicio Notificaciones - Ejecutando health check");
        sendResponse([
            'success' => true,
            'service' => 'notificaciones',
            'status' => 'healthy',
            'port' => 5003,
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $db ? 'connected' : 'disconnected',
            'endpoints' => [
                'health' => '/health',
                'send_notification' => '/notify (POST)',
                'list_notifications' => '/notifications (GET)',
                'stats' => '/stats (GET)'
            ]
        ]);
    }
    
    // Ruta de notificaciones
    elseif ($path_parts[0] === 'notify') {
        switch ($method) {
            case 'POST':
                // POST /notify (enviar notificación)
                // Validar datos requeridos
                if(empty($input['to']) || empty($input['subject']) || empty($input['message'])) {
                    sendError('Destinatario, asunto y mensaje son requeridos', 400);
                }
                
                // Validar formato de email
                if(!filter_var($input['to'], FILTER_VALIDATE_EMAIL)) {
                    sendError('Formato de email inválido', 400);
                }
                
                // Obtener user_id del token
                $user_id = validateJWTAndGetUserId();
                
                // Enviar notificación
                $result = sendNotification(
                    $input['to'],
                    $input['subject'],
                    $input['message'],
                    $user_id
                );
                
                if ($result['success']) {
                    sendResponse([
                        'success' => true,
                        'message' => 'Notificación enviada exitosamente',
                        'notification_id' => $result['notification_id'],
                        'status' => $result['status'],
                        'timestamp' => $result['timestamp'],
                        'service' => 'notificaciones'
                    ], 201);
                } else {
                    sendError('Error al enviar notificación: ' . $result['error'], 500);
                }
                break;
                
            case 'GET':
                // GET /notify (historial de notificaciones)
                $user_id = validateJWTAndGetUserId();
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
                
                $notifications = getNotificationHistory($user_id, $limit);
                
                sendResponse([
                    'success' => true,
                    'notifications' => $notifications,
                    'total' => count($notifications),
                    'service' => 'notificaciones'
                ]);
                break;
                
            default:
                sendError('Método HTTP no permitido', 405);
        }
    }
    
    // Ruta de notificaciones específicas
    elseif ($path_parts[0] === 'notifications') {
        $user_id = validateJWTAndGetUserId();
        
        switch ($method) {
            case 'GET':
                if (isset($path_parts[1])) {
                    // GET /notifications/{id} (obtener notificación específica)
                    try {
                        $stmt = $db->prepare("SELECT id, destinatario, asunto, mensaje, estado, enviado_en 
                                              FROM notificaciones 
                                              WHERE id = ? AND usuario_id = ?");
                        $stmt->execute([$path_parts[1], $user_id]);
                        
                        if ($notification = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            sendResponse([
                                'success' => true,
                                'notification' => $notification,
                                'service' => 'notificaciones'
                            ]);
                        } else {
                            sendError('Notificación no encontrada', 404);
                        }
                    } catch (Exception $e) {
                        sendError('Error al obtener notificación', 500);
                    }
                } else {
                    // GET /notifications (listar todas las notificaciones del usuario)
                    $notifications = getNotificationHistory($user_id, 50);
                    
                    sendResponse([
                        'success' => true,
                        'notifications' => $notifications,
                        'total' => count($notifications),
                        'service' => 'notificaciones'
                    ]);
                }
                break;
                
            case 'DELETE':
                // DELETE /notifications/{id} (eliminar notificación)
                if (isset($path_parts[1])) {
                    try {
                        $stmt = $db->prepare("DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?");
                        $result = $stmt->execute([$path_parts[1], $user_id]);
                        
                        if ($result && $stmt->rowCount() > 0) {
                            sendResponse([
                                'success' => true,
                                'message' => 'Notificación eliminada exitosamente',
                                'service' => 'notificaciones'
                            ]);
                        } else {
                            sendError('Notificación no encontrada', 404);
                        }
                    } catch (Exception $e) {
                        sendError('Error al eliminar notificación', 500);
                    }
                } else {
                    sendError('ID de notificación requerido', 400);
                }
                break;
                
            default:
                sendError('Método HTTP no permitido', 405);
        }
    }
    
    // Ruta de estadísticas de notificaciones
    elseif ($path_parts[0] === 'stats') {
        $user_id = validateJWTAndGetUserId();
        
        try {
            $stmt = $db->prepare("SELECT 
                COUNT(*) as total_notifications,
                COUNT(CASE WHEN estado = 'enviado' THEN 1 END) as notifications_enviadas,
                COUNT(CASE WHEN estado = 'pendiente' THEN 1 END) as notifications_pendientes,
                COUNT(CASE WHEN estado = 'error' THEN 1 END) as notifications_error
                FROM notificaciones WHERE usuario_id = ?");
            $stmt->execute([$user_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendResponse([
                'success' => true,
                'stats' => $stats,
                'service' => 'notificaciones'
            ]);
        } catch (Exception $e) {
            sendError('Error al obtener estadísticas', 500);
        }
    }
    
    // Ruta no encontrada
    else {
        sendError('Endpoint no encontrado en el microservicio de notificaciones', 404);
    }
    
} catch (Exception $e) {
    sendError('Error interno del microservicio: ' . $e->getMessage(), 500);
}
?>
