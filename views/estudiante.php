<?php
session_start();
require_once '../config/database.php';

// Verificar si el usuario es un estudiante
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 1) {
    header("Location: ../views/login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Obtener todos los ID de estudiante asociados al usuario
$sql_estudiante = "SELECT id_estudiante FROM estudiantes WHERE id_usuario = :id_usuario";
$stmt_estudiante = $conexion->prepare($sql_estudiante);
$stmt_estudiante->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt_estudiante->execute();
$estudiante_ids = $stmt_estudiante->fetchAll(PDO::FETCH_COLUMN);

if (!$estudiante_ids) {
    $_SESSION['mensaje'] = "Error: No se encontró el ID del estudiante.";
    header("Location: ../views/login.php");
    exit();
}

// Convertir los IDs en una lista para la consulta SQL
$placeholders = implode(',', array_fill(0, count($estudiante_ids), '?'));

// Consulta para obtener todas las asignaturas del estudiante junto con el código QR
$sql = "SELECT 
            a.nombre AS asignatura, 
            c.nombre AS carrera, 
            n.nombre AS nivel, 
            j.nombre AS jornada, 
            p.nombre AS paralelo,
            q.codigo_qr
        FROM estudiante_asignatura ea
        INNER JOIN asignaturas a ON ea.id_asignatura = a.id_asignatura
        INNER JOIN carreras c ON a.id_carrera = c.id_carrera
        INNER JOIN niveles n ON a.id_nivel = n.id_nivel
        INNER JOIN estudiantes e ON ea.id_estudiante = e.id_estudiante
        INNER JOIN jornadas j ON e.id_jornada = j.id_jornada
        INNER JOIN paralelo p ON e.id_paralelo = p.id_paralelo
        LEFT JOIN qr_estudiantes q ON e.id_estudiante = q.id_estudiante
        WHERE ea.id_estudiante IN ($placeholders)";

$stmt = $conexion->prepare($sql);
$stmt->execute($estudiante_ids);
$asignaturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Asignaturas</title>
    <style>
        h2 { text-align: center; color: #007BFF; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-family: Arial, sans-serif; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #007BFF; color: white; }
        .btn {
            text-decoration: none; color: white; padding: 5px 10px; border-radius: 5px;
            display: inline-block; cursor: pointer;
        }
        .btn-primary { background-color: #007BFF; }
        .modal {
            display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: white; margin: 15% auto; padding: 20px; width: 300px; text-align: center;
            border-radius: 10px;
        }
        .close {
            color: red; float: right; font-size: 28px; font-weight: bold; cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Mis Asignaturas</h2>

<table>
    <thead>
        <tr>
            <th>Carrera</th>
            <th>Asignatura</th>
            <th>Nivel</th>
            <th>Jornada</th>
            <th>Paralelo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($asignaturas)) {
            foreach ($asignaturas as $index => $asignatura): ?>
                <tr>
                    <td><?php echo htmlspecialchars($asignatura['carrera']); ?></td>
                    <td><?php echo htmlspecialchars($asignatura['asignatura']); ?></td>
                    <td><?php echo htmlspecialchars($asignatura['nivel']); ?></td>
                    <td><?php echo htmlspecialchars($asignatura['jornada']); ?></td>
                    <td><?php echo htmlspecialchars($asignatura['paralelo']); ?></td>
                    <td>
                        <?php if (!empty($asignatura['codigo_qr'])) { ?>
                            <button class="btn btn-primary" onclick="verQR('<?php echo $asignatura['codigo_qr']; ?>')">Ver QR</button>
                        <?php } else { ?>
                            <span style="color: red;">QR no generado</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php endforeach; 
        } else {
            echo "<tr><td colspan='6'>No tienes asignaturas registradas.</td></tr>";
        } ?>
    </tbody>
</table>

<!-- Modal para mostrar el QR -->
<div id="qrModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal()">&times;</span>
        <h3>Mi Código QR</h3>
        <img id="qrImage" src="" alt="QR Code" style="width: 200px; height: 200px;">
    </div>
</div>

<script>
    function verQR(qrData) {
        document.getElementById('qrImage').src = "https://api.qrserver.com/v1/create-qr-code/?data=" + qrData + "&size=200x200";
        document.getElementById('qrModal').style.display = "block";
    }

    function cerrarModal() {
        document.getElementById('qrModal').style.display = "none";
    }
</script>

</body>
</html>
