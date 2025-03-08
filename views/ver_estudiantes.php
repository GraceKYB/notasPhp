<?php
session_start();
require_once '../config/database.php';

// Verifica si el usuario está logueado y tiene el perfil de docente
if (!isset($_SESSION['usuario']) || $_SESSION['id_perfil'] != 2) {
    // Si no está logueado o no es un docente, redirige al login
    header("Location: ../views/login.php");
    exit();
}

$id_docente = $_SESSION['id_usuario']; // Obtenemos el id_usuario desde la sesión

$id_asignatura = isset($_GET['id_asignatura']) && is_numeric($_GET['id_asignatura']) ? $_GET['id_asignatura'] : null;

// Si no se pasa el id_asignatura, redirigir a la página principal
if ($id_asignatura === null) {
    header("Location: index.php");
    exit();
}

// Consulta para obtener los estudiantes matriculados en la asignatura
try {
    $sql = "SELECT 
                m.id_matricula,
                c.nombre AS nombre_carrera,
                n.nombre AS nombre_nivel,
                mo.nombre AS nombre_modalidad,
                j.nombre AS nombre_jornada
            FROM matriculas m
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            JOIN carreras c ON m.id_carrera = c.id_carrera
            JOIN niveles n ON m.id_nivel = n.id_nivel
            JOIN modalidades mo ON m.id_modalidad = mo.id_modalidad
            JOIN jornadas j ON m.id_jornada = j.id_jornada
            WHERE m.id_asignatura = :id_asignatura AND u.estado = 'A'"; // Solo estudiantes activos
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_asignatura', $id_asignatura);
    $stmt->execute();
    $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verifica si hay estudiantes matriculados en esa asignatura
    if (!$estudiantes) {
        $mensaje = "No hay estudiantes matriculados en esta asignatura.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
// Consulta para obtener la asignatura que está impartiendo el docente
try {
    $sql = "SELECT a.id_asignatura, a.nombre AS asignatura, u.username AS docente
            FROM asignaturas a
            JOIN usuarios u ON a.id_docente = u.id_usuario
            WHERE a.id_docente = :id_docente AND a.estado = 'A'"; // Estado 'A' para activo
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id_docente', $id_docente);
    $stmt->execute();
    $asignatura = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifica si el docente tiene asignaturas
    if (!$asignatura) {
        $mensaje = "No tienes asignaturas asignadas.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estudiantes Matriculados</title>
    <link rel="stylesheet" href="../css/verEstudiante.css">
</head>
<body>
    <nav class="navbar">
        <span class="welcome-text">Estudiantes Matriculados</span>
        <a class="logout-btn" href="logout.php">Cerrar Sesión</a>
    </nav>

    <div class="container">
        <h2>Listado de Estudiantes</h2>

        <?php if (isset($mensaje)): ?>
            <p class="mensaje"><?php echo $mensaje; ?></p>
        <?php else: ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Asignatura</th>
                        <th>Nivel</th>
                        <th>Modalidad</th>
                        <th>Jornada</th>
                        <th>Acción</th> 
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $estudiante): ?>
                        <tr>
                            <td><?php echo $estudiante['nombre_carrera']; ?></td>
                            <td><?php echo $asignatura['asignatura']; ?></td>
                            <td><?php echo $estudiante['nombre_nivel']; ?></td>
                            <td><?php echo $estudiante['nombre_modalidad']; ?></td>
                            <td><?php echo $estudiante['nombre_jornada']; ?></td>
                            <td><a class="btn-link" href="asignar_notas.php?id_matricula=<?php echo $estudiante['id_matricula']; ?>">Asignar Notas</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>

