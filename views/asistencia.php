<?php
session_start();
require_once '../config/database.php';
require_once '../models/AsistenciaModel.php';

// Verificar que el usuario es docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php");
    exit();
}

// Obtener parámetros de la asignatura desde GET
$id_docente = $_SESSION['id_usuario'];
$id_asignatura = $_GET['id_asignatura'] ?? null;
$id_carrera = $_GET['id_carrera'] ?? null;
$id_nivel = $_GET['id_nivel'] ?? null;
$id_jornada = $_GET['id_jornada'] ?? null;
$id_paralelo = $_GET['id_paralelo'] ?? null;

if (!$id_asignatura || !$id_carrera || !$id_nivel || !$id_jornada || !$id_paralelo) {
    $_SESSION['mensaje'] = "Registro de Asistencia con exito";
    header("Location: ../views/aportes.php");
    exit();
}

// Instancia del modelo de asistencia
$asistenciaModel = new AsistenciaModel($conexion);

// Obtener lista de estudiantes y su asistencia
$estudiantes = $asistenciaModel->getListaAsistencia($id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo);

// Mostrar mensaje de sesión si existe
if (isset($_SESSION['mensaje'])) {
    echo "<script>alert('" . $_SESSION['mensaje'] . "');</script>";
    unset($_SESSION['mensaje']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asistencia</title>
    <style>
        h2 { text-align: center; color: #007BFF; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #007BFF; color: white; }
        .btn {
            text-decoration: none; color: white; padding: 5px 10px; border-radius: 5px;
            display: inline-block; margin: 5px; cursor: pointer;
        }
        .btn-primary { background-color: #007BFF; }
        .btn-success { background-color: #28a745; }
        .btn-warning {
    background-color: #ffc107; /* Color amarillo */
    color: black !important; /* Color de letra negro */
    border: 1px solid #d39e00; /* Borde opcional */
}
    </style>
</head>
<body>

<h2>Registro de Asistencia</h2>

<!-- Botón para generar QR -->
<form action="../controllers/GenerarQRController.php" method="post">
    <input type="hidden" name="id_asignatura" value="<?php echo $id_asignatura; ?>">
    <input type="hidden" name="id_carrera" value="<?php echo $id_carrera; ?>">
    <input type="hidden" name="id_nivel" value="<?php echo $id_nivel; ?>">
    <input type="hidden" name="id_jornada" value="<?php echo $id_jornada; ?>">
    <input type="hidden" name="id_paralelo" value="<?php echo $id_paralelo; ?>">
    <button type="submit" class="btn btn-primary">Generar QR</button>
</form>

<table>
    <thead>
        <tr>
            <th>Estudiante</th>
            <th>QR</th>
            <th>Asistencia</th>
            <th>Latitud</th>
            <th>Longitud</th>
            <th>Dispositivo</th>
            <th>Ubicación</th> 
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($estudiantes)) { ?>
            <?php foreach ($estudiantes as $estudiante) { ?>
                <tr>
                    <td><?php echo $estudiante['username']; ?></td>
                    <td>
                        <?php if (!empty($estudiante['codigo_qr'])) { ?>
                            <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo $estudiante['codigo_qr']; ?>&size=100x100">
                        <?php } else { ?>
                            <span style="color: red;">No generado</span>
                        <?php } ?>
                    </td>
                    <td><?php echo isset($estudiante['estado']) ? $estudiante['estado'] : "N/A"; ?></td>
                    <td><?php echo isset($estudiante['latitud']) ? $estudiante['latitud'] : "N/A"; ?></td>
                    <td><?php echo isset($estudiante['longitud']) ? $estudiante['longitud'] : "N/A"; ?></td>
                    <td><?php echo isset($estudiante['device_id']) ? $estudiante['device_id'] : "N/A"; ?></td>
                    <td>
                        <?php if (!empty($estudiante['latitud']) && !empty($estudiante['longitud'])) { ?>
                            <a href="https://www.google.com/maps?q=<?php echo $estudiante['latitud']; ?>,<?php echo $estudiante['longitud']; ?>" 
                               target="_blank" 
                               class="btn btn-success">
                               Ver Ubicación
                            </a>
                        <?php } else { ?>
                            <span style="color: red;">Sin datos</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($estudiante['estado'] !== 'Asistió') { ?>
                            <form method="post" action="../controllers/MarcarAsistenciaController.php">
                                <input type="hidden" name="id_estudiante" value="<?php echo $estudiante['id_estudiante']; ?>">
                                <input type="hidden" name="id_asignatura" value="<?php echo $id_asignatura; ?>">
                                <button type="submit" class="btn btn-warning">Marcar Asistencia</button>
                            </form>
                        <?php } else { ?>
                            <span style="color: green;">✔ Asistió</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="8">No hay estudiantes registrados en esta asignatura.</td></tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>
