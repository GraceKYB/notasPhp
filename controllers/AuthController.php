¿<?php
session_start();
require_once '../config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $nombre = $_POST['nombre'];
    $contrasenia = $_POST['contrasenia'];

    try {
        $sql = "SELECT * FROM usuarios WHERE username = :username AND estado = 'A'";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':username', $nombre);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Comparación de contraseñas dependiendo del perfil
            if ($usuario['id_perfil'] == 3) {
                // Administrador, no encriptación de contraseña
                if ($contrasenia === $usuario['password']) {
                    $_SESSION['usuario'] = $usuario['username'];
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['id_perfil'] = $usuario['id_perfil'];

                    // Redirigir según el perfil
                    header("Location: ../index.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
                    header("Location: ../views/login.php");
                    exit();
                }
            } else {
                // Otros perfiles, comparación con la contraseña encriptada
                if (password_verify($contrasenia, $usuario['password'])) {
                    $_SESSION['usuario'] = $usuario['username'];
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['id_perfil'] = $usuario['id_perfil'];

                    // Redirigir según el perfil
                    switch ($usuario['id_perfil']) {
                        case 2: // Docente
                            header("Location: ../views/docente.php");
                            break;
                        case 1: // Estudiante
                            header("Location: ../views/estudiante.php");
                            break;
                        default:
                            header("Location: ../views/login.php");
                            break;
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Usuario o contraseña incorrectos.";
                    header("Location: ../views/login.php");
                    exit();
                }
            }
        } else {
            $_SESSION['error'] = "Usuario no encontrado.";
            header("Location: ../views/login.php");
            exit();
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
