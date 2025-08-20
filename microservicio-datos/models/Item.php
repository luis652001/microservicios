<?php
/**
 * Modelo Item - Microservicio Datos
 * Sistema de Microservicios - Arquitecturas 2025
 */

require_once __DIR__ . '/../config/database.php';

class Item {
    private $conn;
    private $table_name = "items";

    public $id;
    public $nombre;
    public $descripcion;
    public $estado;
    public $usuario_id;
    public $creado_en;
    public $actualizado_en;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, estado, usuario_id) 
                  VALUES (:nombre, :descripcion, :estado, :usuario_id)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

        // Bind de parámetros
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":usuario_id", $this->usuario_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Leer todos los items de un usuario
    public function readByUser($usuario_id) {
        $query = "SELECT id, nombre, descripcion, estado, creado_en 
                  FROM " . $this->table_name . " 
                  WHERE usuario_id = ? 
                  ORDER BY creado_en DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $usuario_id);
        $stmt->execute();

        return $stmt;
    }

    // Leer todos los items (para el endpoint real)
    public function readAll() {
        $query = "SELECT id, nombre, descripcion, estado, usuario_id, creado_en 
                  FROM " . $this->table_name . " 
                  ORDER BY creado_en DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Leer un item específico
    public function readOne() {
        $query = "SELECT id, nombre, descripcion, estado, usuario_id, creado_en 
                  FROM " . $this->table_name . " 
                  WHERE id = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->estado = $row['estado'];
            $this->usuario_id = $row['usuario_id'];
            $this->creado_en = $row['creado_en'];
            return true;
        }
        return false;
    }

    // Actualizar item
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, descripcion = :descripcion, estado = :estado 
                  WHERE id = :id AND usuario_id = :usuario_id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

        // Bind de parámetros
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':usuario_id', $this->usuario_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Eliminar item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " 
                  WHERE id = ? AND usuario_id = ?";

        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));

        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->usuario_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Verificar si el item pertenece al usuario
    public function belongsToUser($usuario_id) {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE id = ? AND usuario_id = ? 
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $usuario_id);
        $stmt->execute();

        $num = $stmt->rowCount();
        return $num > 0;
    }

    // Buscar items por nombre
    public function searchByName($search_term, $usuario_id) {
        $query = "SELECT id, nombre, descripcion, estado, creado_en 
                  FROM " . $this->table_name . " 
                  WHERE nombre LIKE ? AND usuario_id = ? 
                  ORDER BY creado_en DESC";

        $stmt = $this->conn->prepare($query);
        $search_term = "%{$search_term}%";
        $stmt->bindParam(1, $search_term);
        $stmt->bindParam(2, $usuario_id);
        $stmt->execute();

        return $stmt;
    }

    // Obtener estadísticas del usuario
    public function getUserStats($usuario_id) {
        $query = "SELECT 
                    COUNT(*) as total_items,
                    COUNT(CASE WHEN estado = 'activo' THEN 1 END) as items_activos,
                    COUNT(CASE WHEN estado = 'inactivo' THEN 1 END) as items_inactivos
                  FROM " . $this->table_name . " 
                  WHERE usuario_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $usuario_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
