<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DocenteEstudiantesExport {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerDatosDocente($id_docente) {
        $sql = "SELECT 
                    a.nombre AS asignatura, 
                    c.nombre AS carrera, 
                    n.nombre AS nivel, 
                    j.nombre AS jornada, 
                    p.nombre AS paralelo,
                    da.id_asignatura,
                    da.id_carrera,
                    da.id_nivel,
                    da.id_jornada,
                    da.id_paralelo,
                    u.username AS docente_nombre  
                FROM docente_asignatura da
                INNER JOIN asignaturas a ON da.id_asignatura = a.id_asignatura
                INNER JOIN carreras c ON da.id_carrera = c.id_carrera
                INNER JOIN niveles n ON da.id_nivel = n.id_nivel
                LEFT JOIN jornadas j ON da.id_jornada = j.id_jornada
                LEFT JOIN paralelo p ON da.id_paralelo = p.id_paralelo
                INNER JOIN usuarios u ON da.id_docente = u.id_usuario  
                WHERE da.id_docente = :id_docente";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_docente', $id_docente);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEstudiantes($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $sql = "SELECT ea.id_estudiante_asignatura, u.id_usuario, u.username
                FROM estudiante_asignatura ea
                INNER JOIN usuarios u ON ea.id_estudiante = u.id_usuario
                WHERE ea.id_asignatura = :id_asignatura
                  AND ea.id_carrera = :id_carrera
                  AND ea.id_nivel = :id_nivel
                  AND ea.id_jornada = :id_jornada
                  AND ea.id_paralelo = :id_paralelo";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bindParam(':id_asignatura', $id_asignatura);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_jornada', $id_jornada);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function generarExcel($id_docente) {
        $asignaturas = $this->obtenerDatosDocente($id_docente);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Títulos de las columnas para los datos del docente
        $sheet->setCellValue('A1', 'Carrera');
        $sheet->setCellValue('B1', 'Jornada');
        $sheet->setCellValue('C1', 'Nivel');
        $sheet->setCellValue('D1', 'Paralelo');
        $sheet->setCellValue('E1', 'Asignatura');
        $sheet->setCellValue('F1', 'Nombre del Docente');

        // Escribir la información del docente
        $row = 2;
        foreach ($asignaturas as $asignatura) {
            $sheet->setCellValue('A' . $row, $asignatura['carrera']);
            $sheet->setCellValue('B' . $row, $asignatura['jornada']);
            $sheet->setCellValue('C' . $row, $asignatura['nivel']);
            $sheet->setCellValue('D' . $row, $asignatura['paralelo']);
            $sheet->setCellValue('E' . $row, $asignatura['asignatura']);
            $sheet->setCellValue('F' . $row, $asignatura['docente_nombre']); // Nombre del docente obtenido
            $row++;
        }

        // Obtener los estudiantes matriculados para cada asignatura
        $row++;
        $sheet->setCellValue('A' . $row, 'Estudiantes Matriculados');
        $row++;

        // Recorrer las asignaturas y agregar los estudiantes matriculados
        foreach ($asignaturas as $asignatura) {
            $estudiantes = $this->getEstudiantes(
                $asignatura['id_asignatura'],
                $asignatura['id_carrera'],
                $asignatura['id_nivel'],
                $asignatura['id_jornada'],
                $asignatura['id_paralelo']
            );
            
            // Títulos de las columnas para los estudiantes
            $sheet->setCellValue('A' . $row, 'ID Estudiante');
            $sheet->setCellValue('B' . $row, 'Nombre Estudiante');
            $row++;

            foreach ($estudiantes as $estudiante) {
                $sheet->setCellValue('A' . $row, $estudiante['id_usuario']);
                $sheet->setCellValue('B' . $row, $estudiante['username']);
                $row++;
            }

            // Añadir un salto de fila después de cada asignatura
            $row++;
        }

        // Guardar el archivo Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'Estudiantes_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
    }
}
