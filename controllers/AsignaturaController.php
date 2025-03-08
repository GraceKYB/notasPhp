<?php
require_once 'models/Asignatura.php';

class AsignaturaController {
    private $model;

    public function __construct($conexion) {
        $this->model = new Asignatura($conexion);
    }

    public function index() {
        $datos = $this->model->obtenerAsignaturas();
        require_once 'views/asignaturas/index.php';
    }

    public function crear() {
        // Si se envía el formulario con el botón "Guardar"
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['guardar'])) {
            $nombre = $_POST['nombre'];
            $id_nivel = isset($_POST['id_nivel']) ? $_POST['id_nivel'] : null;
            $id_carrera = $_POST['id_carrera'];

            foreach ($nombre as $nombreAsignatura) {
                if ($this->model->agregarAsignatura($nombreAsignatura, $id_nivel, $id_carrera)) {
                    $_SESSION['mensaje'] = "Asignatura creada exitosamente.";
                } else {
                    $_SESSION['mensaje'] = "Error al crear la asignatura.";
                }
            }

            header('Location: index.php?modulo=asignatura&accion=index');
            exit();
        } else {
            // Si se envía el formulario al seleccionar una carrera, pero sin guardar
            $carreras = $this->model->obtenerCarreras();
            $carrera_niveles = [];
            if (!empty($_POST['id_carrera'])) {
                $carrera_niveles = $this->model->obtenerNivelesPorCarrera($_POST['id_carrera']);
            }

            // Cargar la vista con los niveles actualizados
            require_once 'views/asignaturas/crear.php';
        }
    }
}
