<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="css/crearUsuario.css">
</head>
<body>
    <header>
        <h1>Editar Usuario</h1>
    </header>

    <!-- Mostrar mensaje de éxito o error -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="" method="POST">
            <h2>Detalles del Usuario</h2>

            <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($usuario['id_usuario']) ?>">

            <div class="form-group">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required>
            </div>

            <div class="form-group">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" value="<?= htmlspecialchars($usuario['cedula']) ?>" maxlength="10" required>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" maxlength="10" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
            </div>

            <h2>Información del Usuario</h2>

            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($usuario['username']) ?>" readonly required>
            </div>

            <div class="form-group">
                <label for="id_perfil">Perfil:</label>
                <select id="id_perfil" name="id_perfil" required>
                    <option value="">Seleccionar</option>
                    <?php foreach ($perfiles as $perfil): ?>
                        <option value="<?= htmlspecialchars($perfil['id_perfil']) ?>" 
                            <?= ($perfil['id_perfil'] == $usuario['id_perfil']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($perfil['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit">Actualizar</button>
        </form>
    </div>
</body>
</html>
