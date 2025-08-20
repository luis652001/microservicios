<?php
/**
 * Test Simple - Microservicio Datos
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'service' => 'datos',
    'status' => 'testing',
    'message' => 'Microservicio de datos funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

