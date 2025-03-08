<?php
class Carrera {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Listar carreras activas con sus jornadas y niveles asociados
    public function obtenerCarrerasJornadasNiveles() {
        $query = "SELECT c.id_carrera, c.nombre AS carrera, 
                        GROUP_CONCAT(DISTINCT j.nombre ORDER BY j.id_jornada ASC SEPARATOR ', ') AS jornadas,
                        GROUP_CONCAT(DISTINCT n.nombre ORDER BY n.id_nivel ASC SEPARATOR ', ') AS niveles
                FROM carreras c
                LEFT JOIN carrera_jornadas cj ON c.id_carrera = cj.id_carrera
                LEFT JOIN jornadas j ON cj.id_jornada = j.id_jornada
                LEFT JOIN carrera_niveles cn ON c.id_carrera = cn.id_carrera
                LEFT JOIN niveles n ON cn.id_nivel = n.id_nivel
                WHERE c.estado = 'A'
                GROUP BY c.id_carrera, c.nombre";

        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    
    // Listar solo las carreras con estado 'A'
    public function obtenerCarreras() {
        $query = "SELECT * FROM carreras WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener una carrera específica
    public function obtenerCarrera($id_carrera) {
        $query = "SELECT * FROM carreras WHERE id_carrera = :id_carrera";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function obtenerJornadas() {
        $query = "SELECT * FROM jornadas WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerNiveles() {
        $query = "SELECT * FROM niveles WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
     // Agregar una nueva carrera junto con sus niveles asociados
     public function agregarCarreraJornadas($nombre, $jornadas = [], $niveles = []) {
        try {
            $this->conexion->beginTransaction();
            
            // Insertar en la tabla carreras
            $query = "INSERT INTO carreras (nombre, estado) VALUES (:nombre, 'A')";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->execute();
            
            $id_carrera = $this->conexion->lastInsertId();
            
            // Insertar en la tabla intermedia carrera_jornadas para cada jornada seleccionada
            if (!empty($jornadas)) {
                $queryCJ = "INSERT INTO carrera_jornadas (id_carrera, id_jornada) VALUES (:id_carrera, :id_jornada)";
                $stmtCJ = $this->conexion->prepare($queryCJ);
                foreach ($jornadas as $id_jornada) {
                    $stmtCJ->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
                    $stmtCJ->bindParam(':id_jornada', $id_jornada, PDO::PARAM_INT);
                    $stmtCJ->execute();
                }
            }
            
            // Insertar en la tabla intermedia carrera_niveles para cada nivel seleccionado
            if (!empty($niveles)) {
                $queryCN = "INSERT INTO carrera_niveles (id_carrera, id_nivel) VALUES (:id_carrera, :id_nivel)";
                $stmtCN = $this->conexion->prepare($queryCN);
                foreach ($niveles as $id_nivel) {
                    $stmtCN->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
                    $stmtCN->bindParam(':id_nivel', $id_nivel, PDO::PARAM_INT);
                    $stmtCN->execute();
                }
            }
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            // Opcional: puedes registrar el error $e->getMessage();
            return false;
        }
    }

    public function actualizarCarrera($id_carrera, $nombre, $niveles = []) {
        try {
            $this->conexion->beginTransaction();
            
            // Actualizar el nombre de la carrera
            $query = "UPDATE carreras SET nombre = :nombre WHERE id_carrera = :id_carrera";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id_carrera', $id_carrera);
            $stmt->execute();
            
            // Eliminar niveles existentes
            $query = "DELETE FROM carrera_nivel WHERE id_carrera = :id_carrera";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':id_carrera', $id_carrera);
            $stmt->execute();
            
            // Insertar nuevos niveles
            if (!empty($niveles)) {
                $queryCN = "INSERT INTO carrera_nivel (id_carrera, id_nivel) VALUES (:id_carrera, :id_nivel)";
                $stmtCN = $this->conexion->prepare($queryCN);
                foreach ($niveles as $id_nivel) {
                    $stmtCN->bindParam(':id_carrera', $id_carrera);
                    $stmtCN->bindParam(':id_nivel', $id_nivel);
                    $stmtCN->execute();
                }
            }
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return false;
        }
    }
    public function obtenerNivelesPorCarrera($id_carrera) {
        $query = "SELECT id_nivel FROM carrera_nivel WHERE id_carrera = :id_carrera";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Eliminación lógica: Cambia estado a 'I' en lugar de eliminar
    public function eliminarCarrera($id_carrera) {
        $query = "UPDATE carreras SET estado = 'I' WHERE id_carrera = :id_carrera";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_carrera', $id_carrera);
        return $stmt->execute();
    }
}
?>
