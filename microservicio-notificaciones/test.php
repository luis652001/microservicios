<?php
/**
 * Test Simple - Microservicio Notificaciones
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'service' => 'notificaciones',
    'status' => 'testing',
    'message' => 'Microservicio de notificaciones funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

