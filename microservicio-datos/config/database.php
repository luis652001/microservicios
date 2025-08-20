<?php
/**
 * Configuración de Base de Datos - Microservicio Datos
 * Sistema de Microservicios - Arquitecturas 2025
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'microservicios_datos_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log("Error de conexión a BD: " . $exception->getMessage());
            return null;
        }

        return $this->conn;
    }
}
?>
