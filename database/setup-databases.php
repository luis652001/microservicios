<?php
/**
 * Script de Configuración de Bases de Datos
 * Sistema de Microservicios - Arquitecturas 2025
 * 
 * Este script crea automáticamente las bases de datos y tablas necesarias
 * para el funcionamiento del sistema de microservicios en XAMPP.
 */

echo "<h1>🚀 Configuración de Bases de Datos - Sistema de Microservicios</h1>\n";
echo "<p>Configurando bases de datos para XAMPP...</p>\n";

// Configuración de conexión
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Conectar a MySQL sin especificar base de datos
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");
    
    echo "<p>✅ Conexión a MySQL exitosa</p>\n";
    
    // Leer el archivo de esquemas
    $schemaFile = __DIR__ . '/schemas.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Archivo de esquemas no encontrado: $schemaFile");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Dividir el SQL en comandos individuales
    $commands = array_filter(array_map('trim', explode(';', $sql)));
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($commands as $command) {
        if (empty($command)) continue;
        
        try {
            $pdo->exec($command);
            $successCount++;
            echo "<p>✅ Comando ejecutado: " . substr($command, 0, 50) . "...</p>\n";
        } catch (PDOException $e) {
            // Ignorar errores de "database already exists" y "table already exists"
            if (strpos($e->getMessage(), 'database exists') !== false || 
                strpos($e->getMessage(), 'table exists') !== false) {
                $successCount++;
                echo "<p>✅ Comando ya existía: " . substr($command, 0, 50) . "...</p>\n";
            } else {
                $errorCount++;
                echo "<p>❌ Error en comando: " . $e->getMessage() . "</p>\n";
            }
        }
    }
    
    echo "<h2>📊 Resumen de la Configuración</h2>\n";
    echo "<p>✅ Comandos exitosos: $successCount</p>\n";
    echo "<p>❌ Errores: $errorCount</p>\n";
    
    if ($errorCount === 0) {
        echo "<h2>🎉 ¡Configuración Completada!</h2>\n";
        echo "<p>Las bases de datos han sido creadas exitosamente:</p>\n";
        echo "<ul>\n";
        echo "<li>microservicios_usuarios_db</li>\n";
        echo "<li>microservicios_datos_db</li>\n";
        echo "<li>microservicios_notificaciones_db</li>\n";
        echo "</ul>\n";
        
        echo "<h3>🔧 Próximos Pasos:</h3>\n";
        echo "<ol>\n";
        echo "<li>Verifica que XAMPP esté ejecutándose (Apache y MySQL)</li>\n";
        echo "<li>Accede al dashboard: <a href='../dashboard.html'>dashboard.html</a></li>\n";
        echo "<li>Prueba el login con: demo@microservicios.com / password</li>\n";
        echo "</ol>\n";
    } else {
        echo "<h2>⚠️ Configuración con Errores</h2>\n";
        echo "<p>Algunos comandos fallaron. Revisa los errores arriba.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error de Conexión</h2>\n";
    echo "<p>No se pudo conectar a MySQL: " . $e->getMessage() . "</p>\n";
    echo "<h3>🔧 Solución:</h3>\n";
    echo "<ol>\n";
    echo "<li>Verifica que XAMPP esté ejecutándose</li>\n";
    echo "<li>Verifica que el servicio MySQL esté activo</li>\n";
    echo "<li>Verifica que el puerto 3306 esté disponible</li>\n";
    echo "<li>Reinicia XAMPP si es necesario</li>\n";
    echo "</ol>\n";
}

echo "<hr>\n";
echo "<p><small>Script ejecutado el: " . date('Y-m-d H:i:s') . "</small></p>\n";
?>
