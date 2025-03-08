<?php
class Paralelo {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerParalelos() {
        $query = "SELECT * FROM paralelo WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerParalelo($id_paralelo) {
        $query = "SELECT * FROM paralelo WHERE id_paralelo = :id_paralelo";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function agregarParalelo($nombre) {
        $query = "INSERT INTO paralelo (nombre, estado) VALUES (:nombre, 'A')";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        return $stmt->execute();
    }

    public function actualizarParalelo($id_paralelo, $nombre) {
        $query = "UPDATE paralelo SET nombre = :nombre WHERE id_paralelo = :id_paralelo";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        return $stmt->execute();
    }

    public function eliminarParalelo($id_paralelo) {
        $query = "UPDATE paralelo SET estado = 'I' WHERE id_paralelo = :id_paralelo";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        return $stmt->execute();
    }
}
?>