<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Notas</title>
    <link rel="stylesheet" href="../css/stylelogin.css">
</head>
<body>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form action="../controllers/AuthController.php" method="POST">
            <div class="input-group">
                <input type="text" name="nombre" placeholder="Usuario" required>
            </div>
            <div class="input-group">
                <input type="password" name="contrasenia" placeholder="Contraseña" required>
            </div>
            <button type="submit" name="login" class="btn">Ingresar</button>
        </form>
    </div>
</body>
</html>

