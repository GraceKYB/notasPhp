<?php
class Matricula {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function listarEstudianteAsignaturas() {
        $sql = "SELECT 
                    u.username AS nombre_estudiante,
                    ca.nombre AS carrera,
                    jo.nombre AS jornada,
                    n.nombre AS nivel,
                    p.nombre AS paralelo,
                    GROUP_CONCAT(a.nombre SEPARATOR '<br>') AS asignaturas
                FROM estudiantes e
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                INNER JOIN carreras ca ON e.id_carrera = ca.id_carrera
                INNER JOIN jornadas jo ON e.id_jornada = jo.id_jornada
                INNER JOIN niveles n ON e.id_nivel = n.id_nivel
                INNER JOIN paralelo p ON e.id_paralelo = p.id_paralelo
                LEFT JOIN estudiante_asignatura ea ON e.id_estudiante = ea.id_estudiante
                LEFT JOIN asignaturas a ON ea.id_asignatura = a.id_asignatura
                GROUP BY e.id_estudiante
                ORDER BY u.username";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    public function buscarEstudiante($cedula){
        $sql = "SELECT u.id_usuario, du.nombre 
                FROM usuarios AS u
                JOIN detalles_usuarios AS du ON u.id_usuario = du.id_usuario
                WHERE du.cedula = :cedula AND u.id_perfil = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
   
    public function asignarEstudianteYAsignaturas($datosEstudiante, $asignaturas) {
        try {
            // Iniciar la transacción para garantizar la integridad de ambas inserciones
            $this->conexion->beginTransaction();
    
            // Insertar el registro del estudiante en la tabla 'estudiantes'
            $queryEstudiantes = "INSERT INTO estudiantes (id_usuario, id_carrera, id_jornada, id_nivel, id_paralelo) 
                                 VALUES (?, ?, ?, ?, ?)";
            $stmtEstudiantes = $this->conexion->prepare($queryEstudiantes);
            $stmtEstudiantes->execute([
                $datosEstudiante['id_usuario'],
                $datosEstudiante['id_carrera'],
                $datosEstudiante['id_jornada'],
                $datosEstudiante['id_nivel'],
                $datosEstudiante['id_paralelo']
            ]);
    
            // Obtener el id_estudiante generado en la inserción anterior
            $id_estudiante = $this->conexion->lastInsertId();
    
            // Insertar cada asignatura en la tabla 'estudiante_asignatura'
            // Nota: Se omite el campo 'bimestre' ya que tiene un valor por defecto (1)
            $queryAsignaturas = "INSERT INTO estudiante_asignatura (id_estudiante, id_asignatura) 
                                 VALUES (?, ?)";
            $stmtAsignaturas = $this->conexion->prepare($queryAsignaturas);
    
            foreach ($asignaturas as $asignacion) {
                $stmtAsignaturas->execute([
                    $id_estudiante,
                    $asignacion['id_asignatura']
                ]);
            }
    
            // Confirmar la transacción si todo ha salido bien
            $this->conexion->commit();
    
            return true;
        } catch (PDOException $e) {
            // En caso de error, revertir todos los cambios realizados en la transacción
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
