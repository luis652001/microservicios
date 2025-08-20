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
error_log("Debug Items - Método: " . $_SERVER['REQUEST_METHOD']);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Simular datos de prueba
        $testItems = [
            [
                'id' => 1,
                'nombre' => 'Item de Debug 1',
                'descripcion' => 'Este es un item de debug',
                'estado' => 'activo',
                'creado_en' => '2024-01-15 10:00:00'
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $testItems
            ],
            'message' => 'Items de debug cargados correctamente',
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
        
        if ($input && isset($input['nombre']) && isset($input['descripcion'])) {
            echo json_encode([
                'success' => true,
                'message' => 'Item de debug creado exitosamente',
                'data' => [
                    'id' => rand(100, 999),
                    'nombre' => $input['nombre'],
                    'descripcion' => $input['descripcion'],
                    'estado' => $input['estado'] ?? 'activo'
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


