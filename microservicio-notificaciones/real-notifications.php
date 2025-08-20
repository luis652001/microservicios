<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
require_once 'config/database.php';
require_once 'models/Notification.php';

// Crear instancias
$database = new Database();
$db = $database->getConnection();
$notification = new Notification($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Obtener todas las notificaciones
        $stmt = $notification->getAll();
        $notifications = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $notifications[] = [
                'id' => $row['id'],
                'asunto' => $row['asunto'],
                'destinatario' => $row['destinatario'],
                'mensaje' => $row['mensaje'],
                'estado' => $row['estado'],
                'usuario_id' => $row['usuario_id'],
                'enviado_en' => $row['enviado_en']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'notifications' => $notifications
            ],
            'message' => 'Notificaciones cargadas correctamente desde la base de datos'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar notificaciones: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input && isset($input['to']) && isset($input['subject']) && isset($input['message'])) {
            // Crear notificación
            $data = [
                'asunto' => $input['subject'],
                'destinatario' => $input['to'],
                'mensaje' => $input['message'],
                'estado' => 'pendiente',
                'usuario_id' => isset($input['usuario_id']) ? $input['usuario_id'] : 1 // Default user ID
            ];
            
            if($notification->create($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Notificación creada exitosamente en la base de datos',
                    'data' => [
                        'notification_id' => $db->lastInsertId(),
                        'status' => 'pendiente',
                        'timestamp' => date('Y-m-d H:i:s')
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear notificación en la base de datos'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Datos incompletos'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear notificación: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>


