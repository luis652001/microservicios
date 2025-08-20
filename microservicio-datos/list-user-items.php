<?php
/**
 * Endpoint para Listar Items de un Usuario Específico
 * Microservicio Datos - Sistema de Microservicios
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
    require_once '../models/Item.php';

    // Crear instancias
    $database = new Database();
    $db = $database->getConnection();
    $itemModel = new Item($db);

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

    // Obtener items del usuario
    $items = $itemModel->getItemsByUserId($user_id);

    if ($items !== false) {
        echo json_encode([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => count($items),
                'user_id' => $user_id
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener items del usuario'
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
