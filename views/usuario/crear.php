
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="css/crearUsuario.css">
    <script>
        // Función para generar automáticamente el username
        function generarUsernamePassword() {
            const nombre = document.getElementById('nombre').value;
            const username = nombre.toLowerCase();
            document.getElementById('username').value = username;
        }
    </script>
</head>
<body>
    <header>
        <h1>Crear Usuario</h1>
    </header>

    <!-- Mostrar mensaje de éxito si existe -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="mensaje"><?= $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="" method="POST" onsubmit="generarUsernamePassword()">
            <h2>Detalles del Usuario</h2>

            <!-- Nombre -->
            <div class="form-group">
                <label for="nombre">Nombre:<span style="color: red;">(Campo obligatorio)</span></label>
                <input type="text" id="nombre" name="nombre" onblur="generarUsernamePassword()" required>
            </div>

            <!-- Apellido -->
            <div class="form-group">
                <label for="apellido">Apellido:<span style="color: red;">(Campo obligatorio)</span></label>
                <input type="text" id="apellido" name="apellido" required>
            </div>
            
            <div class="form-group">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" maxlength="10" required>
            </div>


            <!-- Teléfono -->
            <div class="form-group">
                <label for="telefono">Teléfono:<span style="color: red;">(Campo obligatorio)</span></label>
                <input type="text" id="telefono" name="telefono" maxlength="10" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email:<span style="color: red;">(Campo obligatorio)</span></label>
                <input type="email" id="email" name="email" required>
            </div>

            <h2>Información del Usuario</h2>

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" readonly required>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password:<span style="color: red;">(Campo obligatorio)</span></label>
                <input type="password" id="password" name="password" maxlength="10" required>
            </div>

            <!-- Perfil -->
            <div class="form-group">
                <label for="id_perfil">Perfil:<span style="color: red;">(Campo obligatorio)</span></label>
                <select id="id_perfil" name="id_perfil" required>
                    <option value="">Seleccionar</option>
                    <?php if (!empty($perfiles)): ?>
                        <?php foreach ($perfiles as $perfil): ?>
                            <option value="<?= htmlspecialchars($perfil['id_perfil']) ?>">
                                <?= htmlspecialchars($perfil['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No hay perfiles disponibles</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Botón para crear el usuario -->
            <button type="submit">Crear</button>
        </form>
    </div>
</body>
</html>
