<?php
$carreras = isset($carreras) ? $carreras : [];
$carrera_niveles = isset($carrera_niveles) ? $carrera_niveles : [];
// Si existen asignaturas en el POST, las usamos; si no, mostramos un campo vacío.
$asignaturas = isset($_POST['nombre']) && is_array($_POST['nombre']) ? $_POST['nombre'] : [''];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crear Asignatura</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h2>Crear Asignatura</h2>
    <form action="" method="POST">
      <!-- Ingreso de nombres de asignatura -->
      <div id="asignaturas-container">
        <?php foreach ($asignaturas as $index => $nombre): ?>
          <div class="input-group mb-2">
            <input type="text" class="form-control" name="nombre[]" placeholder="Nombre de la asignatura" required value="<?= htmlspecialchars($nombre) ?>">
            <div class="input-group-append">
              <?php if ($index == 0): ?>
                <button type="button" class="btn btn-success" onclick="agregarAsignatura()">+</button>
              <?php else: ?>
                <button type="button" class="btn btn-danger" onclick="removeField(this)">-</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

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

      <!-- Selección de Niveles -->
      <?php if (!empty($carrera_niveles)): ?>
      <div class="form-group">
        <label for="nivel">Seleccione el Nivel:</label>
        <select id="nivel" class="form-control" name="id_nivel">
          <option value="">Seleccione...</option>
          <?php foreach ($carrera_niveles as $nivel): ?>
            <option value="<?= $nivel['id_nivel'] ?>" <?= (isset($_POST['id_nivel']) && $_POST['id_nivel'] == $nivel['id_nivel']) ? 'selected' : '' ?>>
              <?= $nivel['nombre'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <button type="submit" name="guardar" class="btn btn-primary">Guardar</button>
    </form>
  </div>

  <script>
    function agregarAsignatura() {
      let container = document.getElementById("asignaturas-container");
      let div = document.createElement("div");
      div.classList.add("input-group", "mb-2");

      let input = document.createElement("input");
      input.type = "text";
      input.classList.add("form-control");
      input.name = "nombre[]";
      input.placeholder = "Nombre de la asignatura";
      input.required = true;

      let divButton = document.createElement("div");
      divButton.classList.add("input-group-append");

      let button = document.createElement("button");
      button.type = "button";
      button.classList.add("btn", "btn-danger");
      button.textContent = "-";
      button.onclick = function() { removeField(this); };

      divButton.appendChild(button);
      div.appendChild(input);
      div.appendChild(divButton);
      container.appendChild(div);
    }

    function removeField(button) {
      // Remueve el grupo de input correspondiente al botón presionado
      let inputGroup = button.parentNode.parentNode;
      inputGroup.remove();
    }
  </script>
</body>
</html>
