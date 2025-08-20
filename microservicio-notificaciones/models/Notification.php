<?php
/**
 * Modelo Notification - Microservicio Notificaciones
 * Sistema de Microservicios - Arquitecturas 2025
 */

require_once __DIR__ . '/../config/database.php';

class Notification {
    private $conn;
    private $table_name = "notificaciones";

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Crear una nueva notificación
     */
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                      (usuario_id, destinatario, asunto, mensaje, estado, enviado_en) 
                      VALUES (:usuario_id, :destinatario, :asunto, :mensaje, :estado, NOW())";

            $stmt = $this->conn->prepare($query);

            // Bind de parámetros
            $stmt->bindParam(":usuario_id", $data['usuario_id']);
            $stmt->bindParam(":destinatario", $data['destinatario']);
            $stmt->bindParam(":asunto", $data['asunto']);
            $stmt->bindParam(":mensaje", $data['mensaje']);
            $stmt->bindParam(":estado", $data['estado']);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'id' => $this->conn->lastInsertId(),
                    'message' => 'Notificación creada exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear notificación'
                ];
            }
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error en base de datos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener notificaciones por usuario
     */
    public function getNotificationsByUserId($user_id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE usuario_id = :user_id 
                      ORDER BY enviado_en DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Obtener todas las notificaciones
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY enviado_en DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Actualizar estado de notificación
     */
    public function updateStatus($id, $status) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET estado = :status 
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Eliminar notificación
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>


