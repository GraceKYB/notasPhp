<?php
require_once 'models/Carrera.php';

class CarreraController {
    private $carrera;

    public function __construct($conexion) {
        $this->carrera = new Carrera($conexion);
    }

    public function index() {
        $carreras = $this->carrera->obtenerCarrerasJornadasNiveles();
        require_once 'views/carreras/index.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $jornadas = isset($_POST['jornadas']) ? $_POST['jornadas'] : [];
            $niveles = isset($_POST['niveles']) ? $_POST['niveles'] : [];
    
            // Llamamos al método que guarda la carrera, los niveles y las jornadas
            $resultado = $this->carrera->agregarCarreraJornadas($nombre, $jornadas, $niveles);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "Carrera creada exitosamente.";
            } else {
                $_SESSION['mensaje'] = "Error al crear la carrera.";
            }
    
            header('Location: index.php?modulo=carrera&accion=index');
            exit(); 
        } else {
            $jornadas = $this->carrera->obtenerJornadas(); // Obtener las jornadas
            $niveles = $this->carrera->obtenerNiveles();   // Obtener los niveles
            require_once 'views/carreras/crear.php';
        }
    }
    
   
    public function editar($id_carrera) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $niveles = isset($_POST['niveles']) ? $_POST['niveles'] : [];
            
            $this->carrera->actualizarCarrera($id_carrera, $nombre, $niveles);
            $_SESSION['mensaje'] = "Carrera actualizada exitosamente.";
            header('Location: index.php?modulo=carrera&accion=index');
            exit(); 
        } else {
            $carrera = $this->carrera->obtenerCarrera($id_carrera);
            $niveles = $this->carrera->obtenerNiveles();
            $nivelesSeleccionados = $this->carrera->obtenerNivelesPorCarrera($id_carrera);
            require_once 'views/carreras/editar.php';
        }
    }

    public function eliminar($id_carrera) {
        $this->carrera->eliminarCarrera($id_carrera);
        $_SESSION['mensaje'] = "Carrera eliminado exitosamente.";
        header('Location: index.php?modulo=carrera&accion=index');
        exit(); 
    }
}
?>
