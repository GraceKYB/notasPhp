<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: views/login.php");
    exit();
}

require_once 'config/database.php';
require_once 'controllers/CarreraController.php';
require_once 'controllers/JornadaController.php';
require_once 'controllers/NivelController.php';
require_once 'controllers/MatriculaController.php';
require_once 'controllers/UsuarioController.php';
require_once 'controllers/ParaleloController.php';
require_once 'controllers/AsignaturaController.php';
require_once 'controllers/AdocenteController.php';

$carreraController = new CarreraController($conexion);
$jornadaController = new JornadaController($conexion);
$nivelController = new NivelController($conexion); 
$matriculaController = new MatriculaController($conexion); 
$usuarioController = new UsuarioController($conexion); 
$paraleloController = new ParaleloController($conexion); 
$asignaturaController = new AsignaturaController($conexion); 
$adocenteController = new AdocenteController($conexion); 

$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'home';
$accion = isset($_GET['action']) ? $_GET['action'] : 'index';

require_once 'views/layout/navbar.php'; // Incluye el navbar en todas las páginas

echo "<div class='container mt-4'>";

switch ($modulo) {
    case 'carrera':
        switch ($accion) {
            case 'crear':
                $carreraController->crear();
                break;
            case 'editar':
                $id_carrera = $_GET['id'];
                $carreraController->editar($id_carrera);
                break;
            case 'eliminar':
                $id_carrera = $_GET['id'];
                $carreraController->eliminar($id_carrera);
                break;
            case 'index':
            default:
                $carreraController->index();
                break;
        }
        break;
    case 'jornada':
        switch ($accion) {
            case 'crear':
                $jornadaController->crear();
                break;
            case 'editar':
                $id_jornada = $_GET['id'];
                $jornadaController->editar($id_jornada);
                break;
            case 'eliminar':
                $id_jornada = $_GET['id'];
                $jornadaController->eliminar($id_jornada);
                break;
            case 'index':
            default:
                $jornadaController->index();
                break;
        }
        break;
    case 'nivel': // Manejamos el módulo de niveles
        switch ($accion) {
            case 'crear':
                $nivelController->crear();
                break;
            case 'editar':
                $id_nivel = $_GET['id'];
                $nivelController->editar($id_nivel);
                break;
            case 'eliminar':
                $id_nivel = $_GET['id'];
                $nivelController->eliminar($id_nivel);
                break;
            case 'index':
            default:
                $nivelController->index();
                break;
        }
        break;
    case 'matricula': // Manejamos el módulo de niveles
        switch ($accion) {
            case 'crear':
                $matriculaController->crear();
                break;
            case 'editar':
                $id_matricula = $_GET['id'];
                $matriculaController->editar($id_matricula);
                break;
            case 'eliminar':
                $id_matricula = $_GET['id'];
                $matriculaController->eliminar($id_matricula);
                break;
            case 'index':
            default:
                $matriculaController->index();
                break;
        }
        break;
    case 'docente': // Manejamos el módulo de niveles
        switch ($accion) {
            case 'crear':
                $adocenteController->crear();
                break;
            case 'index':
            default:
                $adocenteController->index();
                break;
        }
        break;
    case 'paralelo':
        switch ($accion) {
            case 'crear':
                $paraleloController->crear();
                break;
            case 'editar':
                $id_paralelo = $_GET['id'];
                $paraleloController->editar($id_paralelo);
                break;
            case 'eliminar':
                $id_paralelo = $_GET['id'];
                $paraleloController->eliminar($id_paralelo);
                break;
            case 'index':
            default:
                $paraleloController->index();
                break;
        }
        break;
    case 'asignatura':
        switch ($accion) {
            case 'crear':
                $asignaturaController->crear();
                break;
            case 'editar':
                $id_asignatura = $_GET['id'];
                $asignaturaController->editar($id_asignatura);
                break;
            case 'eliminar':
                $id_asignatura = $_GET['id'];
                $asignaturaController->eliminar($id_asignatura);
                break;
            case 'index':
            default:
                $asignaturaController->index();
                break;
        }
        break;
    
    case 'usuario': // Manejamos el módulo de niveles
        switch ($accion) {
            case 'crear':
                $usuarioController->crear();
                break;
            case 'editar':
                $id_usuario= $_GET['id'];
                $usuarioController->editar($id_usuario);
                break;
            case 'eliminar':
                $id_usuario = $_GET['id'];
                $usuarioController->eliminar($id_usuario);
                break;
        default:
            $usuarioController->index();
            break;
        }
        break;
    case 'home':
    default:
        echo "<h2>Bienvenido al Sistema</h2><p>Seleccione un módulo en el menú de navegación.</p>";
        break;
}

echo "</div>";
?>