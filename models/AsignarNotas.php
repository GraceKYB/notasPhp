<?php
class AsignarNotas {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    // Obtiene todos los aportes activos (asumiendo estado 'A' para activo)
    public function getAportes() {
        $sql = "SELECT id_aporte, nombre FROM aportes WHERE estado = 'A'";  // Ajusta el nombre de la tabla si es diferente
        $query = $this->conexion->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Obtiene los estudiantes matriculados según los parámetros enviados
    public function getEstudiantes($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $sql = "SELECT e.id_estudiante AS id_estudiante_asignatura, u.id_usuario, u.username
                FROM estudiante_asignatura ea
                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                WHERE ea.id_asignatura = :id_asignatura
                  AND e.id_carrera = :id_carrera
                  AND e.id_nivel = :id_nivel
                  AND e.id_jornada = :id_jornada
                  AND e.id_paralelo = :id_paralelo";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_asignatura', $id_asignatura);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_jornada', $id_jornada);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

       // Método para guardar o actualizar las notas
       public function guardarNotas($id_asignatura, $notas) {
        try {
            $this->conexion->beginTransaction();
            
            // Recorrer cada aporte y estudiante
            foreach ($notas as $id_aporte => $estudiantes) {
                foreach ($estudiantes as $id_estudiante => $notas_array) {
                    
                    // Seleccionar los registros existentes para este estudiante, asignatura y aporte
                    $sqlSelect = "SELECT id_notas FROM notas 
                                  WHERE id_estudiante = :id_estudiante 
                                    AND id_asignatura = :id_asignatura 
                                    AND id_aporte = :id_aporte
                                  ORDER BY id_notas ASC";
                    $stmtSelect = $this->conexion->prepare($sqlSelect);
                    $stmtSelect->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
                    $stmtSelect->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
                    $stmtSelect->bindParam(':id_aporte', $id_aporte, PDO::PARAM_INT);
                    $stmtSelect->execute();
                    $existingRows = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
                    $existingCount = count($existingRows);
                    
                    // Recorrer las notas enviadas desde el formulario
                    for ($i = 0; $i < count($notas_array); $i++) {
                        $nota = $notas_array[$i];
                        if ($i < $existingCount) {
                            // Actualizar la fila existente
                            $id_notas = $existingRows[$i]['id_notas'];
                            $sqlUpdate = "UPDATE notas SET nota = :nota WHERE id_notas = :id_notas";
                            $stmtUpdate = $this->conexion->prepare($sqlUpdate);
                            $stmtUpdate->bindParam(':nota', $nota);
                            $stmtUpdate->bindParam(':id_notas', $id_notas, PDO::PARAM_INT);
                            $stmtUpdate->execute();
                        } else {
                            // Insertar una nueva fila si no existe
                            $titulo = "Nota de aporte"; // Puedes ajustar el título si lo deseas
                            $descripcion = null;
                            $sqlInsert = "INSERT INTO notas (id_estudiante, id_asignatura, id_aporte, titulo, descripcion, nota)
                                          VALUES (:id_estudiante, :id_asignatura, :id_aporte, :titulo, :descripcion, :nota)";
                            $stmtInsert = $this->conexion->prepare($sqlInsert);
                            $stmtInsert->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
                            $stmtInsert->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
                            $stmtInsert->bindParam(':id_aporte', $id_aporte, PDO::PARAM_INT);
                            $stmtInsert->bindParam(':titulo', $titulo);
                            $stmtInsert->bindValue(':descripcion', $descripcion, PDO::PARAM_NULL);
                            $stmtInsert->bindParam(':nota', $nota);
                            $stmtInsert->execute();
                        }
                    }
                    
                    // Si existen más registros de los que se enviaron (por ejemplo, se eliminó un input), borrarlos
                    if ($existingCount > count($notas_array)) {
                        for ($j = count($notas_array); $j < $existingCount; $j++) {
                            $id_notas = $existingRows[$j]['id_notas'];
                            $sqlDelete = "DELETE FROM notas WHERE id_notas = :id_notas";
                            $stmtDelete = $this->conexion->prepare($sqlDelete);
                            $stmtDelete->bindParam(':id_notas', $id_notas, PDO::PARAM_INT);
                            $stmtDelete->execute();
                        }
                    }
                }
            }
            
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            // Puedes registrar $e->getMessage() para depurar
            return false;
        }
    }
    
    // Método auxiliar: obtiene el promedio de notas para un estudiante en un aporte específico
    public function getPromedioNota($id_estudiante, $id_asignatura, $id_aporte) {
        $sql = "SELECT AVG(nota) as promedio FROM notas 
                WHERE id_estudiante = :id_estudiante 
                  AND id_asignatura = :id_asignatura 
                  AND id_aporte = :id_aporte";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante);
        $stmt->bindParam(':id_asignatura', $id_asignatura);
        $stmt->bindParam(':id_aporte', $id_aporte);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['promedio'] !== null) ? $result['promedio'] : 0;
    }
    
    // Calcula el resumen bimestral para cada estudiante
    public function calcularResumenBimestral($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $estudiantes = $this->getEstudiantes($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo);
        $aportes = $this->getAportes();
        
        $resumen = [];
        foreach ($estudiantes as $estudiante) {
            $id_estudiante = $estudiante['id_estudiante_asignatura'];
            $nombre = $estudiante['username'];
            
            $promedioAD = $this->getPromedioNota($id_estudiante, $id_asignatura, 1);
            $promedioAP = $this->getPromedioNota($id_estudiante, $id_asignatura, 2);
            $promedioAA = $this->getPromedioNota($id_estudiante, $id_asignatura, 3);
            $promedioEX = $this->getPromedioNota($id_estudiante, $id_asignatura, 4);
            
            // Contribuciones ponderadas (ajusta los porcentajes según tu lógica)
            $contribAD = $promedioAD * 0.25;
            $contribAP = $promedioAP * 0.20;
            $contribAA = $promedioAA * 0.20;
            $contribEX = $promedioEX * 0.35;
            
            $promedioFinal = $contribAD + $contribAP + $contribAA + $contribEX;
            
            $resumen[] = [
                'id_estudiante' => $id_estudiante,
                'estudiante' => $nombre,
                'AD' => round($promedioAD, 2),
                'AP' => round($promedioAP, 2),
                'AA' => round($promedioAA, 2),
                'EX' => round($promedioEX, 2),
                'Promedio' => round($promedioFinal, 2)
            ];
        }
        return $resumen;
    }
    
    // Guarda el resumen bimestral en la tabla "bimestres"
    public function guardarResumenBimestral($id_asignatura, $resumen, $bimestre = 1) {
        try {
            $this->conexion->beginTransaction();
            $sql = "INSERT INTO bimestres (id_estudiante, id_asignatura, bimestre, 
                    promedio_docencia, promedio_practico, promedio_autonomo, promedio_examen, promedio_final)
                    VALUES (:id_estudiante, :id_asignatura, :bimestre, 
                    :promedio_docencia, :promedio_practico, :promedio_autonomo, :promedio_examen, :promedio_final)";
            $stmt = $this->conexion->prepare($sql);
            foreach ($resumen as $row) {
                $stmt->bindParam(':id_estudiante', $row['id_estudiante']);
                $stmt->bindParam(':id_asignatura', $id_asignatura);
                $stmt->bindParam(':bimestre', $bimestre);
                $stmt->bindParam(':promedio_docencia', $row['AD']);
                $stmt->bindParam(':promedio_practico', $row['AP']);
                $stmt->bindParam(':promedio_autonomo', $row['AA']);
                $stmt->bindParam(':promedio_examen', $row['EX']);
                $stmt->bindParam(':promedio_final', $row['Promedio']);
                $stmt->execute();
            }
            $this->conexion->commit();
            return true;
        } catch (Exception $e) {
            $this->conexion->rollBack();
            return false;
        }
    }
    public function getNotasExistentes($id_asignatura) {
        $sql = "SELECT n.id_estudiante, n.id_aporte, n.nota
                FROM notas n
                WHERE n.id_asignatura = :id_asignatura
                ORDER BY n.id_estudiante, n.id_aporte, n.id_notas ASC"; // Ordenar correctamente
    
        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>