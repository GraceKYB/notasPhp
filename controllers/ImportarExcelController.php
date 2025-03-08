<?php
ob_start();
session_start();
require_once '../config/database.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar que se haya subido el archivo
if (isset($_FILES['excel_file']) && $_FILES['excel_file']['error'] === 0) {
    // Recoger parámetros
    $id_asignatura = $_POST['id_asignatura'];
    $id_carrera    = $_POST['id_carrera'];
    $id_nivel      = $_POST['id_nivel'];
    $id_jornada    = $_POST['id_jornada'];
    $id_paralelo   = $_POST['id_paralelo'];

    // Cargar el archivo Excel
    $file = $_FILES['excel_file']['tmp_name'];
    $reader = IOFactory::createReaderForFile($file);
    $spreadsheet = $reader->load($file);
    $sheet = $spreadsheet->getActiveSheet();

    // Convertir el contenido de la hoja a un arreglo
    $data = $sheet->toArray();

    // Inicializamos el arreglo para almacenar los datos importados y usarlos en el formulario
    $datosImportados = [];

    // Suponiendo que los datos comienzan en la fila 8 (índice 7)
    foreach (array_slice($data, 7) as $rowIndex => $row) {
        // Verificamos que la fila tenga al menos ID y nombre
        if (count($row) < 2) {
            continue;
        }

        $id_estudiante = trim($row[0]);
        $nombre_estudiante = trim($row[1]);

        // Si están vacíos, saltamos la fila
        if (empty($id_estudiante) || empty($nombre_estudiante)) {
            continue;
        }

        // Extraer notas (si la celda no existe o no es numérica, se asigna 0)
        $adNota1 = isset($row[2]) ? (is_numeric($row[2]) ? $row[2] : 0) : 0;
        $adNota2 = isset($row[3]) ? (is_numeric($row[3]) ? $row[3] : 0) : 0;
        $apNota1 = isset($row[4]) ? (is_numeric($row[4]) ? $row[4] : 0) : 0;
        $apNota2 = isset($row[5]) ? (is_numeric($row[5]) ? $row[5] : 0) : 0;
        $aaNota1 = isset($row[6]) ? (is_numeric($row[6]) ? $row[6] : 0) : 0;
        $aaNota2 = isset($row[7]) ? (is_numeric($row[7]) ? $row[7] : 0) : 0;
        $exNota  = isset($row[8]) ? (is_numeric($row[8]) ? $row[8] : 0) : 0;

        // Calcular promedios parciales y final
        $adAvg = ($adNota1 + $adNota2) / 2;
        $apAvg = ($apNota1 + $apNota2) / 2;
        $aaAvg = ($aaNota1 + $aaNota2) / 2;
        $exVal = $exNota;
        // Por ejemplo: AD=25%, AP=20%, AA=20%, EX=35%
        $promedioFinal = round(($adAvg * 0.25) + ($apAvg * 0.20) + ($aaAvg * 0.20) + ($exVal * 0.35), 2);

        // Definir los aportes según la estructura:
        // 1: Actividad Docente (AD), 2: Actividad Práctica (AP), 3: Actividad Autónoma (AA), 4: Examen (EX)
        $aportes = [
            1 => [$adNota1, $adNota2],
            2 => [$apNota1, $apNota2],
            3 => [$aaNota1, $aaNota2],
            4 => [$exNota]
        ];

        // Insertar las notas en la tabla 'notas'
        $sqlInsert = "INSERT INTO notas (id_estudiante, id_asignatura, id_aporte, titulo, descripcion, nota)
                      VALUES (:id_estudiante, :id_asignatura, :id_aporte, :titulo, :descripcion, :nota)";
        $stmtInsert = $conexion->prepare($sqlInsert);

        foreach ($aportes as $id_aporte => $notasArray) {
            foreach ($notasArray as $nota) {
                $titulo = "Nota de aporte";
                $descripcion = null;
                $stmtInsert->bindParam(':id_estudiante', $id_estudiante);
                $stmtInsert->bindParam(':id_asignatura', $id_asignatura);
                $stmtInsert->bindParam(':id_aporte', $id_aporte);
                $stmtInsert->bindParam(':titulo', $titulo);
                $stmtInsert->bindValue(':descripcion', $descripcion, PDO::PARAM_NULL);
                $stmtInsert->bindParam(':nota', $nota);
                $stmtInsert->execute();
            }
        }

        // Insertar o actualizar en la tabla 'bimestres'
        $sqlSelectBim = "SELECT * FROM bimestres
                         WHERE id_estudiante = :id_estudiante AND id_asignatura = :id_asignatura AND bimestre = 1";
        $stmtSelectBim = $conexion->prepare($sqlSelectBim);
        $stmtSelectBim->bindParam(':id_estudiante', $id_estudiante);
        $stmtSelectBim->bindParam(':id_asignatura', $id_asignatura);
        $stmtSelectBim->execute();
        $bimRegistro = $stmtSelectBim->fetch(PDO::FETCH_ASSOC);

        if ($bimRegistro) {
            $sqlUpdateBim = "UPDATE bimestres
                             SET promedio_docencia = :ad,
                                 promedio_practico = :ap,
                                 promedio_autonomo = :aa,
                                 promedio_examen   = :ex,
                                 promedio_final    = :promedio_final
                             WHERE id_estudiante = :id_estudiante
                               AND id_asignatura = :id_asignatura
                               AND bimestre      = 1";
            $stmtUpdateBim = $conexion->prepare($sqlUpdateBim);
            $stmtUpdateBim->bindParam(':ad', $adAvg);
            $stmtUpdateBim->bindParam(':ap', $apAvg);
            $stmtUpdateBim->bindParam(':aa', $aaAvg);
            $stmtUpdateBim->bindParam(':ex', $exVal);
            $stmtUpdateBim->bindParam(':promedio_final', $promedioFinal);
            $stmtUpdateBim->bindParam(':id_estudiante', $id_estudiante);
            $stmtUpdateBim->bindParam(':id_asignatura', $id_asignatura);
            $stmtUpdateBim->execute();
        } else {
            $sqlInsertBim = "INSERT INTO bimestres (
                                id_estudiante,
                                id_asignatura,
                                bimestre,
                                promedio_docencia,
                                promedio_practico,
                                promedio_autonomo,
                                promedio_examen,
                                promedio_final
                             ) VALUES (
                                :id_estudiante,
                                :id_asignatura,
                                1,
                                :ad,
                                :ap,
                                :aa,
                                :ex,
                                :promedio_final
                             )";
            $stmtInsertBim = $conexion->prepare($sqlInsertBim);
            $stmtInsertBim->bindParam(':id_estudiante', $id_estudiante);
            $stmtInsertBim->bindParam(':id_asignatura', $id_asignatura);
            $stmtInsertBim->bindParam(':ad', $adAvg);
            $stmtInsertBim->bindParam(':ap', $apAvg);
            $stmtInsertBim->bindParam(':aa', $aaAvg);
            $stmtInsertBim->bindParam(':ex', $exVal);
            $stmtInsertBim->bindParam(':promedio_final', $promedioFinal);
            $stmtInsertBim->execute();
        }

        // Guardamos en el arreglo para autocompletar el formulario
        $datosImportados[] = [
            'id' => $id_estudiante,
            'AD' => [$adNota1, $adNota2],
            'AP' => [$apNota1, $apNota2],
            'AA' => [$aaNota1, $aaNota2],
            'EX' => $exNota
        ];
    }

    // Guardar los datos importados en la sesión para usarlos en el formulario
    $_SESSION['datosImportados'] = $datosImportados;

    echo "Archivo importado y notas guardadas correctamente.";
    exit();
} else {
    echo "No se ha subido un archivo o ocurrió un error en la carga.";
}
?>
