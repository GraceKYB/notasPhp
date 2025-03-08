<?php
require_once 'models/Matricula.php';

class MatriculaController {
    private $matricula;

    public function __construct($conexion) {
        $this->matricula = new Matricula($conexion);
    }
   
    // Listado de matrículas
    public function index() {
        // Obtener la lista de estudiantes y sus asignaturas
        $estudiante_asignaturas = $this->matricula->listarEstudianteAsignaturas();
    
        if (empty($estudiante_asignaturas)) {
            $_SESSION['mensaje'] = 'No hay asignaturas disponibles.';
        }
    
        // Pasar los datos a la vista
        require_once 'views/matricula/index.php';
    }
    
    public function crear() {
        // Inicialización de variables
        $carreras    = $this->matricula->obtenerCarreras();
        $jornadas    = [];
        $niveles     = [];
        $paralelos   = [];
        $asignaturas = [];
        $error       = false;
        $estudiante  = null;
    
        // Obtener datos enviados desde el formulario
        $id_carrera           = isset($_POST['id_carrera']) ? $_POST['id_carrera'] : null;
        $selected_jornadas    = isset($_POST['id_jornada']) ? (array) $_POST['id_jornada'] : [];
        // id_nivel se envía como valor único; lo convertimos a array para trabajar de forma uniforme
        $selected_niveles     = isset($_POST['id_nivel']) ? (array) $_POST['id_nivel'] : [];
        // id_paralelo se envía como array multidimensional, por ejemplo: [1 => [0 => "1"]]
        $selected_paralelos   = isset($_POST['id_paralelo']) ? $_POST['id_paralelo'] : [];
        // id_asignatura también es un array multidimensional, por ejemplo: [1 => [ "1", "2", "3" ]]
        $selected_asignaturas = isset($_POST['id_asignatura']) ? $_POST['id_asignatura'] : [];
    
        $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    
        // Buscar estudiante por cédula y obtener el id_usuario correspondiente
        if ($cedula) {
            $estudiante = $this->matricula->buscarEstudiante($cedula);
            if (!$estudiante) {
                echo "<p class='alert alert-danger'>No se encontró el docente con la cédula ingresada.</p>";
                $error = true;
            }
        }
    
        // Obtener jornadas y niveles para la carrera seleccionada
        if ($id_carrera) {
            $jornadas = $this->matricula->obtenerJornadasPorCarrera($id_carrera);
            $niveles  = $this->matricula->obtenerNivelesPorCarrera($id_carrera);
        }
    
        // Obtener paralelos y asignaturas para cada nivel seleccionado
        if (!empty($selected_niveles)) {
            foreach ($selected_niveles as $id_nivel) {
                $paralelos[$id_nivel]   = $this->matricula->obtenerParalelosPorNivel($id_nivel);
                $asignaturas[$id_nivel] = $this->matricula->obtenerAsignaturasPorNivelYCarrera($id_nivel, $id_carrera);
            }
        }
    
        // Procesar el registro cuando se presiona "registrar" y no hubo error en la búsqueda
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registrar']) && !$error) {
            // Obtener el primer (y único) nivel seleccionado
            $nivelPrimero = !empty($selected_niveles) ? reset($selected_niveles) : null;
            // id_paralelo viene como array multidimensional: tomamos el primer paralelo del nivel seleccionado
            $id_paralelo = (isset($selected_paralelos[$nivelPrimero]) 
                            && is_array($selected_paralelos[$nivelPrimero]) 
                            && !empty($selected_paralelos[$nivelPrimero]))
                            ? reset($selected_paralelos[$nivelPrimero])
                            : null;
    
            // Usar el id_usuario obtenido de la búsqueda por cédula; si no se encontró, se puede usar otro valor
            $id_usuario = isset($estudiante['id_usuario'])
                        ? $estudiante['id_usuario']
                        : (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : (isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null));
    
            // Armar el arreglo de datos para la tabla 'estudiantes'
            $datosEstudiante = [
                'id_usuario'  => $id_usuario,
                'id_carrera'  => $id_carrera,
                'id_jornada'  => !empty($selected_jornadas) ? reset($selected_jornadas) : null,
                'id_nivel'    => $nivelPrimero,
                'id_paralelo' => $id_paralelo
            ];
    
            // Armar el arreglo de asignaturas para la tabla 'estudiante_asignatura'
            $asignaciones = [];
            if (!empty($selected_asignaturas)) {
                foreach ($selected_asignaturas as $nivel => $asigs) {
                    if (is_array($asigs)) {
                        foreach ($asigs as $id_asignatura) {
                            $asignaciones[] = ['id_asignatura' => $id_asignatura];
                        }
                    } else {
                        $asignaciones[] = ['id_asignatura' => $asigs];
                    }
                }
            }
    
            // Llamar al método del modelo que inserta en ambas tablas de forma transaccional
            if ($this->matricula->asignarEstudianteYAsignaturas($datosEstudiante, $asignaciones)) {
                $_SESSION['mensaje'] = "Estudiante y asignaturas registrados correctamente.";
                header('Location: index.php?modulo=matricula&accion=index');
                exit();
            } else {
                echo "<p class='alert alert-danger'>Hubo un error al registrar el estudiante.</p>";
            }
        }
    
        require_once 'views/matricula/crear.php';
    }
    
}
?>