-- Esquemas de Base de Datos - Sistema de Microservicios
-- Arquitecturas 2025

-- ===== BASE DE DATOS PARA MICROSERVICIO DE USUARIOS =====
CREATE DATABASE IF NOT EXISTS microservicios_usuarios_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE microservicios_usuarios_db;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(80) NOT NULL,
    correo VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_correo (correo),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== BASE DE DATOS PARA MICROSERVICIO DE DATOS =====
CREATE DATABASE IF NOT EXISTS microservicios_datos_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE microservicios_datos_db;

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    usuario_id INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_creado_en (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== BASE DE DATOS PARA MICROSERVICIO DE NOTIFICACIONES =====
CREATE DATABASE IF NOT EXISTS microservicios_notificaciones_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE microservicios_notificaciones_db;

CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    destinatario VARCHAR(120) NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('pendiente', 'enviado', 'error') DEFAULT 'pendiente',
    enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_destinatario (destinatario),
    INDEX idx_estado (estado),
    INDEX idx_enviado_en (enviado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== INSERTAR DATOS DE PRUEBA =====

-- Usuarios de prueba
USE microservicios_usuarios_db;
INSERT INTO usuarios (nombre, correo, password_hash) VALUES
('Usuario Demo MS', 'demo@microservicios.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('Admin MS', 'admin@microservicios.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- Items de prueba
USE microservicios_datos_db;
INSERT INTO items (nombre, descripcion, estado, usuario_id) VALUES
('Item MS 1', 'Primer item del sistema de microservicios', 'activo', 1),
('Item MS 2', 'Segundo item del sistema de microservicios', 'activo', 1),
('Item Admin MS', 'Item del usuario administrador', 'activo', 2);

-- Notificaciones de prueba
USE microservicios_notificaciones_db;
INSERT INTO notificaciones (usuario_id, destinatario, asunto, mensaje, estado) VALUES
(1, 'test@example.com', 'Bienvenido al Sistema MS', 'Tu cuenta ha sido creada exitosamente', 'enviado'),
(2, 'admin@test.com', 'Configuración Completada', 'El sistema está listo para usar', 'enviado');

-- ===== CREAR USUARIOS DE BASE DE DATOS (AJUSTAR SEGÚN CONFIGURACIÓN) =====
-- CREATE USER 'ms_usuarios_user'@'localhost' IDENTIFIED BY 'password_usuarios';
-- GRANT ALL PRIVILEGES ON microservicios_usuarios_db.* TO 'ms_usuarios_user'@'localhost';

-- CREATE USER 'ms_datos_user'@'localhost' IDENTIFIED BY 'password_datos';
-- GRANT ALL PRIVILEGES ON microservicios_datos_db.* TO 'ms_datos_user'@'localhost';

-- CREATE USER 'ms_notificaciones_user'@'localhost' IDENTIFIED BY 'password_notificaciones';
-- GRANT ALL PRIVILEGES ON microservicios_notificaciones_db.* TO 'ms_notificaciones_user'@'localhost';

-- FLUSH PRIVILEGES;

-- ===== VERIFICAR TABLAS CREADAS =====
USE microservicios_usuarios_db;
SHOW TABLES;
DESCRIBE usuarios;

USE microservicios_datos_db;
SHOW TABLES;
DESCRIBE items;

USE microservicios_notificaciones_db;
SHOW TABLES;
DESCRIBE notificaciones;
