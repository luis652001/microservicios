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

// Simular datos de prueba
$testNotifications = [
    [
        'id' => 1,
        'asunto' => 'Notificación de Prueba 1',
        'destinatario' => 'test1@email.com',
        'mensaje' => 'Esta es una notificación de prueba',
        'estado' => 'enviado',
        'enviado_en' => '2024-01-15 10:00:00'
    ],
    [
        'id' => 2,
        'asunto' => 'Notificación de Prueba 2',
        'destinatario' => 'test2@email.com',
        'mensaje' => 'Otra notificación de prueba',
        'estado' => 'pendiente',
        'enviado_en' => '2024-01-15 11:00:00'
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => [
            'notifications' => $testNotifications
        ],
        'message' => 'Notificaciones de prueba cargadas correctamente'
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['to']) && isset($input['subject']) && isset($input['message'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Notificación enviada exitosamente (modo prueba)',
            'data' => [
                'notification_id' => rand(100, 999),
                'status' => 'enviado',
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>


