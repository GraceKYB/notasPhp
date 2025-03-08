<?php
class Jornada {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerJornadas() {
        $query = "SELECT * FROM jornadas WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerJornada($id_jornada) {
        $query = "SELECT * FROM jornadas WHERE id_jornada = :id_jornada";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_jornada', $id_jornada);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function obtenerNiveles() {
        $query = "SELECT * FROM niveles WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregarJornada($nombre) {
        try {
            // Insertar la jornada
            $query = "INSERT INTO jornadas (nombre, estado) VALUES (:nombre, 'A')";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->execute();
            $id_jornada = $this->conexion->lastInsertId(); // Obtener el ID de la jornada insertada
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    public function actualizarJornada($id_jornada, $nombre) {
        $query = "UPDATE jornadas SET nombre = :nombre WHERE id_jornada = :id_jornada";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_jornada', $id_jornada);
        return $stmt->execute();
    }

    public function eliminarJornada($id_jornada) {
        $query = "UPDATE jornadas SET estado = 'I'   WHERE id_jornada = :id_jornada";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_jornada', $id_jornada);
        return $stmt->execute();
    }
}
?>
