<?php
class Nivel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Listar solo los niveles con estado 'A'
    public function obtenerNivelesConParalelos() {
        // Consulta SQL para obtener niveles con paralelos asociados
        $query = "
            SELECT n.id_nivel, n.nombre AS nivel_nombre, 
                   GROUP_CONCAT(p.nombre ORDER BY p.nombre SEPARATOR ', ') AS paralelos
            FROM niveles n
            LEFT JOIN paralelo_niveles pn ON n.id_nivel = pn.id_nivel
            LEFT JOIN paralelo p ON pn.id_paralelo = p.id_paralelo
            WHERE n.estado = 'A'  -- Filtramos para mostrar solo los niveles activos
            GROUP BY n.id_nivel, n.nombre
        ";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        
        // Retornamos los resultados como un array asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function obtenerParalelos() {
        $query = "SELECT id_paralelo, nombre FROM paralelo WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    // Obtener un nivel específico
    public function obtenerNivel($id_nivel) {
        $query = "SELECT * FROM niveles WHERE id_nivel = :id_nivel";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Agregar un nuevo nivel con estado 'A' por defecto
    public function agregarNivelConParalelos($nombre, $paralelos = []) {
        try {
            $this->conexion->beginTransaction(); // Comienza una transacción
            
            // Insertar en la tabla niveles
            $query = "INSERT INTO niveles (nombre, estado) VALUES (:nombre, 'A')";
            $stmt = $this->conexion->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->execute();
            
            // Obtener el ID del nivel recién insertado
            $id_nivel = $this->conexion->lastInsertId();
            
            // Insertar en la tabla intermedia nivel_paralelo para cada paralelo seleccionado
            if (!empty($paralelos)) {
                $queryNP = "INSERT INTO paralelo_niveles (id_nivel, id_paralelo) VALUES (:id_nivel, :id_paralelo)";
                $stmtNP = $this->conexion->prepare($queryNP);
                foreach ($paralelos as $id_paralelo) {
                    $stmtNP->bindParam(':id_nivel', $id_nivel);
                    $stmtNP->bindParam(':id_paralelo', $id_paralelo);
                    $stmtNP->execute();
                }
            }
            
            // Confirmar los cambios en la base de datos
            $this->conexion->commit();
            return true;
            
        } catch (Exception $e) {
            // Si ocurre un error, revertir los cambios
            $this->conexion->rollBack();
            // Opcional: puedes registrar el error $e->getMessage();
            return false;
        }
    }
    




    // Actualizar solo el nombre del nivel, sin cambiar el estado
    public function actualizarNivel($id_nivel, $nombre) {
        $query = "UPDATE niveles SET nombre = :nombre WHERE id_nivel = :id_nivel";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id_nivel', $id_nivel);
        return $stmt->execute();
    }

    // Eliminación lógica: Cambia estado a 'I' en lugar de eliminar
    public function eliminarNivel($id_nivel) {
        $query = "UPDATE niveles SET estado = 'I' WHERE id_nivel = :id_nivel";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_nivel', $id_nivel);
        return $stmt->execute();
    }

    public function agregarNivelParalelo($id_nivel, $id_paralelo) {
        $query = "INSERT INTO nivel_paralelo (id_nivel, id_paralelo) VALUES (:id_nivel, :id_paralelo)";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        return $stmt->execute();
    }
    
}
?>
