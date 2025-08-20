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
$testItems = [
    [
        'id' => 1,
        'nombre' => 'Item de Prueba 1',
        'descripcion' => 'Este es un item de prueba',
        'estado' => 'activo',
        'creado_en' => '2024-01-15 10:00:00'
    ],
    [
        'id' => 2,
        'nombre' => 'Item de Prueba 2',
        'descripcion' => 'Otro item de prueba',
        'estado' => 'inactivo',
        'creado_en' => '2024-01-15 11:00:00'
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true,
        'data' => [
            'items' => $testItems
        ],
        'message' => 'Items de prueba cargados correctamente'
    ]);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input && isset($input['nombre']) && isset($input['descripcion'])) {
        echo json_encode([
            'success' => true,
            'message' => 'Item creado exitosamente (modo prueba)',
            'data' => [
                'id' => rand(100, 999),
                'nombre' => $input['nombre'],
                'descripcion' => $input['descripcion'],
                'estado' => $input['estado'] ?? 'activo'
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


