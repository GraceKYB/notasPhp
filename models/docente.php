<?php
class docente {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    // Lista las asignaciones registradas en docente_asignatura junto con información del docente y asignatura
    public function listarDocenteAsignaturas() {
        $sql = "
            SELECT 
                u.username AS 'Nombre Docente',
                a.nombre AS 'Asignatura',
                j.nombre AS 'Jornada',
                p.nombre AS 'Paralelo',
                c.nombre AS 'Carrera',
                n.nombre AS 'Nivel'
            FROM 
                docente_asignatura da
            JOIN 
                usuarios u ON da.id_usuario = u.id_usuario
            JOIN 
                asignaturas a ON da.id_asignatura = a.id_asignatura
            JOIN 
                jornadas j ON da.id_jornada = j.id_jornada
            JOIN 
                paralelo p ON da.id_paralelo = p.id_paralelo
            JOIN 
                carreras c ON a.id_carrera = c.id_carrera
            JOIN 
                niveles n ON a.id_nivel = n.id_nivel
            ORDER BY 
                u.username, a.nombre, j.nombre, p.nombre;
        ";
        
        try {
            // Ejecutar la consulta
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            
            // Obtener los resultados
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Devolver los resultados
            return $resultados;
        } catch (PDOException $e) {
            // Manejar el error
            echo "Error al obtener la lista de docentes y asignaturas: " . $e->getMessage();
            return false;
        }
    }
        

    public function buscarDocente($cedula){
        $sql = "SELECT u.id_usuario, du.nombre 
                FROM usuarios AS u
                JOIN detalles_usuarios AS du ON u.id_usuario = du.id_usuario
                WHERE du.cedula = :cedula AND u.id_perfil = 2";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Se espera que $asignaciones sea un arreglo de elementos con claves: id_asignatura, id_jornada, id_paralelo
    public function asignarDocenteAsignatura($id_docente, $asignaciones) {
        try {
            $this->conexion->beginTransaction();
            
            foreach ($asignaciones as $asignacion) {
                // Asegúrate de que el id_paralelo existe en la tabla correspondiente
                $id_paralelo = $asignacion['id_paralelo'];
                $checkQuery = "SELECT COUNT(*) FROM paralelo WHERE id_paralelo = ?";
                $stmt = $this->conexion->prepare($checkQuery);
                $stmt->execute([$id_paralelo]);
                $count = $stmt->fetchColumn();
                
                if ($count == 0) {
                    echo "El id_paralelo $id_paralelo no existe en la tabla paralelo.<br>";
                    continue; // Saltar a la siguiente combinación si no existe el paralelo
                }
                
                // Realizar la inserción de la combinación de docente, asignatura, jornada y paralelo
                $query = "INSERT INTO docente_asignatura (id_usuario, id_asignatura, id_jornada, id_paralelo) 
                          VALUES (?, ?, ?, ?)";
                $stmt = $this->conexion->prepare($query);
                $stmt->execute([
                    $id_docente, 
                    $asignacion['id_asignatura'], 
                    $asignacion['id_jornada'], 
                    $asignacion['id_paralelo']
                ]);
            }
    
            $this->conexion->commit();
            return true;
        } catch (PDOException $e) {
            $this->conexion->rollBack();
            echo "Error en la inserción: " . $e->getMessage();
            return false;
        }
    }
    
    
    // Consulta para obtener todas las carreras
    public function obtenerCarreras() {
        $query = "SELECT id_carrera, nombre FROM carreras";
        $stmt = $this->conexion->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Consulta para obtener los niveles asociados a una carrera usando la tabla carrera_niveles
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
    
    // Consulta para obtener las jornadas disponibles para una carrera desde la tabla carrera_jornadas
    public function obtenerJornadasPorCarrera($id_carrera) {
        $query = "SELECT cj.id_jornada, j.nombre 
                  FROM carrera_jornadas cj
                  JOIN jornadas j ON cj.id_jornada = j.id_jornada
                  WHERE cj.id_carrera = :id_carrera";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    // Consulta para obtener los paralelos disponibles para un nivel usando la tabla paralelo_niveles
    public function obtenerParalelosPorNivel($id_nivel) {
        $query = "SELECT pn.id_paralelo, p.nombre 
                  FROM paralelo_niveles pn
                  JOIN paralelo p ON pn.id_paralelo = p.id_paralelo
                  WHERE pn.id_nivel = :id_nivel";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_nivel', $id_nivel, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    // Consulta para obtener las asignaturas asociadas a un nivel
    public function obtenerAsignaturasPorNivelYCarrera($id_nivel, $id_carrera) {
        $query = "SELECT id_asignatura, nombre 
                  FROM asignaturas 
                  WHERE id_nivel = :id_nivel AND id_carrera = :id_carrera";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(':id_nivel', $id_nivel, PDO::PARAM_INT);
        $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
