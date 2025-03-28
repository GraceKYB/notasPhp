<?php
session_start();
require_once '../config/database.php';
require_once '../models/AsignarNotas.php';

// Verificar que el usuario sea docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php");
    exit();
}

// Recoger parámetros GET necesarios
if (
    isset($_GET['id_asignatura']) &&
    isset($_GET['id_carrera']) &&
    isset($_GET['id_nivel']) &&
    isset($_GET['id_jornada']) &&
    isset($_GET['id_paralelo'])
) {
    $id_asignatura = $_GET['id_asignatura'];
    $id_carrera    = $_GET['id_carrera'];
    $id_nivel      = $_GET['id_nivel'];
    $id_jornada    = $_GET['id_jornada'];
    $id_paralelo   = $_GET['id_paralelo'];
} else {
    echo "No se ha recibido el ID de la asignatura, carrera, nivel, jornada o paralelo.";
    exit();
}

$asignarNotasModel = new AsignarNotas($conexion);


$estudiantes = $asignarNotasModel->getEstudiantes($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo);
$aportes = $asignarNotasModel->getAportes();
// Obtener las notas actuales desde la base de datos
$notasExistentes = $asignarNotasModel->getNotasExistentes($id_asignatura);
$notasMap = [];

// Mapear las notas existentes en un array organizado por estudiante y aporte
foreach ($notasExistentes as $nota) {
    $notasMap[$nota['id_estudiante']][$nota['id_aporte']][] = $nota['nota'];
}

// Por defecto, se puede seleccionar el primer aporte de la lista
$id_aporte_seleccionado = isset($aportes[0]['id_aporte']) ? $aportes[0]['id_aporte'] : 0;

//$id_aporte_seleccionado = 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recoger los demás identificadores
    $id_asignatura = $_POST['id_asignatura'];
    // Los otros campos (carrera, nivel, jornada, paralelo) se usan para obtener estudiantes, pero no son necesarios para la inserción aquí.
    $notas = $_POST['notas'];  // Arreglo multidimensional: [id_aporte][id_estudiante] => [notas]
    
    $resultado = $asignarNotasModel->guardarNotas($id_asignatura, $notas);
    
    if ($resultado) {
        // Luego de guardar las notas se genera el resumen bimestral
        $resumen = $asignarNotasModel->calcularResumenBimestral(
            $id_asignatura,
            $id_carrera,
            $id_nivel,
            $id_jornada,
            $id_paralelo
        );
        
        // Se guarda el resumen en la tabla "bimestres" (bimestre por defecto 1)
        $resultadoResumen = $asignarNotasModel->guardarResumenBimestral($id_asignatura, $resumen, 1);
        
        if ($resultadoResumen) {
            // **Actualizar los datos de la sesión con los valores actuales de la BD**
            $datosImportados = [];
            // Recorrer cada estudiante para obtener sus notas actualizadas
            foreach ($estudiantes as $estudiante) {
                $id_estudiante = $estudiante['id_estudiante_asignatura'];
                // Seleccionar las notas actualizadas de la base de datos para este estudiante y asignatura
                $sql = "SELECT id_aporte, nota 
                        FROM notas 
                        WHERE id_estudiante = :id_estudiante 
                          AND id_asignatura = :id_asignatura 
                        ORDER BY id_aporte ASC, id_notas ASC";
                $stmt = $conexion->prepare($sql);
                $stmt->bindParam(':id_estudiante', $id_estudiante, PDO::PARAM_INT);
                $stmt->bindParam(':id_asignatura', $id_asignatura, PDO::PARAM_INT);
                $stmt->execute();
                $notasRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $notasPorAporte = [];
                foreach ($notasRows as $row) {
                    $notasPorAporte[$row['id_aporte']][] = $row['nota'];
                }
                // Mapear: 1->'AD', 2->'AP', 3->'AA', 4->'EX'
                $map = [1 => 'AD', 2 => 'AP', 3 => 'AA', 4 => 'EX'];
                $import = [];
                foreach ($notasPorAporte as $aporte => $notasArr) {
                    $key = $map[$aporte];
                    if ($key === 'EX') {
                        $import[$key] = $notasArr[0]; // Examen: una sola nota
                    } else {
                        $import[$key] = $notasArr; // Los demás: array de notas
                    }
                }
                $datosImportados[] = [
                    'id' => $id_estudiante,
                    'AD' => isset($import['AD']) ? $import['AD'] : [],
                    'AP' => isset($import['AP']) ? $import['AP'] : [],
                    'AA' => isset($import['AA']) ? $import['AA'] : [],
                    'EX' => isset($import['EX']) ? $import['EX'] : 0
                ];
            }
            $_SESSION['datosImportados'] = $datosImportados;
            
            echo "Notas y resumen bimestral guardados correctamente.";
        } else {
            echo "Notas guardadas, pero ocurrió un error al guardar el resumen bimestral.";
        }
    } else {
        echo "Error al guardar las notas.";
    }
}

// Si no es POST o para mostrar la vista, se autogenera el resumen bimestral
$resumen = $asignarNotasModel->calcularResumenBimestral(
    $id_asignatura,
    $id_carrera,
    $id_nivel,
    $id_jornada,
    $id_paralelo
);

require_once '../views/asignar_notas.php';
?>