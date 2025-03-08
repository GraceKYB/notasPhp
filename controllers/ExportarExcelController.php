<?php
ob_start(); // Inicia la captura de salida
session_start();

require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Verificar que se reciban los parámetros necesarios
if (isset($_GET['id_asignatura'], $_GET['id_carrera'], $_GET['id_nivel'], $_GET['id_jornada'], $_GET['id_paralelo'])) {

    // Parámetros
    $id_asignatura = $_GET['id_asignatura'];
    $id_carrera    = $_GET['id_carrera'];
    $id_nivel      = $_GET['id_nivel'];
    $id_jornada    = $_GET['id_jornada'];
    $id_paralelo   = $_GET['id_paralelo'];
    
    // Función para obtener los datos del docente (incluyendo nombre desde la tabla usuarios)
    function obtenerDatosDocente($conexion, $id_docente, $id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $sql = "SELECT 
                    a.nombre AS asignatura, 
                    c.nombre AS carrera, 
                    n.nombre AS nivel, 
                    j.nombre AS jornada, 
                    p.nombre AS paralelo,
                    da.id_asignatura,
                    a.id_carrera,
                    a.id_nivel,
                    da.id_jornada,
                    da.id_paralelo,
                    u.username AS docente_nombre  
                FROM docente_asignatura da
                INNER JOIN asignaturas a ON da.id_asignatura = a.id_asignatura
                INNER JOIN carreras c ON a.id_carrera = c.id_carrera
                INNER JOIN niveles n ON a.id_nivel = n.id_nivel
                LEFT JOIN jornadas j ON da.id_jornada = j.id_jornada
                LEFT JOIN paralelo p ON da.id_paralelo = p.id_paralelo
                INNER JOIN usuarios u ON da.id_usuario = u.id_usuario  
                WHERE da.id_usuario = :id_docente
                  AND da.id_asignatura = :id_asignatura
                  AND a.id_carrera = :id_carrera
                  AND a.id_nivel = :id_nivel
                  AND da.id_jornada = :id_jornada
                  AND da.id_paralelo = :id_paralelo";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_docente', $id_docente);
        $stmt->bindParam(':id_asignatura', $id_asignatura);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_jornada', $id_jornada);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Función para obtener los estudiantes matriculados
    // Se obtiene directamente el id_estudiante de la tabla estudiantes y el username
    function getEstudiantes($conexion, $id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo) {
        $sql = "SELECT e.id_estudiante, u.username
                FROM estudiante_asignatura ea
                INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
                INNER JOIN usuarios u ON e.id_usuario = u.id_usuario
                WHERE ea.id_asignatura = :id_asignatura
                  AND e.id_carrera = :id_carrera
                  AND e.id_nivel = :id_nivel
                  AND e.id_jornada = :id_jornada
                  AND e.id_paralelo = :id_paralelo";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_asignatura', $id_asignatura);
        $stmt->bindParam(':id_carrera', $id_carrera);
        $stmt->bindParam(':id_nivel', $id_nivel);
        $stmt->bindParam(':id_jornada', $id_jornada);
        $stmt->bindParam(':id_paralelo', $id_paralelo);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Función para obtener las notas de un estudiante para un aporte específico (desde la tabla notas)
    function getNotas($conexion, $id_estudiante, $id_asignatura, $id_aporte) {
        $sql = "SELECT id_notas, titulo, descripcion, nota 
                FROM notas 
                WHERE id_estudiante = :id_estudiante 
                  AND id_asignatura = :id_asignatura 
                  AND id_aporte = :id_aporte
                ORDER BY id_notas ASC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->bindParam(':id_aporte', $id_aporte, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Función para obtener el promedio final de un estudiante desde la tabla bimestres
    function getBimestre($conexion, $id_estudiante, $id_asignatura, $bimestre = 1) {
        $sql = "SELECT promedio_docencia, promedio_practico, promedio_autonomo, promedio_examen, promedio_final 
                FROM bimestres 
                WHERE id_estudiante = :id_estudiante 
                  AND id_asignatura = :id_asignatura 
                  AND bimestre = :bimestre";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
        $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
        $stmt->bindParam(':bimestre', $bimestre, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $id_docente = $_SESSION['id_usuario'];
    $asignaturas = obtenerDatosDocente($conexion, $id_docente, $id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo);
    $asig = !empty($asignaturas) ? $asignaturas[0] : [];
    $estudiantes = getEstudiantes($conexion, $id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $headingStyle = [
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['argb' => 'FFD9D9D9']
        ],
        'font' => [
            'bold' => true,
            'color' => ['argb' => 'FF000000']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ]
        ]
    ];

    // 1) Fila 1: Encabezado "Instituto Cordillera" (A1:J1 combinadas)
    $sheet->mergeCells('A1:I1');
    $sheet->setCellValue('A1', 'Instituto Cordillera');
    $sheet->getStyle('A1:I1')->applyFromArray($headingStyle);
    $sheet->getStyle('A1')->getFont()->setSize(14);

    // 2) Fila 2: "Bimestre Académico" (A2:J2)
    $sheet->mergeCells('A2:I2');
    $sheet->setCellValue('A2', 'Bimestre Académico');
    $sheet->getStyle('A2:I2')->applyFromArray($headingStyle);
    $sheet->getStyle('A2')->getFont()->setSize(12);

    // 3) Fila 3: Encabezados para Carrera, Jornada, Nivel, Paralelo
    $sheet->setCellValue('A3', 'Carrera');
    $sheet->setCellValue('B3', 'Jornada');
    $sheet->setCellValue('C3', 'Nivel');
    $sheet->setCellValue('D3', 'Paralelo');
    $sheet->mergeCells('E3:I3');
    $sheet->getStyle('A3:I3')->applyFromArray($headingStyle);

    // 4) Fila 4: Valores reales de Carrera, Jornada, Nivel, Paralelo
    if (!empty($asig)) {
        $sheet->setCellValue('A4', $asig['carrera']);
        $sheet->setCellValue('B4', $asig['jornada']);
        $sheet->setCellValue('C4', $asig['nivel']);
        $sheet->setCellValue('D4', $asig['paralelo']);
    }
    $sheet->mergeCells('E4:I4');
    $sheet->getStyle('A4:I4')->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ]
        ]
    ]);

    // 5) Fila 5: Títulos "Asignatura" y "Nombre del Docente"
    $sheet->setCellValue('A5', 'Asignatura');
    $sheet->setCellValue('B5', 'Nombre del Docente');
    $sheet->mergeCells('C5:I5');
    $sheet->getStyle('A5:B5')->applyFromArray($headingStyle);

    // 6) Fila 6: Valores reales de Asignatura y Docente
    if (!empty($asig)) {
        $sheet->setCellValue('A6', $asig['asignatura']);
        $sheet->setCellValue('B6', $asig['docente_nombre']);
    }
    $sheet->mergeCells('C6:I6');
    $sheet->getStyle('A6:I6')->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000']
            ]
        ]
    ]);

    // 7) Fila 7: Encabezados para la tabla de estudiantes y notas  
    // Columnas: A: ID Estudiante | B: Nombre Estudiante | C: AD Nota 1 | D: AD Nota 2 | E: AP Nota 1 | F: AP Nota 2 | G: AA Nota 1 | H: AA Nota 2 | I: Examen | J: Promedio
    $sheet->setCellValue('A7', 'ID Estudiante');
    $sheet->setCellValue('B7', 'Nombre Estudiante');
    $sheet->setCellValue('C7', 'AD Nota 1');
    $sheet->setCellValue('D7', 'AD Nota 2');
    $sheet->setCellValue('E7', 'AP Nota 1');
    $sheet->setCellValue('F7', 'AP Nota 2');
    $sheet->setCellValue('G7', 'AA Nota 1');
    $sheet->setCellValue('H7', 'AA Nota 2');
    $sheet->setCellValue('I7', 'Examen');
    $sheet->getStyle('A7:I7')->applyFromArray($headingStyle);

    // 8) Filas 8 en adelante: Datos de cada estudiante y sus notas
    $rowEst = 8;
    foreach ($estudiantes as $est) {
        $id_estudiante = $est['id_estudiante']; // Se obtiene directamente desde la tabla estudiantes
        $nombreEst = $est['username'];

        // Obtener las notas para cada aporte
        $adNotas = getNotas($conexion, $id_estudiante, $id_asignatura, 1);
        $apNotas = getNotas($conexion, $id_estudiante, $id_asignatura, 2);
        $aaNotas = getNotas($conexion, $id_estudiante, $id_asignatura, 3);
        $exNotas = getNotas($conexion, $id_estudiante, $id_asignatura, 4);

        // Asignar las notas (se esperan 2 para AD, AP y AA; 1 para EX)
        $adNota1 = isset($adNotas[0]['nota']) ? $adNotas[0]['nota'] : '';
        $adNota2 = isset($adNotas[1]['nota']) ? $adNotas[1]['nota'] : '';
        $apNota1 = isset($apNotas[0]['nota']) ? $apNotas[0]['nota'] : '';
        $apNota2 = isset($apNotas[1]['nota']) ? $apNotas[1]['nota'] : '';
        $aaNota1 = isset($aaNotas[0]['nota']) ? $aaNotas[0]['nota'] : '';
        $aaNota2 = isset($aaNotas[1]['nota']) ? $aaNotas[1]['nota'] : '';
        $exNota  = isset($exNotas[0]['nota']) ? $exNotas[0]['nota'] : '';

        // Obtener el promedio final desde la tabla bimestres
        $bimData = getBimestre($conexion, $id_estudiante, $id_asignatura, 1);
        $promedioFinal = isset($bimData['promedio_final']) ? $bimData['promedio_final'] : '';

        // Escribir datos en la fila actual
        $sheet->setCellValue('A' . $rowEst, $id_estudiante);
        $sheet->setCellValue('B' . $rowEst, $nombreEst);
        $sheet->setCellValue('C' . $rowEst, $adNota1);
        $sheet->setCellValue('D' . $rowEst, $adNota2);
        $sheet->setCellValue('E' . $rowEst, $apNota1);
        $sheet->setCellValue('F' . $rowEst, $apNota2);
        $sheet->setCellValue('G' . $rowEst, $aaNota1);
        $sheet->setCellValue('H' . $rowEst, $aaNota2);
        $sheet->setCellValue('I' . $rowEst, $exNota);

        // Aplicar bordes a la fila
        $sheet->getStyle("A{$rowEst}:I{$rowEst}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000']
                ]
            ]
        ]);

        $rowEst++;
    }

    // Autoajustar el ancho de las columnas A a J
    foreach (range('A', 'I') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Preparar la salida del archivo Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'Estudiantes_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
}
