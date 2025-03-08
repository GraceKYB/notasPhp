<?php
// Datos importados desde Excel (si existen)
$datosImportados = isset($_SESSION['datosImportados']) ? $_SESSION['datosImportados'] : [];

// Se asume que las siguientes variables están definidas en el controlador:
// $id_asignatura, $id_carrera, $id_nivel, $id_jornada, $id_paralelo, $estudiantes, $aportes y $id_aporte_seleccionado

// Función auxiliar para obtener la nota importada para un estudiante y aporte
function getImportedValue($datosImportados, $studentId, $aporteKey, $index = 0) {
    foreach ($datosImportados as $dato) {
        if ($dato['id'] == $studentId) {
            // Para el examen ('EX') se guarda directamente (no es array)
            if ($aporteKey === 'EX') {
                return $dato['EX'];
            } else if (isset($dato[$aporteKey][$index])) {
                return $dato[$aporteKey][$index];
            }
        }
    }
    return "";
}

// Mapeo de id_aporte a la clave que se usó en el Excel
// Se espera: 1 -> AD, 2 -> AP, 3 -> AA, 4 -> EX
$aporteMap = [
    '1' => 'AD',
    '2' => 'AP',
    '3' => 'AA',
    '4' => 'EX'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Asignar Notas</title>
  <style>
    .menu-aportes { margin-bottom: 30px; }
    h2 { text-align: center; color: #007BFF; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid black; padding: 10px; text-align: center; }
    .aporte-tab { display: none; }
    .aporte-tab.active { display: block; }
    .menu-aportes button {
      margin-right: 10px;
      padding: 8px 12px;
      cursor: pointer;
      border: none;
      background-color: #007BFF;
      color: white;
      border-radius: 5px;
    }
    .menu-aportes button.active { background-color: #0056b3; }
  </style>
</head>
<body>
<div class="content">
  <h2>Asignar Notas</h2>
  
  <!-- Menú de Pestañas -->
  <div class="menu-aportes">
    <!-- Los botones usan los id_aporte de la BD (1,2,3,4) y uno para el resumen -->
    <button type="button" onclick="showAporte('1')" class="active">Actividad Docente</button>
    <button type="button" onclick="showAporte('2')">Actividad Práctica</button>
    <button type="button" onclick="showAporte('3')">Actividad Autónoma</button>
    <button type="button" onclick="showAporte('4')">Examen</button>
    <button type="button" onclick="showAporte('resumen')">Resumen Bimestral</button>
  </div>

  <!-- Formulario de Notas -->
  <form action="" method="post">
    <!-- Campos ocultos necesarios -->
    <input type="hidden" name="id_asignatura" value="<?php echo $id_asignatura; ?>">
    <input type="hidden" name="id_carrera" value="<?php echo $id_carrera; ?>">
    <input type="hidden" name="id_nivel" value="<?php echo $id_nivel; ?>">
    <input type="hidden" name="id_jornada" value="<?php echo $id_jornada; ?>">
    <input type="hidden" name="id_paralelo" value="<?php echo $id_paralelo; ?>">

    <!-- Pestañas para cada aporte (AD, AP, AA, EX) -->
    <?php foreach ($aportes as $aporte): ?>
      <?php 
        // Solo procesamos los aportes con id 1 a 4
        if (!isset($aporteMap[$aporte['id_aporte']])) continue;
        $excelKey = $aporteMap[$aporte['id_aporte']];
      ?>
      <div id="aporte-<?php echo $aporte['id_aporte']; ?>" class="aporte-tab <?php echo ($aporte['id_aporte'] == $id_aporte_seleccionado) ? 'active' : ''; ?>">
        <h4>Estudiantes para el Aporte: <?php echo $aporte['nombre']; ?></h4>
        <!-- Botones para agregar/eliminar inputs -->
        <button type="button" onclick="agregarNota(<?php echo $aporte['id_aporte']; ?>)">+</button>
        <button type="button" onclick="eliminarNota(<?php echo $aporte['id_aporte']; ?>)">-</button>
        <table>
          <thead>
            <tr>
              <th>Estudiante</th>
              <th>Notas</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($estudiantes)): ?>
              <?php foreach ($estudiantes as $estudiante): ?>
                <tr>
                  <td><?php echo htmlspecialchars($estudiante['username']); ?></td>
                  <td class="notas-container" data-aporte="<?php echo $aporte['id_aporte']; ?>" data-id="<?php echo $estudiante['id_estudiante_asignatura']; ?>">
                    <?php if ($aporte['id_aporte'] == 4): ?>
                      <!-- Para Examen, una sola casilla -->
                      <input type="number"
                        name="notas[<?php echo $aporte['id_aporte']; ?>][<?php echo $estudiante['id_estudiante_asignatura']; ?>][]"
                        placeholder="Nota"
                        step="0.01" min="0" max="10"
                        class="nota"
                        value="<?php echo htmlspecialchars(getImportedValue($datosImportados, $estudiante['id_estudiante_asignatura'], $excelKey)); ?>">
                    <?php else: ?>
                      <!-- Para AD, AP y AA, dos casillas -->
                      <input type="number"
                        name="notas[<?php echo $aporte['id_aporte']; ?>][<?php echo $estudiante['id_estudiante_asignatura']; ?>][]"
                        placeholder="Nota 1"
                        step="0.01" min="0" max="10"
                        class="nota"
                        value="<?php echo htmlspecialchars(getImportedValue($datosImportados, $estudiante['id_estudiante_asignatura'], $excelKey, 0)); ?>">
                      <input type="number"
                        name="notas[<?php echo $aporte['id_aporte']; ?>][<?php echo $estudiante['id_estudiante_asignatura']; ?>][]"
                        placeholder="Nota 2"
                        step="0.01" min="0" max="10"
                        class="nota"
                        value="<?php echo htmlspecialchars(getImportedValue($datosImportados, $estudiante['id_estudiante_asignatura'], $excelKey, 1)); ?>">
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="2">No hay estudiantes matriculados.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>

    <!-- Pestaña: Resumen Bimestral (se mantiene igual) -->
    <div id="aporte-resumen" class="aporte-tab">
      <h4>Resumen Bimestral</h4>
      <table>
        <thead>
          <tr>
            <th>Estudiante</th>
            <th>AD</th>
            <th>AP</th>
            <th>AA</th>
            <th>EX</th>
            <th>Promedio</th>
          </tr>
        </thead>
        <tbody>
          <?php if (isset($resumen) && !empty($resumen)): ?>
            <?php foreach ($resumen as $row): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['estudiante']); ?></td>
                <td><?php echo htmlspecialchars($row['AD']); ?></td>
                <td><?php echo htmlspecialchars($row['AP']); ?></td>
                <td><?php echo htmlspecialchars($row['AA']); ?></td>
                <td><?php echo htmlspecialchars($row['EX']); ?></td>
                <td><?php echo htmlspecialchars($row['Promedio']); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6">No hay resumen disponible.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <br>
    <input type="submit" value="Guardar Notas">
  </form>
</div>

<script>
// Función para mostrar la pestaña seleccionada
function showAporte(tipo) {
  var tabs = document.querySelectorAll('.aporte-tab');
  tabs.forEach(function(tab) {
    tab.classList.remove('active');
  });
  if (tipo === 'resumen') {
    document.getElementById('aporte-resumen').classList.add('active');
  } else {
    document.getElementById('aporte-' + tipo).classList.add('active');
  }
  
  var buttons = document.querySelectorAll('.menu-aportes button');
  buttons.forEach(function(btn) {
    btn.classList.remove('active');
  });
  event.target.classList.add('active');
}

// Funciones para agregar y eliminar inputs de nota
function agregarNota(id_aporte) {
  var contenedores = document.querySelectorAll('#aporte-' + id_aporte + ' .notas-container');
  contenedores.forEach(function(fila) {
    var totalNotas = fila.querySelectorAll('input').length + 1;
    var input = document.createElement('input');
    input.type = 'number';
    input.name = 'notas[' + id_aporte + '][' + fila.dataset.id + '][]';
    input.placeholder = 'Nota ' + totalNotas;
    input.step = "0.01";
    input.min = "0";
    input.max = "10";
    input.classList.add('nota');
    fila.appendChild(input);
  });
}

function eliminarNota(id_aporte) {
  var contenedores = document.querySelectorAll('#aporte-' + id_aporte + ' .notas-container');
  contenedores.forEach(function(fila) {
    var notas = fila.querySelectorAll('input');
    if (notas.length > 1) {
      fila.removeChild(notas[notas.length - 1]);
    }
  });
}
</script>
</body>
</html>