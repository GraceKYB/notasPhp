<?php
session_start();

// Verificar si el usuario está logueado y tiene el perfil correcto (Docente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: ../views/login.php"); // Redirigir si no es docente
    exit();
}

require_once '../config/database.php';

// Obtener detalles del docente
$id_usuario = $_SESSION['id_usuario'];
$sql = "SELECT * FROM detalles_usuarios WHERE id_usuario = :id_usuario";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(':id_usuario', $id_usuario);
$stmt->execute();
$docente = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docente - Página Principal</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Asegúrate de tener este archivo para estilos -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #007BFF;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h2 {
            color: white;
            margin: 0;
        }
        .navbar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .navbar ul li {
            margin: 0 15px;
        }
        .navbar ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        .content {
            padding: 20px;
        }
        .section {
            display: none;
        }
        .section.active {
            display: block;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h2>Panel Docente</h2>
        <ul>
            <li><a href="#home" onclick="showSection('home')">Home</a></li>
            <li><a href="#perfil" onclick="showSection('perfil')">Perfil</a></li>
            <li><a href="#aportes" onclick="loadAportes()">Aportes</a></li>

            <li><a href="login.php">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <div class="content">
        <div id="home" class="section active">
            <h1>Bienvenido, <?php echo $docente['nombre'] . ' ' . $docente['apellido']; ?></h1>
            <p>Seleccione una opción del menú para comenzar.</p>
        </div>
        
        <div id="perfil" class="section">
            <h2>Mis Datos</h2>
            <p><strong>Nombre:</strong> <?php echo $docente['nombre'] . ' ' . $docente['apellido']; ?></p>
            <p><strong>Cédula:</strong> <?php echo $docente['cedula']; ?></p>
            <p><strong>Teléfono:</strong> <?php echo $docente['telefono']; ?></p>
            <p><strong>Email:</strong> <?php echo $docente['email']; ?></p>
        </div>
    </div>

    <div id="aporte" class="section">
        <h2>Mis Aportes</h2>
        <div id="aporte-content">
            <!-- Aquí se cargará dinámicamente el contenido de aportes.php -->
        </div>
    </div>


    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }
        function loadAportes() {
        showSection('aporte'); // Muestra la sección de "Aportes"

        fetch('aportes.php') // Llama al archivo PHP sin recargar la página
            .then(response => response.text()) // Convierte la respuesta a texto
            .then(data => {
                document.getElementById('aporte-content').innerHTML = data; // Inserta el contenido
            })
            .catch(error => console.error('Error al cargar los aportes:', error));
    }
    </script>
</body>
</html>
