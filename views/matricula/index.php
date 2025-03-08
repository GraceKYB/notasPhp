<?php
if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-success">
        <?php echo $_SESSION['mensaje']; ?>
    </div>
    <?php unset($_SESSION['mensaje']); // Borra el mensaje después de mostrarlo ?>
<?php endif; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Asignaturas por Estudiante</title>
    <!-- Puedes incluir estilos CSS adicionales aquí -->
</head>
<body>
    <h1>Listado de Asignaturas por Estudiante</h1>

    <?php if (!empty($estudiante_asignaturas)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Carrera</th>
                    <th>Jornada</th>
                    <th>Nivel</th>
                    <th>Paralelo</th>
                    <th>Asignaturas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiante_asignaturas as $estudiante_asignatura): ?>
                    <tr>
                        <td><?= $estudiante_asignatura['nombre_estudiante'] ?></td>
                        <td><?= $estudiante_asignatura['carrera'] ?></td>
                        <td><?= $estudiante_asignatura['jornada'] ?></td>
                        <td><?= $estudiante_asignatura['nivel'] ?></td>
                        <td><?= $estudiante_asignatura['paralelo'] ?></td>
                        <td><?= $estudiante_asignatura['asignaturas'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay registros disponibles.</p>
    <?php endif; ?>
</body>
</html>
