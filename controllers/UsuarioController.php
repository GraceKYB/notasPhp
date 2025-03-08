<?php
require_once 'models/Usuario.php';
require_once 'models/Perfil.php';

class UsuarioController {
    private $conexion;
    private $usuario;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->usuario = new Usuario($conexion);
    }

    // Listar los usuarios para el formulario
    public function obtenerUsuarios() {
        return $this->usuario->obtenerUsuarios();
    }
     // Mostrar la lista de usuarios
     public function index() {
        $usuarios = $this->usuario->obtenerUsuariosDetalles();
        require_once 'views/usuario/index.php';
    }

    // Crear un nuevo usuario
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoger los datos del formulario
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $cedula = $_POST['cedula'];
            $telefono = $_POST['telefono'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $idPerfil = $_POST['id_perfil']; // Cambié a 'id_perfil' según tu estructura
    
            // Instanciar el modelo de Usuario
            $usuarioModel = new Usuario($this->conexion);
    
            // Crear el usuario
            $usuarioModel->crearUsuario($username, $password, $idPerfil, $nombre, $apellido, $cedula,$telefono, $email);
           
            $_SESSION['mensaje'] = "Usuario creado exitosamente.";
            header('Location: index.php?modulo=usuario&accion=index');
        } else {
            // Obtener los perfiles disponibles
            $perfil = new Perfil($this->conexion);
            $perfiles = $perfil->obtenerPerfiles();
    
            // Cargar la vista de creación de usuario
            require_once 'views/usuario/crear.php'; // Cambia la ruta si es necesario
        }
    }
    

    public function editar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Recoger datos del formulario
            $id_usuario = $_POST['id_usuario'];
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $cedula = $_POST['cedula'];
            $telefono = $_POST['telefono'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $id_perfil = $_POST['id_perfil'];
    
            // Instanciar el modelo de Usuario
            $usuarioModel = new Usuario($this->conexion);
    
            // Actualizar el usuario
            if ($usuarioModel->actualizarUsuario($id_usuario, $username, $id_perfil, $nombre, $apellido, $cedula, $telefono, $email)) {
                $_SESSION['mensaje'] = "Usuario actualizado correctamente.";
                header('Location: index.php?modulo=usuario&accion=index');
                exit;
            } else {
                $_SESSION['mensaje'] = "Error al actualizar usuario.";
            }
        }
    
        // Obtener datos del usuario a editar
        $id_usuario = $_GET['id'];
        $usuarioModel = new Usuario($this->conexion);
        $usuario = $usuarioModel->obtenerUsuarioPorId($id_usuario);
    
        // Obtener los perfiles disponibles
        $perfil = new Perfil($this->conexion);
        $perfiles = $perfil->obtenerPerfiles();
    
        // Cargar la vista de edición
        require_once 'views/usuario/editar.php';
    }
    

    // Eliminar un usuario
    public function eliminar()
    {
        if (isset($_GET['id'])) {
            $id_usuario = $_GET['id'];
            $resultado = $this->usuario->eliminarUsuario($id_usuario);

            if ($resultado) {
                $_SESSION['mensaje'] = "Usuario eliminado correctamente.";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar el usuario.";
            }
        }
        header('Location: index.php?modulo=usuario&accion=index');
    }


    // Obtener los perfiles (asumiendo que los perfiles son usados en la creación y edición de usuarios)
    public function obtenerPerfiles() {
        return $this->usuario->obtenerPerfiles();
    }
}
?>
