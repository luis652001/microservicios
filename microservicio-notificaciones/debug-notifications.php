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

// Debug: mostrar información básica
error_log("Debug Notifications - Método: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Simular datos de prueba
        $testNotifications = [
            [
                'id' => 1,
                'asunto' => 'Notificación de Debug 1',
                'destinatario' => 'debug1@email.com',
                'mensaje' => 'Esta es una notificación de debug',
                'estado' => 'enviado',
                'enviado_en' => '2024-01-15 10:00:00'
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'notifications' => $testNotifications
            ],
            'message' => 'Notificaciones de debug cargadas correctamente',
            'debug' => 'Endpoint funcionando'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en debug: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input && isset($input['to']) && isset($input['subject']) && isset($input['message'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Notificación de debug enviada exitosamente',
                'data' => [
                    'notification_id' => rand(100, 999),
                    'status' => 'enviado',
                    'timestamp' => date('Y-m-d H:i:s')
                ],
                'debug' => 'POST funcionando'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Datos incompletos en debug'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en POST debug: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido en debug'
    ]);
}
?>


