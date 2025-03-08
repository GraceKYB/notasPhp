<?php
require_once 'models/Nivel.php';

class NivelController {
    private $nivel;

    public function __construct($conexion) {
        $this->nivel = new Nivel($conexion);
    }

    public function index() {
        $niveles = $this->nivel->obtenerNivelesConParalelos();
        require_once 'views/niveles/index.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre']; // Nombre del nivel
            $paralelos = isset($_POST['paralelos']) ? $_POST['paralelos'] : []; // Paralelos seleccionados
            
            // Llamar al método para agregar el nivel, pasando el nombre y los paralelos seleccionados
            $this->nivel->agregarNivelConParalelos($nombre, $paralelos);
            
            $_SESSION['mensaje'] = "Nivel creado exitosamente.";
            header('Location: index.php?modulo=nivel&accion=index');
            exit(); 
        } else {
            // Cargar la lista de paralelos para mostrar en el formulario
            $paralelos = $this->nivel->obtenerParalelos(); // Método para obtener paralelos
            require_once 'views/niveles/crear.php';
        }
    }
    

    public function editar($id_nivel) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $this->nivel->actualizarNivel($id_nivel, $nombre);
            $_SESSION['mensaje'] = "Nivel actualizado exitosamente.";
            header('Location: index.php?modulo=nivel&accion=index');
            exit();
        } else {
            $nivel = $this->nivel->obtenerNivel($id_nivel);
            require_once 'views/niveles/editar.php';
        }
    }

    public function eliminar($id_nivel) {
        $this->nivel->eliminarNivel($id_nivel);
        $_SESSION['mensaje'] = "Nivel eliminado exitosamente.";
        header('Location: index.php?modulo=nivel&accion=index');
        exit();
}
}
?>
