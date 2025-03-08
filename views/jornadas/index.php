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
    <title>Jornadas</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Listado de Jornadas</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Jornada</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jornadas as $jornada): ?>
                    <tr>
                        <td><?php echo $jornada['id_jornada']; ?></td>
                        <td><?php echo $jornada['nombre']; ?></td>
                        <td>
                            <a href="index.php?modulo=jornada&action=editar&id=<?php echo $jornada['id_jornada']; ?>" class="btn btn-primary">Editar</a>
                            <a href="index.php?modulo=jornada&action=eliminar&id=<?php echo $jornada['id_jornada']; ?>" class="btn btn-danger">Eliminar</a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
