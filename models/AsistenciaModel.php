<?php
class AsistenciaModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Método para obtener la lista de estudiantes con su QR y asistencia
    public function getListaAsistencia($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $sql = "SELECT 
                    u.username, 
                    e.id_estudiante,  -- 🔹 Se agregó esta línea
                    q.codigo_qr, 
                    a.estado, 
                    a.latitud, 
                    a.longitud, 
                    a.device_id
                FROM estudiante_asignatura ea
                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                LEFT JOIN qr_estudiantes q ON e.id_estudiante = q.id_estudiante 
                    AND q.id_docente_asignatura = (
                        SELECT id_docente_asignatura 
                        FROM docente_asignatura 
                        WHERE id_asignatura = :id_asignatura 
                        LIMIT 1
                    )
                LEFT JOIN asistencia a ON e.id_estudiante = a.id_estudiante 
                    AND a.id_asignatura = :id_asignatura
                WHERE ea.id_asignatura = :id_asignatura
                  AND e.id_carrera = :id_carrera
                  AND e.id_nivel = :id_nivel
                  AND e.id_jornada = :id_jornada
                  AND e.id_paralelo = :id_paralelo";
    
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->bindParam(':id_carrera', $id_carrera, PDO::PARAM_INT);
        $stmt->bindParam(':id_nivel', $id_nivel, PDO::PARAM_INT);
        $stmt->bindParam(':id_jornada', $id_jornada, PDO::PARAM_INT);
        $stmt->bindParam(':id_paralelo', $id_paralelo, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
      // 🔹 Generar códigos QR para cada estudiante
      public function generarQRCodigos($id_docente, $id_asignatura) {
        $sqlEstudiantes = "SELECT e.id_estudiante 
                           FROM estudiante_asignatura ea
                           INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                           WHERE ea.id_asignatura = :id_asignatura";

        $stmtEstudiantes = $this->conexion->prepare($sqlEstudiantes);
        $stmtEstudiantes->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmtEstudiantes->execute();
        $estudiantes = $stmtEstudiantes->fetchAll(PDO::FETCH_ASSOC);

        if (empty($estudiantes)) {
            return false;
        }

        foreach ($estudiantes as $estudiante) {
            $codigo_qr = uniqid("QR_", true); // Generar código único

            // Insertar el código QR en la base de datos
            $sqlInsert = "INSERT INTO qr_estudiantes (id_estudiante, id_docente_asignatura, codigo_qr)
                          VALUES (:id_estudiante, 
                              (SELECT id_docente_asignatura FROM docente_asignatura 
                               WHERE id_asignatura = :id_asignatura LIMIT 1), 
                              :codigo_qr) 
                          ON DUPLICATE KEY UPDATE codigo_qr = :codigo_qr";

            $stmtInsert = $this->conexion->prepare($sqlInsert);
            $stmtInsert->bindParam(':id_estudiante', $estudiante['id_estudiante'], PDO::PARAM_INT);
            $stmtInsert->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
            $stmtInsert->bindParam(':codigo_qr', $codigo_qr, PDO::PARAM_STR);
            $stmtInsert->execute();
        }

        return true;
    }

    // 🔹 Registrar asistencia de un estudiante cuando escanea el QR
    public function registrarAsistencia($id_estudiante, $id_asignatura, $latitud, $longitud) {
        $sql = "INSERT INTO asistencia (id_estudiante, id_asignatura, fecha, latitud, longitud, estado)
                VALUES (:id_estudiante, :id_asignatura, NOW(), :latitud, :longitud, 'asistió')
                ON DUPLICATE KEY UPDATE estado = 'asistió', latitud = :latitud, longitud = :longitud, fecha = NOW()";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->bindParam(':latitud', $latitud, PDO::PARAM_STR);
        $stmt->bindParam(':longitud', $longitud, PDO::PARAM_STR);
        
        return $stmt->execute();
    }

    // 🔹 Registrar asistencia manualmente
  
    public function marcarAsistenciaManual($id_estudiante, $id_asignatura) {
    // Verificar si ya existe una asistencia para este estudiante en la asignatura
    $sqlCheck = "SELECT COUNT(*) as total FROM asistencia WHERE id_estudiante = :id_estudiante AND id_asignatura = :id_asignatura";
    $stmtCheck = $this->conexion->prepare($sqlCheck);
    $stmtCheck->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
    $stmtCheck->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
    $stmtCheck->execute();
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($row['total'] > 0) {
        return "error_ya_asistio"; // 🔹 Devolver un mensaje de error si ya asistió
    }

    // Insertar asistencia manual solo si no existe
    $sql = "INSERT INTO asistencia (id_estudiante, id_asignatura, fecha, estado, latitud, longitud, device_id) 
            VALUES (:id_estudiante, :id_asignatura, NOW(), 'Asistió', NULL, NULL, NULL)";

    $stmt = $this->conexion->prepare($sql);
    $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
    $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);

    return $stmt->execute() ? "ok" : "error";
}

}
?>
