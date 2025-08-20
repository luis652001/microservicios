<?php
/**
 * API Gateway - Sistema de Microservicios
 * Arquitecturas 2025
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

// Configuración de microservicios
$microservices = [
    'usuarios' => 'http://localhost/microservicios/microservicio-usuarios',
    'datos' => 'http://localhost/microservicios/microservicio-datos',
    'notificaciones' => 'http://localhost/microservicios/microservicio-notificaciones'
];

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
        'gateway' => 'API Gateway v1.0'
    ], $status_code);
}

// Función para validar JWT (simplificada para el gateway)
function validateJWT($token) {
    if (empty($token)) {
        return false;
    }
    
    // En un entorno real, aquí se validaría el JWT
    // Por simplicidad, solo verificamos que exista
    return !empty($token);
}

// Función para hacer proxy a microservicios
function proxyToService($service, $method, $endpoint, $data = null, $headers = []) {
    global $microservices;
    
    if (!isset($microservices[$service])) {
        return [
            'success' => false,
            'message' => 'Servicio no encontrado'
        ];
    }
    
    $url = $microservices[$service] . $endpoint;
    
    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Configurar método HTTP
    switch ($method) {
        case 'GET':
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            break;
        case 'POST':
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'PUT':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            break;
        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;
    }
    
    // Ejecutar request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [
            'success' => false,
            'message' => 'Error de conexión: ' . $error
        ];
    }
    
    // Decodificar respuesta
    $response_data = json_decode($response, true);
    if (!$response_data) {
        $response_data = ['raw_response' => $response];
    }
    
    return [
        'success' => $http_code >= 200 && $http_code < 300,
        'data' => $response_data,
        'status_code' => $http_code,
        'service' => $service
    ];
}

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug: mostrar la ruta completa
error_log("Ruta completa: " . $path);

// Extraer solo la parte después de /api-gateway/
$path = str_replace('/microservicios/api-gateway/', '', $path);
$path = str_replace('/api-gateway/', '', $path);

// Debug: mostrar la ruta procesada
error_log("Ruta procesada: " . $path);

$path_parts = explode('/', $path);

// Obtener datos del body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Obtener headers
$headers = [];
foreach (getallheaders() as $name => $value) {
    $headers[] = "$name: $value";
}

// Enrutamiento del Gateway
try {
    // Debug: mostrar las partes de la ruta
    error_log("Partes de la ruta: " . print_r($path_parts, true));
    error_log("Primera parte: " . ($path_parts[0] ?? 'VACÍO'));
    
    // Ruta de prueba del gateway
    if ($path_parts[0] === 'test') {
        sendResponse([
            'success' => true,
            'message' => 'API Gateway funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'services' => array_keys($microservices)
        ]);
    }
    
    // Ruta de autenticación (proxy a microservicio de usuarios)
    elseif ($path_parts[0] === 'auth') {
        $endpoint = '/auth/' . ($path_parts[1] ?? '');
        $result = proxyToService('usuarios', $method, $endpoint, $input, $headers);
        sendResponse($result, $result['status_code'] ?? 200);
    }
    
    // Ruta de usuarios (proxy a microservicio de usuarios)
    elseif ($path_parts[0] === 'usuarios') {
        $endpoint = '/usuarios/' . ($path_parts[1] ?? '');
        $result = proxyToService('usuarios', $method, $endpoint, $input, $headers);
        sendResponse($result, $result['status_code'] ?? 200);
    }
    
    // Ruta de items (proxy a microservicio de datos, requiere autenticación)
    elseif ($path_parts[0] === 'items') {
        // Verificar autenticación
        $auth_header = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : '';
        if (!validateJWT($auth_header)) {
            sendError('Token de autenticación requerido', 401);
        }
        
        $endpoint = '/items/' . ($path_parts[1] ?? '');
        $result = proxyToService('datos', $method, $endpoint, $input, $headers);
        sendResponse($result, $result['status_code'] ?? 200);
    }
    
    // Ruta de notificaciones (proxy a microservicio de notificaciones, requiere autenticación)
    elseif ($path_parts[0] === 'notify') {
        // Verificar autenticación
        $auth_header = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : '';
        if (!validateJWT($auth_header)) {
            sendError('Token de autenticación requerido', 401);
        }
        
        $endpoint = '/notify';
        $result = proxyToService('notificaciones', $method, $endpoint, $input, $headers);
        sendResponse($result, $result['status_code'] ?? 200);
    }
    
    // Ruta de estado de items (proxy a microservicio de datos)
    elseif ($path_parts[0] === 'status' && isset($path_parts[1]) && isset($path_parts[2])) {
        // Verificar autenticación
        $auth_header = isset(getallheaders()['Authorization']) ? getallheaders()['Authorization'] : '';
        if (!validateJWT($auth_header)) {
            sendError('Token de autenticación requerido', 401);
        }
        
        $endpoint = '/status/' . $path_parts[1] . '/' . $path_parts[2];
        $result = proxyToService('datos', $method, $endpoint, [], $headers);
        sendResponse($result, $result['status_code'] ?? 200);
    }
    
    // Ruta de health check para todos los servicios
    elseif ($path_parts[0] === 'health') {
        $health_status = [];
        
        foreach ($microservices as $service => $url) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            
            $start_time = microtime(true);
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $response_time = (microtime(true) - $start_time) * 1000;
            curl_close($ch);
            
            $health_status[$service] = [
                'status' => $http_code === 200 ? 'healthy' : 'unhealthy',
                'response_time_ms' => round($response_time, 2),
                'url' => $url
            ];
        }
        
        sendResponse([
            'success' => true,
            'gateway' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'services' => $health_status
        ]);
    }
    
    // Ruta no encontrada
    else {
        sendError('Endpoint no encontrado en el gateway', 404);
    }
    
} catch (Exception $e) {
    sendError('Error interno del gateway: ' . $e->getMessage(), 500);
}
?>
