<?php
/**
 * Test Simple - Microservicio Usuarios
 */

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

echo json_encode([
    'success' => true,
    'service' => 'usuarios',
    'status' => 'testing',
    'message' => 'Microservicio de usuarios funcionando',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>

