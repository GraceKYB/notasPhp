<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Carrera</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Editar Carrera</h2>
        <form action="index.php?modulo=carrera&action=editar&id=<?php echo $carrera['id_carrera']; ?>" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="<?php echo $carrera['nombre']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Niveles</label><br>
                <?php foreach($niveles as $nivel): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="niveles[]" 
                               id="nivel_<?php echo $nivel['id_nivel']; ?>" 
                               value="<?php echo $nivel['id_nivel']; ?>" 
                               <?php echo in_array($nivel['id_nivel'], $nivelesSeleccionados) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="nivel_<?php echo $nivel['id_nivel']; ?>">
                            <?php echo $nivel['nombre']; ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
    </div>
</body>
</html>
