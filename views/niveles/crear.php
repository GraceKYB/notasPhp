<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nivel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Crear Nivel</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" required>
            </div>

           <!-- Checkbox para seleccionar paralelos -->
            <div class="form-group">
                <label for="paralelos">Paralelos</label><br>
                <?php foreach ($paralelos as $paralelo): ?>
                    <div class="form-check">
                        <input type="checkbox" name="paralelos[]" value="<?= $paralelo['id_paralelo']; ?>" class="form-check-input" id="paralelo_<?= $paralelo['id_paralelo']; ?>">
                        <label class="form-check-label" for="paralelo_<?= $paralelo['id_paralelo']; ?>"><?= $paralelo['nombre']; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>


            <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
    </div>
</body>
</html>
