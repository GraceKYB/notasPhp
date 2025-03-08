<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Matrícula</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Sistema de Matrículas</a>
    </nav>

    <div class="container mt-4">
        <h2>Editar Matrícula</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="id_usuario">Usuario</label>
                <select name="id_usuario" id="id_usuario" class="form-control" required>
                    <option value="<?php echo $matricula['id_usuario']; ?>"><?php echo $matricula['id_usuario']; ?></option>
                    <!-- Aquí deberías llenar el select con los usuarios disponibles -->
                </select>
            </div>

            <div class="form-group">
                <label for="id_carrera">Carrera</label>
                <select name="id_carrera" id="id_carrera" class="form-control" required>
                    <?php foreach ($carreras as $carrera): ?>
                        <option value="<?php echo $carrera['id_carrera']; ?>" <?php echo ($carrera['id_carrera'] == $matricula['id_carrera']) ? 'selected' : ''; ?>><?php echo $carrera['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_asignatura">Asignatura</label>
                <select name="id_asignatura" id="id_asignatura" class="form-control" required>
                    <?php foreach ($asignaturas as $asignatura): ?>
                        <option value="<?php echo $asignatura['id_asignatura']; ?>" <?php echo ($asignatura['id_asignatura'] == $matricula['id_asignatura']) ? 'selected' : ''; ?>><?php echo $asignatura['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_modalidad">Modalidad</label>
                <select name="id_modalidad" id="id_modalidad" class="form-control" required>
                    <?php foreach ($modalidades as $modalidad): ?>
                        <option value="<?php echo $modalidad['id_modalidad']; ?>" <?php echo ($modalidad['id_modalidad'] == $matricula['id_modalidad']) ? 'selected' : ''; ?>><?php echo $modalidad['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_jornada">Jornada</label>
                <select name="id_jornada" id="id_jornada" class="form-control" required>
                    <?php foreach ($jornadas as $jornada): ?>
                        <option value="<?php echo $jornada['id_jornada']; ?>" <?php echo ($jornada['id_jornada'] == $matricula['id_jornada']) ? 'selected' : ''; ?>><?php echo $jornada['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_nivel">Nivel</label>
                <select name="id_nivel" id="id_nivel" class="form-control" required>
                    <?php foreach ($niveles as $nivel): ?>
                        <option value="<?php echo $nivel['id_nivel']; ?>" <?php echo ($nivel['id_nivel'] == $matricula['id_nivel']) ? 'selected' : ''; ?>><?php echo $nivel['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Actualizar Matrícula</button>
        </form>
    </div>
</body>
</html>
