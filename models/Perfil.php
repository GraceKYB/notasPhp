<?php
class Perfil {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
  
    // Obtener todos los usuarios
    public function obtenerPerfiles() {
        $query = "SELECT * FROM perfiles WHERE estado = 'A'";
        $stmt = $this->conexion->prepare($query);   
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
