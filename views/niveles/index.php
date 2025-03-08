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
    <title>Niveles</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Listado de Niveles</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Paralelos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($niveles as $nivel): ?>
                    <tr>
                        <td><?php echo $nivel['id_nivel']; ?></td>
                        <td><?php echo $nivel['nivel_nombre']; ?></td>
                        <td><?php echo $nivel['paralelos']; ?></td>
                        <td>
                            <a href="index.php?modulo=nivel&action=editar&id=<?php echo $nivel['id_nivel']; ?>" class="btn btn-primary">Editar</a>
                            <a href="index.php?modulo=nivel&action=eliminar&id=<?php echo $nivel['id_nivel']; ?>" class="btn btn-danger">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
