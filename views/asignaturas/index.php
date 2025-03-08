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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listado de Asignaturas</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h2>Listado de Asignaturas</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Asignatura</th>
          <th>Carrera</th>
          <th>Nivel</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($datos)): ?>
          <?php foreach ($datos as $fila): ?>
            <tr>
              <td><?= htmlspecialchars($fila['asignaturas']) ?></td>
              <td><?= htmlspecialchars($fila['carrera']) ?></td>
              <td><?= htmlspecialchars($fila['nivel']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="3">No hay asignaturas registradas</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>