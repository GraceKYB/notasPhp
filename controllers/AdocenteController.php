<?php
require_once 'models/docente.php';

class AdocenteController {
    private $docente;

    public function __construct($conexion) {
        $this->docente = new Docente($conexion);
    }
   // Controlador para listar las asignaturas por docente
    public function index() {
        // Obtener los registros de la tabla docente_asignatura
        $docente_asignaturas = $this->docente->listarDocenteAsignaturas();

        if (empty($docente_asignaturas)) {
            $_SESSION['mensaje'] = 'No hay asignaturas disponibles.';
        }

        // Pasar los datos a la vista
        require_once 'views/docente/index.php';
    }

    public function crear() {
        // Inicialización de variables
        $carreras = $this->docente->obtenerCarreras();
        $jornadas = [];
        $niveles = [];
        $paralelos = [];
        $asignaturas = [];
        $docente = null;
    
        // Obtener datos del formulario
        $id_carrera = isset($_POST['id_carrera']) ? $_POST['id_carrera'] : null;
        $selected_jornadas = isset($_POST['id_jornada']) ? (array) $_POST['id_jornada'] : [];
        $selected_niveles = isset($_POST['id_nivel']) ? (array) $_POST['id_nivel'] : [];
        $selected_paralelos = isset($_POST['id_paralelo']) ? (array) $_POST['id_paralelo'] : [];
        $selected_asignaturas = isset($_POST['id_asignatura']) ? (array) $_POST['id_asignatura'] : [];
    
        $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    
        // Buscar docente por cédula
        if ($cedula) {
            $docente = $this->docente->buscarDocente($cedula);
            if (!$docente) {
                echo "<p class='alert alert-danger'>No se encontró el docente con la cédula ingresada.</p>";
            }
        }
    
        // Obtener jornadas y niveles para la carrera seleccionada
        if ($id_carrera) {
            $jornadas = $this->docente->obtenerJornadasPorCarrera($id_carrera);
            $niveles = $this->docente->obtenerNivelesPorCarrera($id_carrera);
        }
    
        // Obtener paralelos y asignaturas para cada nivel seleccionado
        if (!empty($selected_niveles)) {
            foreach ($selected_niveles as $id_nivel) {
                $paralelos[$id_nivel] = $this->docente->obtenerParalelosPorNivel($id_nivel);
                $asignaturas[$id_nivel] = $this->docente->obtenerAsignaturasPorNivelYCarrera($id_nivel, $id_carrera);
            }
        }
    
        // Procesar la asignación de materias cuando se presiona "registrar"
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar'])) {
            $asignaciones = [];
            // Se arma la asignación para cada combinación de jornada, nivel, paralelo y asignatura
            foreach ($selected_jornadas as $id_jornada) {
                foreach ($selected_niveles as $id_nivel) {
                    foreach ($selected_paralelos[$id_nivel] as $id_paralelo) { // Tomamos paralelos por nivel
                        foreach ($selected_asignaturas[$id_nivel] as $id_asignatura) { // Tomamos asignaturas por nivel
                            $asignaciones[] = [
                                'id_asignatura' => $id_asignatura,
                                'id_jornada'    => $id_jornada,
                                'id_paralelo'   => $id_paralelo
                            ];
                        }
                    }
                }
            }
    
            // Si se encontró el docente y la asignación se procesa correctamente
            if ($docente && $this->docente->asignarDocenteAsignatura($docente['id_usuario'], $asignaciones)) {
                $_SESSION['mensaje'] = "Asignaturas asignadas correctamente.";
                header('Location: index.php?modulo=docente&accion=index');
                exit();
            } else {
                echo "<p class='alert alert-danger'>Hubo un error al asignar las materias.</p>";
            }
        }
    
        require_once 'views/docente/crear.php';
    }
    

}
    