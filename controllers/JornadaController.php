<?php
require_once 'models/Jornada.php';

class JornadaController {
    private $jornada;

    public function __construct($conexion) {
        $this->jornada = new Jornada($conexion);
    }

    public function index() {
        $jornadas = $this->jornada->obtenerJornadas();
        require_once 'views/jornadas/index.php';
    }

    public function crear() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nombre = $_POST['nombre'];

            if ($this->jornada->agregarJornada($nombre)) {
                $_SESSION['mensaje'] = "Jornada creado exitosamente.";
                header('Location: index.php?modulo=jornada&accion=index');
                exit(); 
            }
        }
        require_once 'views/jornadas/crear.php';
    }

    public function editar($id_jornada) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $this->jornada->actualizarJornada($id_jornada, $nombre);
            $_SESSION['mensaje'] = "Jornada actualizado exitosamente.";
            header('Location: index.php?modulo=jornada&accion=index');
            exit(); 
        } else {
            $jornada = $this->jornada->obtenerJornada($id_jornada);
            require_once 'views/jornadas/editar.php';
        }
    }

    public function eliminar($id_jornada) {
        $this->jornada->eliminarJornada($id_jornada);
        $_SESSION['mensaje'] = "Jornada eliminado exitosamente.";
        header('Location: index.php?modulo=jornada&accion=index');
        exit(); 
    }
}
?>
