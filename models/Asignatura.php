<?php
class Asignatura {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    public function obtenerAsignaturas() {
        $query = "SELECT 
                    c.nombre AS carrera, 
                    n.nombre AS nivel, 
                    GROUP_CONCAT(a.nombre SEPARATOR ', ') AS asignaturas
                  FROM asignaturas a
                  INNER JOIN carreras c ON a.id_carrera = c.id_carrera
                  INNER JOIN niveles n ON a.id_nivel = n.id_nivel
                  GROUP BY a.id_carrera, a.id_nivel
                  ORDER BY c.nombre, n.nombre";
        $stmt = $this->conexion->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function agregarAsignatura($nombre, $id_nivel, $id_carrera) {
        if ($id_nivel === null) {
            return false; // O manejarlo como prefieras
        }
    
        $query = "INSERT INTO asignaturas (nombre, id_nivel, id_carrera) VALUES (:nombre, :id_nivel, :id_carrera)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_carrera', $id_carrera);
        
        return $stmt->execute();
    }

    public function obtenerCarreras() {
        $query = "SELECT id_carrera, nombre FROM carreras";
        $stmt = $this->conexion->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerNivelesPorCarrera($id_carrera) {
        $query = "SELECT n.id_nivel, n.nombre 
                  FROM carrera_niveles cn
                  INNER JOIN niveles n ON cn.id_nivel = n.id_nivel
                  WHERE cn.id_carrera = :id_carrera";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
}