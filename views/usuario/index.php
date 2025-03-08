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
    <title>Listar Usuarios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
    <h2>Listado de usuarios</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID Detalle</th>
                <th>Username</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Cedula</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?= htmlspecialchars($usuario['id_detalle']) ?></td>
                    <td><?= htmlspecialchars($usuario['username']) ?></td> 
                    <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                    <td><?= htmlspecialchars($usuario['apellido']) ?></td>
                    <td><?= htmlspecialchars($usuario['cedula']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>
                    <td>
                        <a href="index.php?modulo=usuario&action=editar&id=<?php echo $usuario['id_usuario']; ?>">Editar</a>
                        <a href="index.php?modulo=usuario&action=eliminar&id=<?php echo $usuario['id_usuario']; ?>">Eliminar</a>
                    </td>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <script>
        // Deshabilitar clic derecho
        document.addEventListener('contextmenu', function(event) {
            event.preventDefault();
        });

        // Deshabilitar las teclas F12 y Ctrl + U
        document.addEventListener('keydown', function(event) {
            if (event.key === 'F12' || (event.ctrlKey && event.key === 'u')) {
                event.preventDefault();
            }
        });
    </script>  
</body>
</html>
