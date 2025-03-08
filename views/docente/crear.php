<?php 
// Inicializa las variables para evitar errores si no se han buscado aún
$docente = isset($docente) ? $docente : null;
$carreras = isset($carreras) ? $carreras : [];
$jornadas = isset($jornadas) ? $jornadas : [];
$niveles = isset($niveles) ? $niveles : [];
$paralelos = isset($paralelos) ? $paralelos : [];  // Array indexado por id_nivel
$asignaturas = isset($asignaturas) ? $asignaturas : [];  // Array indexado por id_nivel

// Para repoblar los selects de paralelos y asignaturas
$selected_paralelos = isset($_POST['id_paralelo']) ? $_POST['id_paralelo'] : [];
$selected_asignaturas = isset($_POST['id_asignatura']) ? $_POST['id_asignatura'] : [];
$selected_nivel = isset($_POST['id_nivel']) ? $_POST['id_nivel'] : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Materias a Docente</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Asignar Materias a Docente</h1>
        
        <!-- Mostrar mensajes de sesión si existen -->
        <?php 
        if (isset($_SESSION['mensaje'])) {
            echo "<p class='alert alert-info'>" . $_SESSION['mensaje'] . "</p>";
            unset($_SESSION['mensaje']);
        }
        ?>
        
        <!-- Formulario de búsqueda de docente -->
        <form method="post" action="">
            <div class="form-group">
                <label for="cedula">Buscar docente por cédula:</label>
                <input type="text" class="form-control" name="cedula" id="cedula" required>
            </div>
            <button type="submit" name="buscar" class="btn btn-primary">Buscar</button>
        </form>
        
        <?php if ($docente): ?>
            <h2 class="mt-4">Datos del Docente</h2>
            <p><strong>Nombre:</strong> <?php echo $docente['nombre']; ?></p>
            
            <!-- Formulario para asignación -->
            <form method="post" action="">
                <!-- Mantenemos la cédula para no perderla -->
                <input type="hidden" name="cedula" value="<?= htmlspecialchars($_POST['cedula'] ?? '') ?>">
                
                <!-- Selección de Carrera -->
                <div class="form-group">
                    <label for="carrera">Seleccione la Carrera:</label>
                    <select id="carrera" class="form-control" name="id_carrera" onchange="this.form.submit()">
                        <option value="">Seleccione...</option>
                        <?php foreach ($carreras as $carrera): ?>
                            <option value="<?= $carrera['id_carrera'] ?>" <?= (isset($_POST['id_carrera']) && $_POST['id_carrera'] == $carrera['id_carrera']) ? 'selected' : '' ?>>
                                <?= $carrera['nombre'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Selección de Jornada -->
                <?php if (!empty($jornadas)): ?>
                    <div class="form-group">
                        <label>Seleccione Jornadas:</label>
                        <?php foreach ($jornadas as $jornada): ?>
                            <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="id_jornada[]" value="<?= $jornada['id_jornada'] ?>" 
                                <?= (isset($_POST['id_jornada']) && in_array($jornada['id_jornada'], (array)$_POST['id_jornada'])) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $jornada['nombre'] ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                
                <!-- Selección de Nivel -->
                <?php if (!empty($niveles)): ?>
                <div class="form-group">
                    <label>Seleccione el Nivel:</label>
                    <?php foreach ($niveles as $nivel): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="id_nivel" value="<?= $nivel['id_nivel'] ?>"
                                <?= (isset($_POST['id_nivel']) && $_POST['id_nivel'] == $nivel['id_nivel']) ? 'checked' : '' ?>
                                onchange="this.form.submit()">
                            <label class="form-check-label"><?= $nivel['nombre'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Selección de Paralelos para el nivel seleccionado -->
                <?php if (!empty($paralelos) && $selected_nivel && isset($paralelos[$selected_nivel])): ?>
                <div class="form-group">
                    <label>Seleccione los Paralelos:</label>
                    <?php foreach ($paralelos[$selected_nivel] as $paralelo): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="id_paralelo[<?= $selected_nivel ?>][]" value="<?= $paralelo['id_paralelo'] ?>"
                                <?= (isset($selected_paralelos[$selected_nivel]) && in_array($paralelo['id_paralelo'], $selected_paralelos[$selected_nivel])) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= $paralelo['nombre'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Selección de Asignaturas para el nivel seleccionado -->
                <?php if (!empty($asignaturas) && $selected_nivel && isset($asignaturas[$selected_nivel])): ?>
                <div class="form-group">
                    <label>Seleccione las Asignaturas:</label>
                    <?php foreach ($asignaturas[$selected_nivel] as $asignatura): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="id_asignatura[<?= $selected_nivel ?>][]" value="<?= $asignatura['id_asignatura'] ?>"
                                <?= (isset($selected_asignaturas[$selected_nivel]) && in_array($asignatura['id_asignatura'], $selected_asignaturas[$selected_nivel])) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= $asignatura['nombre'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <button type="submit" name="registrar" class="btn btn-primary">Guardar Asignación</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
