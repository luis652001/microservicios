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
require_once 'models/Item.php';

// Crear instancias
$database = new Database();
$db = $database->getConnection();
$item = new Item($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Obtener todos los items
        $stmt = $item->readAll();
        $items = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'estado' => $row['estado'],
                'usuario_id' => $row['usuario_id'],
                'creado_en' => $row['creado_en']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $items
            ],
            'message' => 'Items cargados correctamente desde la base de datos'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al cargar items: ' . $e->getMessage()
        ]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if ($input && isset($input['nombre']) && isset($input['descripcion'])) {
            // Crear item
            $item->nombre = $input['nombre'];
            $item->descripcion = $input['descripcion'];
            $item->estado = isset($input['estado']) ? $input['estado'] : 'activo';
            $item->usuario_id = isset($input['usuario_id']) ? $input['usuario_id'] : 1; // Default user ID
            
            if($item->create()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Item creado exitosamente en la base de datos',
                    'data' => [
                        'id' => $item->id,
                        'nombre' => $item->nombre,
                        'descripcion' => $item->descripcion,
                        'estado' => $item->estado
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear item en la base de datos'
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
            'message' => 'Error al crear item: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>


