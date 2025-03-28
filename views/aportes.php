<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['mensaje'])) {
    echo "<div class='mensaje' style='padding:10px; margin:10px auto; background-color:#e0f3ff; border:1px solid #007BFF; color:#007BFF; border-radius:5px; text-align:center; width:90%; max-width:600px;'>" . htmlspecialchars($_SESSION['mensaje']) . "</div>";
    unset($_SESSION['mensaje']);
}

// Verificar si el usuario es un docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php");
    exit();
}

$id_docente = $_SESSION['id_usuario']; 

// Consulta SQL actualizada: 
// - Se une la tabla 'asignaturas' para obtener el id_nivel y id_carrera.
// - Se une la tabla 'carreras' usando a.id_carrera y 'niveles' usando a.id_nivel.
// - Se utiliza la columna id_usuario de docente_asignatura para filtrar por docente.
$sql = "SELECT 
            a.nombre AS asignatura, 
            c.nombre AS carrera, 
            n.nombre AS nivel, 
            j.nombre AS jornada, 
            p.nombre AS paralelo,
            a.id_asignatura,
            a.id_carrera,
            a.id_nivel,
            da.id_jornada,
            da.id_paralelo
        FROM docente_asignatura da
        INNER JOIN asignaturas a ON da.id_asignatura = a.id_asignatura
        INNER JOIN carreras c ON a.id_carrera = c.id_carrera
        INNER JOIN niveles n ON a.id_nivel = n.id_nivel
        LEFT JOIN jornadas j ON da.id_jornada = j.id_jornada
        LEFT JOIN paralelo p ON da.id_paralelo = p.id_paralelo
        WHERE da.id_usuario = :id_docente";

$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id_docente', $id_docente);
$stmt->execute();
$asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 style="text-align: center; color: #007BFF;">Mis Asignaturas</h2>
<table style="width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif;">
    <thead>
        <tr style="background-color: #007BFF; color: white;">
            <th style="padding: 10px; border: 1px solid #ddd;">Carrera</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Asignatura</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Nivel</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Jornada</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Paralelo</th>
            <th style="padding: 10px; border: 1px solid #ddd;">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($asignaturas)) {
            foreach ($asignaturas as $asignatura): ?>
                <tr style="text-align: center; background-color: #f9f9f9;">
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $asignatura['carrera']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $asignatura['asignatura']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $asignatura['nivel']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $asignatura['jornada']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?php echo $asignatura['paralelo']; ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                    <a href="../controllers/AsignarNotasController.php?id_asignatura=<?php echo $asignatura['id_asignatura']; ?>&id_carrera=<?php echo $asignatura['id_carrera']; ?>&id_nivel=<?php echo $asignatura['id_nivel']; ?>&id_jornada=<?php echo $asignatura['id_jornada']; ?>&id_paralelo=<?php echo $asignatura['id_paralelo']; ?>"
                        style="text-decoration: none; color: white; background-color: #28a745; padding: 5px 10px; border-radius: 5px; display: inline-block;">
                        Ver Estudiantes
                    </a>
                    <a href="../controllers/ExportarExcelController.php?id_asignatura=<?php echo $asignatura['id_asignatura']; ?>&id_carrera=<?php echo $asignatura['id_carrera']; ?>&id_nivel=<?php echo $asignatura['id_nivel']; ?>&id_jornada=<?php echo $asignatura['id_jornada']; ?>&id_paralelo=<?php echo $asignatura['id_paralelo']; ?>"
                        style="text-decoration: none; color: white; background-color: #007BFF; padding: 5px 10px; border-radius: 5px; display: inline-block; margin-left: 10px;">
                        Descargar Excel
                    </a>
                     <!-- Formulario para importar Excel -->
                     <form action="../controllers/ImportarExcelController.php" method="post" enctype="multipart/form-data" style="display:inline-block; margin-left:10px;">
                        <input type="file" name="excel_file" id="excel_file_<?php echo $asignatura['id_asignatura']; ?>" accept=".xlsx, .xls" style="display:inline-block;">
                        <!-- Enviar también los demás parámetros necesarios -->
                        <input type="hidden" name="id_asignatura" value="<?php echo $asignatura['id_asignatura']; ?>">
                        <input type="hidden" name="id_carrera" value="<?php echo $asignatura['id_carrera']; ?>">
                        <input type="hidden" name="id_nivel" value="<?php echo $asignatura['id_nivel']; ?>">
                        <input type="hidden" name="id_jornada" value="<?php echo $asignatura['id_jornada']; ?>">
                        <input type="hidden" name="id_paralelo" value="<?php echo $asignatura['id_paralelo']; ?>">
                        <button type="submit" style="padding:5px 10px; border-radius:5px; background-color:#dc3545; color:white;">Importar Notas</button>
                    </form>
                    <a href="../views/asistencia.php?id_asignatura=<?php echo $asignatura['id_asignatura']; ?>&id_carrera=<?php echo $asignatura['id_carrera']; ?>&id_nivel=<?php echo $asignatura['id_nivel']; ?>&id_jornada=<?php echo $asignatura['id_jornada']; ?>&id_paralelo=<?php echo $asignatura['id_paralelo']; ?>" 
                        style="text-decoration: none; color: white; background-color: #28a745; padding: 5px 10px; border-radius: 5px; display: inline-block;">
                        Asistencia
                    </a>
                    </td>
                </tr>   
            <?php endforeach; 
        } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 10px; border: 1px solid #ddd;'>No hay asignaturas disponibles.</td></tr>";
        } ?>
    </tbody>
</table>
