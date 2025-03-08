<?php
ob_start();  // Comienza el almacenamiento en búfer de salida
require_once 'models/Paralelo.php';

class ParaleloController {
    private $paralelo;

    public function __construct($conexion) {
        $this->paralelo = new Paralelo($conexion);
    }

    public function index() {
        $paralelos = $this->paralelo->obtenerParalelos();
        require_once 'views/paralelo/index.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $this->paralelo->agregarParalelo($nombre);
            $_SESSION['mensaje'] = "Paralelo creado exitosamente.";
            header('Location: index.php?modulo=paralelo&accion=index');
            exit(); 
        } else {
            require_once 'views/paralelo/crear.php';
        }
    }

    public function editar($id_paralelo) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $this->paralelo->actualizarParalelo($id_paralelo, $nombre);
            $_SESSION['mensaje'] = "Paralelo actualizado exitosamente.";
            header('Location: index.php?modulo=paralelo&accion=index');
            exit(); 
        } else {
            $paralelo = $this->paralelo->obtenerParalelo($id_paralelo);
            require_once 'views/paralelo/editar.php';
        }
    }

    public function eliminar($id_paralelo) {
        $this->paralelo->eliminarParalelo($id_paralelo);
        $_SESSION['mensaje'] = "Paralelo eliminado exitosamente.";
        header('Location: index.php?modulo=paralelo&accion=index');
        exit(); 
}
}