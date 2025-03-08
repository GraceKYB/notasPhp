<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear Carrera</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-4">
    <h2>Crear Carrera</h2>
    <form action="" method="POST">
      <div class="form-group">
        <label for="nombre">Nombre</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required>
      </div>

      <!-- Sección para las Jornadas -->
      <div class="form-group">
        <label>Jornadas</label><br>
        <?php foreach($jornadas as $jornada): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="jornadas[]" 
                   id="jornada_<?php echo $jornada['id_jornada']; ?>" 
                   value="<?php echo $jornada['id_jornada']; ?>">
            <label class="form-check-label" for="jornada_<?php echo $jornada['id_jornada']; ?>">
              <?php echo $jornada['nombre']; ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Sección para los Niveles -->
      <div class="form-group">
        <label>Niveles</label><br>
        <?php foreach($niveles as $nivel): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="niveles[]" 
                   id="nivel_<?php echo $nivel['id_nivel']; ?>" 
                   value="<?php echo $nivel['id_nivel']; ?>">
            <label class="form-check-label" for="nivel_<?php echo $nivel['id_nivel']; ?>">
              <?php echo $nivel['nombre']; ?>
            </label>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
  </div>
</body>
</html>