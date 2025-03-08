<?php
require_once 'models/Asignatura.php';

class DocenteController {
    private $asignatura;

    public function __construct($conexion) {
        $this->asignatura = new Asignatura($conexion);
    }

    public function index() {
        // Verificar que exista la sesión y que el usuario tenga perfil de docente (suponiendo id_perfil = 2)
        if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
            header("Location: views/login.php");
            exit;
        }

        $id_docente = $_SESSION['id_usuario'];
        $asignaturas = $this->asignatura->obtenerAsignaturasPorDocente($id_docente);
        require_once 'views/docentes/index.php';
    }
}
?>
