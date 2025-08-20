<?php
/**
 * Endpoint para Listar Notificaciones de un Usuario
 * Microservicio Notificaciones - Sistema de Microservicios
 */

// Configuración de CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

try {
    // Incluir archivos necesarios
    require_once '../config/database.php';
    require_once '../models/Notification.php';

    // Crear instancias
    $database = new Database();
    $db = $database->getConnection();
    $notificationModel = new Notification($db);

    // Obtener user_id del parámetro GET
    $user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'user_id es requerido'
        ]);
        exit();
    }

    // Obtener notificaciones del usuario
    $notifications = $notificationModel->getNotificationsByUserId($user_id);

    if ($notifications !== false) {
        echo json_encode([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'total' => count($notifications),
                'user_id' => $user_id
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener notificaciones del usuario'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>


