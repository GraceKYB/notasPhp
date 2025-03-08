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
    <title>Listado de Asignaturas por Docente</title>
</head>
<body>
    <h1>Listado de Asignaturas por Docente</h1>

    <?php if (!empty($docente_asignaturas)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Docente</th>
                    <th>Asignatura</th>
                    <th>Jornada</th>
                    <th>Paralelo</th>
                    <th>Carrera</th>
                    <th>Nivel</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $previous_docente = '';
                foreach ($docente_asignaturas as $docente_asignatura): ?>
                    <?php if ($docente_asignatura['Nombre Docente'] != $previous_docente): ?>
                        <tr>
                            <td colspan="6" style="font-weight: bold;"><?php echo $docente_asignatura['Nombre Docente']; ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td></td> <!-- Columna vacía para que se alinee con el encabezado -->
                        <td><?= $docente_asignatura['Asignatura'] ?></td>
                        <td><?= $docente_asignatura['Jornada'] ?></td>
                        <td><?= $docente_asignatura['Paralelo'] ?></td>
                        <td><?= $docente_asignatura['Carrera'] ?></td>
                        <td><?= $docente_asignatura['Nivel'] ?></td>
                    </tr>
                    <?php 
                    $previous_docente = $docente_asignatura['Nombre Docente'];
                endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay registros disponibles.</p>
    <?php endif; ?>

</body>
</html>
